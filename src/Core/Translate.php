<?php

use DI\Container;
use Doctrine\Common\Cache\Cache;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;

/**
 * Gère la traduction.
 */
class Core_Translate
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(Translator $translator, LoggerInterface $logger, Cache $cache)
    {
        $this->translator = $translator;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * Va chercher le texte correspondant à la référence dans la langue voulue.
     *
     * @param string $package      Nom du module d'où est issue la traducation.
     * @param string $file         Nom du fichier tmx dans lequel chercher la traduction.
     * @param string $ref          Référence du texte (datagrids, champs etc...)
     * @param array  $replacements (optional) Tableau de remplacement à effectuer,
     *  ces remplacement prennent la forme suivante : array('RECHERCHE' => 'remplacement').
     * @param string $locale
     *
     * @return string Traduction.
     */
    public function get($package, $file, $ref, $replacements = [], $locale = null)
    {
        $id = $package . '.' . $file . '.' . $ref;

        return $this->translator->trans($id, $replacements, null, $locale);
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
        /** @var Container $container */
        $container = Zend_Registry::get('container');
        /** @var Core_Translate $translate */
        $translate = $container->get('Core_Translate');

        $message = $translate->get($package, $file, $ref);

        // Encode pour échapper tous les caractères qui pourraient casser la chaine de caractère JS
        return sprintf(
            'Translate.addTranslation(%s, %s, %s, %s);',
            json_encode($package),
            json_encode($file),
            json_encode($ref),
            json_encode($message)
        );
    }
}
