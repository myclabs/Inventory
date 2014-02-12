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
     * Locale optionnelle par défaut.
     * @var Core_Locale
     */
    protected static $default = null;

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
     * Nombre de chiffres significatifs minimum.
     *
     * Si le nombre de chiffres significatifs demandés est inférieur à ce minimum,
     * cette valeur sera utilisée.
     *
     * @var int
     */
    public static $minSignificantFigures = null;


    /**
     * Récupération de la locale demandée
     *
     * @param string $localeId
     * @return Core_Locale
     * @throws Core_Exception_InvalidArgument Locale inconnue
     */
    public static function load($localeId)
    {
        if (! Zend_Locale::isLocale($localeId)) {
            throw new Core_Exception_InvalidArgument("Locale inconnue : '$localeId'");
        }

        $locale = new Zend_Locale($localeId);
        if (! in_array($locale->getLanguage(), Zend_Registry::get('languages'))) {
            throw new Core_Exception_InvalidArgument("Locale non supportée : '$localeId'");
        }

        return new self($locale);
    }

    /**
     * Récupération de la locale par défaut
     *
     * @return Core_Locale
     */
    public static function loadDefault()
    {
        foreach (Zend_Locale::getBrowser() as $localeId => $quality) {
            $locale = new Zend_Locale($localeId);
            if (in_array($locale->getLanguage(), Zend_Registry::get('languages'))) {
                return new self($locale);
            }
        }

        if (self::$default !== null) {
            return self::$default;
        }
        return new self(new Zend_Locale(Zend_Registry::get('configuration')->translation->defaultLocale));
    }

    /**
     * Définition de la la locale par défaut
     *
     * @param Core_Locale $defaultLocale
     */
    public static function setDefault(Core_Locale $defaultLocale)
    {
        self::$default = $defaultLocale;
    }

    /**
     * Constructeur
     *
     * @param Zend_Locale $zendLocale
     */
    protected function __construct(Zend_Locale $zendLocale)
    {
        // Lien vers la locale Zend
        $this->zendLocale = $zendLocale;
    }

    /**
     * @return string ID de la locale
     */
    public function getId()
    {
        return $this->zendLocale->toString();
    }

    /**
     * @return string ID de la locale
     */
    public function getLanguage()
    {
        return $this->zendLocale->getLanguage();
    }

    /**
     * Formate un nombre pour l'affichage.
     *
     * @param float $number
     * @param int|null $significantFigures indique le nombre de chiffres Significatif
     * @param int|null $numberDecimal indique le nombre de décimales pour l'affichage du nombre.
     *                              Incompatible Avec les chiffres significatifs.
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
            if (self::$minSignificantFigures !== null &&
                $significantFigures < self::$minSignificantFigures
            ) {
                $significantFigures = self::$minSignificantFigures;
            }
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
     * Formate un nombre pour l'affichage dans un élément de saisie (champ de formulaire).
     * @param float $number
     * @return string
     */
    public function formatNumberForInput($number)
    {
        $options = array(
            'locale' => $this->zendLocale,
            'number_format' => '#0.###',
        );

        return Zend_Locale_Format::toNumber($number, $options);
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
     * @throws Core_Exception_InvalidArgument Le nombre saisi n'est pas reconnu comme un nombre
     */
    public function readNumber($input, $significantFigures = null, $numberDecimal = null)
    {
        if (trim($input) === '') {
            return null;
        }

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
            return (float) Zend_Locale_Format::getNumber($input, $options);
        } catch (Zend_Locale_Exception $e) {
            throw new Core_Exception_InvalidArgument("Le nombre saisi n'est pas reconnu comme un nombre.");
        }
    }

    /**
     * Récupère la valeur numérique entière d'une chaine de caractère
     *
     * @param string $input
     * @return int|null
     * @throws Core_Exception_InvalidArgument Le nombre saisi n'est pas reconnu comme un nombre entier
     */
    public function readInteger($input)
    {
        if (trim($input) === '') {
            return null;
        }

        $options = [
            'locale' => $this->zendLocale,
        ];

        try {
            return Zend_Locale_Format::getInteger($input, $options);
        } catch (Zend_Locale_Exception $e) {
            throw new Core_Exception_InvalidArgument("Le nombre saisi n'est pas reconnu comme un nombre entier.");
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
     * Formate une date en fonction de la locale
     *
     * @param DateTime|null $date
     *
     * @return string
     */
    public function formatShortDate(DateTime $date = null)
    {
        if ($date) {
            return $date->format('j M');
        } else {
            return '';
        }
    }

    /**
     * Formate une date en fonction de la locale
     *
     * @param DateTime|null $date
     *
     * @return string
     */
    public function formatDateTime(DateTime $date = null)
    {
        if ($date) {
            return $date->format('d/m/Y H:i');
        } else {
            return '';
        }
    }

    /**
     * Formate une date en fonction de la locale
     *
     * @param DateTime|null $date
     *
     * @return string
     */
    public function formatShortDateTime(DateTime $date = null)
    {
        if ($date) {
            return $date->format('j M H:i');
        } else {
            return '';
        }
    }

    /**
     * Formate une date en fonction de la locale
     *
     * @param DateTime|null $date
     *
     * @return string
     */
    public function formatTime(DateTime $date = null)
    {
        if ($date) {
            return $date->format('H \h i');
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

}
