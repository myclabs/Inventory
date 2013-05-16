<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Local
 */

/**
 * Classe permettant la localisation d'une application.
 *
 * @package    Core
 * @subpackage Local
 *
 * @uses Zend_Locale
 */
class Core_Locale
{

    /**
     * Emplacement de la locale dans le registry
     */
    const registryKey = 'Core_Locale';

    /**
     * ID de la locale
     * @var string
     */
    public $id;

    /**
     * Locale Zend
     * @var Zend_Locale
     */
    protected $zendLocale;

    /**
     * Nombre de chiffres significatifs à utiliser par défaut.
     *
     * Si non défini, alors pas de limite sur les chiffres significatifs.
     *
     * @var int
     */
    public $significantFigures = null;


    /**
     * Récupération de la liste des locales
     *
     * @return array(Core_Locale)
     */
    public static function loadList()
    {
        $localelist = Zend_Locale::getLocaleList();
    }

    /**
     * Récupération de la locale demandée
     *
     * @param string $id
     * @return Core_Locale
     * @throws Core_Exception_InvalidArgument Locale inconnue
     */
    public static function load($id)
    {
        if (! Zend_Locale::isLocale($id)) {
            throw new Core_Exception_InvalidArgument("Locale inconnue : '$id'");
        }
        $locale = new Zend_Locale($id);
        return new self($locale);
    }

    /**
     * Récupération de la locale par défaut
     *
     * @return Core_Locale
     */
    public static function loadDefault()
    {
        $locale = new Zend_Locale();
        return new self($locale);
    }

    /**
     * Définition de la locale par défaut à utiliser si aucune locale ne peut être trouvée
     * automatiquement pour l'utilisateur.
     *
     * @param string $id
     * @return void
     * @throws Core_Exception_InvalidArgument Locale inconnue
     */
    public static function setLocalDefault($id)
    {
        if (! Zend_Locale::isLocale($id)) {
            throw new Core_Exception_InvalidArgument("Locale inconnue : '$id'");
        }
        Zend_Locale::setDefault($id);
    }

    /**
     * Vérifie si l'identifiant proposé correspond bien à une locale
     *
     * @param string $id
     * @return bool
     */
    public static function isLocale($id)
    {
        return Zend_Locale::isLocale($id);
    }

    /**
     * Constructeur
     *
     * @param Zend_Locale $zendLocale
     */
    protected function __construct(Zend_Locale $zendLocale)
    {
        $this->id = $zendLocale->toString();
        // Lien vers la locale Zend
        $this->zendLocale = $zendLocale;
        // Chargement de la langue associée
        $idLangue = $zendLocale->getLanguage();
    }


    /**
     * Formate un nombre pour l'affichage.
     *
     * @param float $number
     * @param int|null $significantFigures indique le nombre de chiffres Significatif
     * @param int|null $numberDecimal indique le nombre de décimales pour l'affichage du nombre.
     *                              Incompatible Avec les chiffres significatifs.
     *
     * @return string
     */
    public function formatNumber($number, $significantFigures=null, $numberDecimal=null)
    {
        $options = array(
            'locale' => $this->zendLocale,
        );

        // Utilisation du nombre de chiffres significatifs par défaut si aucune option n'est spécifiée.
        if (($significantFigures == null) && ($numberDecimal == null)) {
            $significantFigures = $this->significantFigures;
        }

        if ($significantFigures !== null) {
            // Application du nombre de chiffre signifactif.
            // non géré par Zend.
            $precision = floor($significantFigures - log10(abs($number)));
            $number = round($number, $precision);
        } else if ($numberDecimal !== null) {
            // Si un nombre de de décimal est spécifié (mais pas de chiffres significatifs),
            // alors il est spécifié et Zend fera le formattage.
            $options['precision'] = $numberDecimal;
        }

        // Récupération du nombre formatté par Zend.
        $number = Zend_Locale_Format::toNumber($number, $options);

        return $number;

    }

    /**
     * Formate un nombre entier pour l'affichage.
     *
     * @param int $number
     * @return string
     */
    public function formatInteger($number)
    {
        $options = array(
            'locale' => $this->zendLocale,
        );
        return Zend_Locale_Format::toInteger((int) $number, $options);
    }

    /**
     * Formate une valeur d'incertitude pour l'affichage.
     * @param int $uncertainty
     * @return string
     */
    public function formatUncertainty($uncertainty)
    {
        $str = (string) round($uncertainty, 0);
        return $str . ' %';
    }

    /**
     * Récupère la valeur numérique d'une chaine de caractère.
     *
     * Peut être utilisé par exemple pour récupérer le nombre saisi par
     * un utilisateur dans un champ de formulaire.
     *
     * @param string   $input
     * @param int|null $significantFigures indique le nombre de chiffres Significatif
     * @param int|null $numberDecimal      indique le nombre de décimales du nombre.
     *                                     Incompatible Avec les chiffres significatifs.
     *
     * @return float
     * @throws Core_Exception_User Le nombre saisi n'est pas reconnu comme un nombre
     */
    public function retrieveNumber($input, $significantFigures = null, $numberDecimal = null)
    {
        $options = array(
            'locale' => $this->zendLocale
        );

        if (($significantFigures === null) && ($numberDecimal === null)) {
            $significantFigures = $this->significantFigures;
        }
        if ($significantFigures !== null) {
            $precision = floor($significantFigures - log10(abs($input)));
            $input = round($input, $precision);
        } else if ($numberDecimal !== null ) {
            $options['precision'] = $numberDecimal;
        }

        try {
            return (double) Zend_Locale_Format::getNumber($input, $options);
        } catch (Zend_Locale_Exception $e) {
            throw new Core_Exception_User("Le nombre saisi n'est pas reconnu comme un nombre.");
        }
    }

    /**
     * Récupère la valeur numérique entière d'une chaine de caractère
     *
     * Peut être utilisé par exemple pour récupérer le nombre entier saisi par
     * un utilisateur dans un champ de formulaire.
     *
     * @param string $saisie
     * @return int
     * @throws Core_Exception_User Le nombre saisi n'est pas reconnu comme un nombre entier
     */
    public function retrieveInteger($saisie)
    {
        $options = array(
            'locale' => $this->zendLocale,
        );
        try {
            return Zend_Locale_Format::getInteger($saisie, $options);
        } catch (Zend_Locale_Exception $e) {
            throw new Core_Exception_User("Le nombre saisi n'est pas reconnu comme un nombre entier.");
        }
    }

    /**
     * Parse une date en fonction de la locale
     *
     * @param string $str Saisie utilisateur
     * @throws Core_Exception_InvalidArgument La date saisie n'est pas reconnue
     *
     * @return DateTime
     */
    public function parseDate($str)
    {
        $options = [
            'locale' => $this->zendLocale,
        ];
        if (Zend_Locale_Format::checkDateFormat($str, $options)) {
            $parts = Zend_Locale_Format::getDate($str, $options);
            $dateStr = $parts['year'] . '-' . $parts['month'] . '-' . $parts['day'];
            return new DateTime($dateStr);
        }
        throw new Core_Exception_InvalidArgument('Invalid date');
    }

    /**
     * Formate une date en fonction de la locale
     *
     * @param DateTime|null $date
     *
     * @return string
     */
    public function formatDate(DateTime $date = null)
    {
        if ($date) {
            return $date->format('d/m/Y');
        } else {
            return '';
        }
    }

    /**
     * Formate un nombre entier pour l'affichage
     * @param int $valeur
     * @return string
     */
    public function formatCurrency($valeur)
    {
        $options = array(
            'value'        => $valeur,
            'currency'    => 'EUR',
        );
        $monnaie = new Zend_Currency($this->zendLocale);
        return $monnaie->toCurrency($valeur);
    }

    /**
     * @return string ID de la locale
     */
    public function getId()
    {
        return $this->id;
    }

}
