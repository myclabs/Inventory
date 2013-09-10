<?php

use Psr\Log\LoggerInterface;

include_once('TranslateAdapter.php');

/**
 * Alias de la fonction statique de traduction.
 * Permet de gagner du temps dans les vues.
 *
 * @param string $package      Nom du module d'où est issue la traducation.
 * @param string $file         Nom du fichier tmx dans lequel chercher la traduction.
 * @param string $ref          Référence du texte (datagrids, champs etc...)
 * @param array  $replacements (optionnel) Tableau de remplacement à effectuer,
 *                             ces remplacement prennent la forme suivante : array('RECHERCHE' => 'remplacement').
 *
 * @return string Texte traduit
 */
function __($package, $file, $ref, array $replacements = [])
{
    // Force la locale par défaut, sinon Zend ne prend pas en compte les changements en cours d'exécution de l'appli
    return Core_Translate::get($package, $file, $ref, $replacements, Core_Locale::loadDefault()->getId());
}


/**
 * Classe Translate
 */
class Core_Translate extends Zend_Translate
{

    // Emplacement des traductions.
    const registryKey = 'Core_Translate';
    const DATA_FOLDER = '/languages';
    const ADAPTER_CLASS = 'Core_Translate_Adapter_Tmx';

    private $options = array(
        'scan' => 'directory',
        // L'option override permet de dire si on écrase ou non les texte déjà chargés
        // (par défaut à true : dans ce cas, les textes des modules écrasent ceux de l'application)
        'override' => false,
        'useId' => true
    );

    public function __construct(LoggerInterface $logger)
    {
        // Création des options
        // Si on est en environnement de dev ou de test, on log les traductions manquantes
        if ((APPLICATION_ENV == 'developpement') || (APPLICATION_ENV == 'test')) {
            $this->options['disableNotices'] = false;
            $this->options['logUntranslated'] = true;
        } else {
            $this->options['disableNotices'] = true;
            $this->options['logUntranslated'] = false;
        }

        // Paramétrage du cache si on est pas en développement ou test
        if (APPLICATION_ENV == 'production' || APPLICATION_ENV == 'test') {
            $cache = Core_Cache::factory('translate');

            if (!$cache) {
                throw new Core_Exception_NotFound("Le cache des traductions n'a pas été créé "
                    . "(vérifiez que le dossier contenant le cache a été créé dans public/cache et "
                    . "qu'il est accessible en écriture)");
            }

            Zend_Translate::setCache($cache);
        }

        $this->options['adapter'] = $this::ADAPTER_CLASS;
        $this->options['content'] = APPLICATION_PATH.$this::DATA_FOLDER;
        $this->options['locale'] = 'auto';

        parent::__construct($this->options);

        /** @var Core_Translate_Adapter_Tmx $adapter */
        $adapter = $this->getAdapter();
        $adapter->setLogger($logger);
    }

    /**
     * Permet d'ajouter les textes/traductions d'un module.
     *
     * @param string $moduleName Nom du module
     * @param string $moduleDir Répertoire du module
     */
    public function addModule($moduleName, $moduleDir)
    {
        if (is_dir($moduleDir.'/application'.$this::DATA_FOLDER)) {
            $chemin = $moduleDir.'/application'.$this::DATA_FOLDER;
        } else {
            throw new Core_Exception_NotFound("Le répertoire '".$moduleDir."' est introuvable.");
        }

        // Ajout de l'emplacement aux options
        $this->options['folder'] = $moduleName;

        $this->addTranslation(
                $chemin,
                'auto',
                $this->options
        );
    }

    /**
     * Va chercher le texte correspondant à la référence dans la langue du poste client.
     *
     * @param string $package      Nom du module d'où est issue la traducation.
     * @param string $file         Nom du fichier tmx dans lequel chercher la traduction.
     * @param string $ref          Référence du texte (datagrids, champs etc...)
     * @param array  $replacements (optional) Tableau de remplacement à effectuer,
     *  ces remplacement prennent la forme suivante : array('RECHERCHE' => 'remplacement').
     * @param string $locale
     *
     * @return string Texte traduit
     */
    public static function get($package, $file, $ref, $replacements=array(), $locale=null)
    {
        // Chargement de la traduction.
        $translate = Zend_Registry::get(Core_Translate::registryKey);
        // Traduction du texte.
        $translation = $translate->_($ref, $file, $package, $locale);

        // Insertion d'espaces insécables, et des sauts de lignes.
        $exceptionList = array(
                                '?' => '# +\?#',
                                '!' => '# +!#',
                                ':' => '# +:#',
                                ';' => '#[^(&nbsp)] *;#',
                                'R' => '#\R#',
                            );
        // Chaque caractères de la liste d'exceptions sera remplacé par son équivalent dans la liste de remplacement.
        // Ici on remplace tout espace suivi par ?/!/:/; par un seul espace suivi du même caractère.
        // Les retours à la la ligne sont aussi remplacés par un espace simple.
        $exceptionReplacements = array(
                                    '?' => ' ?',
                                    '!' => ' !',
                                    ':' => ' :',
                                    ';' => ' ;',
                                    'R' => "\n",
                                );
        $translation = preg_replace($exceptionList, $exceptionReplacements, $translation);

        // Opération de remplacement.
        $searchs = array();
        $replaces = array();
        foreach ($replacements as $search => $replace) {
            $searchs[] = '['.strtoupper($search).']';
            $replaces[] = $replace;
        }
        $translation = str_replace($searchs, $replaces, $translation);

        // Retourne la traduction avec les termes recherchés remplacés.
        return $translation;
    }

    /**
     * Exporte une traduction dans le gestionnaire de traduction Javascript
     * @param string $package Nom du module d'où est issue la traducation.
     * @param string $file    Nom du fichier tmx dans lequel chercher la traduction.
     * @param string $ref     Référence du texte (datagrids, champs etc...)
     *
     * @return string Code javascript
     */
    public static function exportJS($package, $file, $ref)
    {
        $translate = Zend_Registry::get(Core_Translate::registryKey);
        $message = $translate->_($ref, $file, $package);

        // Encode pour échapper tous les caractères qui pourraient casser la chaine de caractère JS
        $jsMessage = json_encode($message);

        $js = "Translate.addTranslation('$package', '$file', '$ref', $jsMessage);";
        return $js;
    }

}
