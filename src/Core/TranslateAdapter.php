<?php
/**
 * @package    Core
 * @subpackage Translate
 */

/**
 * Classe Translate Adapter
 *
 * @package    Core
 * @subpackage Translate
 */
class Core_Translate_Adapter_Tmx extends Zend_Translate_Adapter
{

    // Attributs privés
    private $_file    = false;
    private $_useId   = true;
    private $_srclang = null;
    private $_tu      = null;
    private $_tuv     = null;
    private $_seg     = null;
    private $_content = null;
    private $_data    = array();

    /**
     * Charge les données depuis un fichier TMX.
     *
     * @param  string  $filename  Fichier TMX (chemin complet).
     * @param  string  $locale    Langue à charger (aucune utilité).
     * @param  array   $options   Options.
     *
     * @throws Zend_Translation_Exception
     *
     * @return array
     */
    protected function _loadTranslationData($filename, $locale, array $options = array())
    {
        $this->_data = array();
        if (!is_readable($filename)) {
            require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception('Le fichier de traduction \'' . $filename . '\' n\'est pas lisible.');
        }

        $encoding = $this->_findEncoding($filename);
        $this->_file = xml_parser_create($encoding);
        xml_set_object($this->_file, $this);
        xml_parser_set_option($this->_file, XML_OPTION_CASE_FOLDING, 0);
        xml_set_element_handler($this->_file, "_startElement", "_endElement");
        xml_set_character_data_handler($this->_file, "_contentElement");

        if (!xml_parse($this->_file, file_get_contents($filename))) {
            $ex = sprintf('XML error: %s at line %d in file %s',
                          xml_error_string(xml_get_error_code($this->_file)),
                          xml_get_current_line_number($this->_file),
                          $filename);
            xml_parser_free($this->_file);
            require_once 'Zend/Translate/Exception.php';
            $exception = new Zend_Translate_Exception($ex);
            Core_Error_Log::getInstance()->logException($exception);
            throw $exception;

        }

        // Si l'option folder n'est pas précisée, c'est qu'on ajoute des textes de l'application
        if (!isset($options['folder'])) {
            // If $option['folder'] is empty, this means that we are loading translations
            // from the languages (i.e.: the Core_Translate::dataFolder) sub folder of the current package
            // Thus, we should not load all the translations in the 'application' key of the data structure
            // storing all the translations
            // But we should instead store the translations contained in a sub folder A into the
            // 'A' key of this data structure (i.e.: $this->_loadedData)
            if (substr(dirname($filename), -strlen(Core_Translate::dataFolder), strlen(Core_Translate::dataFolder))
                != Core_Translate::dataFolder
            ) {
                $options['folder'] = basename(dirname($filename));
            }
            else {
                $options['folder'] = 'application';
            }
        }

        // On récupère le nom du fichier de traduction (sans l'extension)
        $file = basename($filename, '.tmx');

        // On construit le tableau des traductions classé par package et fichier
        $organisedData = array();
        // Si on ne récupère pas les traductions déjà existantes, la fusion des traductions dans Zend ne fonctionne
        // pas correctement. @todo : à comprendre un de ces jours (voir Zend_Translate_Adapter, ligne 661)
        // Fusion réalisée à l'aide de : $this->_translate[$key] = $temp[$key] + $this->_translate[$key];
        if (isset($this->_translate[$options['folder']][$file])) {
        	$organisedData[$options['folder']][$file] = $this->_translate[$options['folder']][$file];
        }
        foreach ($this->_data as $lang =>  $translate) {
            foreach ($translate as $key => $text) {
                // Si on ne doit pas écraser et que la clef existe déjà => on ne fait rien
                if (!isset($options['override'])
                    || $options['override']
                    || !isset($organisedData[$options['folder']][$file][$lang][$key])
                ) {
                    // Ajout de la traduction
                    $organisedData[$options['folder']][$file][$lang][$key] = $text;
                }
            }
        }

        return $organisedData;
    }

    /**
     * Renvoie le nom de l'adapter
     * @return string
     */
    public function toString()
    {
        return "UITmx";
    }

    /**
     * Charge le texte correspondant à la référence passée en paramètre.
     *
     * @param  string|array       $messageId     référence du texte.
     * @param  string             $file          (optional) fichier dans lequel charger l'extension.
     * @param  string             $folder        Emplacement où chercher la traduction (nom d'un
     *                      module comme par exemple "acl", "utilisateurs" OU "librairie" pour charger.
     * @param  string|Zend_Locale $locale        (optional) Locale/Langage à utiliser.
     *
     * @return string
     */
    public function _($messageId, $file = null, $folder = null, $locale = null)
    {
        return $this->translate($messageId, $file, $folder, $locale);
    }

    /**
     * Charge le texte correspondant à la référence passée en paramètre
     *
     * @see Zend_Locale
     * @param  string|array         $messageId référence du texte
     * @param  Core_Translate       $file      (optional) fichier dans lequel charger l'extension
     * @param  string               $folder    Emplacement où chercher la traduction (nom d'un
     *                      module comme par exemple "acl", "utilisateurs" OU "librairie" pour charger.
     * @param  string|Zend_Locale   $locale    (optional) Locale/Langage à utiliser
     *
     * @return string
     */
    public function translate($messageId, $file = null, $folder = null, $locale = null)
    {
        // Gestion de la langue
        if ($locale === null) {
            $locale = $this->_options['locale'];
        }

        $plural = null;
        if (is_array($messageId)) {
            if (count($messageId) > 2) {
                $number    = array_pop($messageId);
                if (!is_numeric($number)) {
                    $plocale = $number;
                    $number       = array_pop($messageId);
                } else {
                    $plocale = 'en';
                }

                $plural    = $messageId;
                $messageId = $messageId[0];
            } else {
                $messageId = $messageId[0];
            }
        }

        if (!Zend_Locale::isLocale($locale, true, false)) {
            if (!Zend_Locale::isLocale($locale, false, false)) {
                // le langage n'existe pas on renvoie la référence
                $this->_log($messageId, $locale);
                if ($plural === null) {
                    return $messageId;
                }

                $rule = Zend_Translate_Plural::getPlural($number, $plocale);
                if (!isset($plural[$rule])) {
                    $rule = 0;
                }

                return $plural[$rule];
            }

            $locale = new Zend_Locale($locale);
        }

        $locale = (string) $locale;

        // Récupération du texte

        // Fichier(s) dans le(s)quel(s) on va chercher les traductions
        $files = array();
        if (!is_null($file)) {
            $files[] = $file;
        } else {
            foreach ($this->_translate as $loadedFolder => $loadedFiles) {
                foreach ($loadedFiles as $file => $datas) {
                    $files[] = $file;
                }
            }
        }

        // Dossier dans le(s)quel(s) on va chercher les traductions
        $folders = array();
        if (!is_null($folder)) {
            $folders[] = $folder;
        } else {
            foreach ($this->_translate as $folder => $datas) {
                $folders[] = $folder;
            }
        }

        foreach ($folders as $folder) {
            foreach ($files as $file) {
                if (isset($this->_translate[$folder][$file][$locale][$messageId])) {
                    // return original translation
                    if ($plural === null) {
                        return $this->_translate[$folder][$file][$locale][$messageId];
                    }

                    $rule = Zend_Translate_Plural::getPlural($number, $locale);
                    if (isset($this->_translate[$folder][$file][$locale][$plural[0]][$rule])) {
                        return $this->_translate[$folder][$file][$locale][$plural[0]][$rule];
                    }
                } else if (strlen($locale) != 2) {
                    // faster than creating a new locale and separate the leading part
                    $locale = substr($locale, 0, -strlen(strrchr($locale, '_')));

                    if (isset($this->_translate[$folder][$file][$locale][$messageId])) {
                        // return regionless translation (en_US -> en)
                        if ($plural === null) {
                            return $this->_translate[$folder][$file][$locale][$messageId];
                        }

                        $rule = Zend_Translate_Plural::getPlural($number, $locale);
                        if (isset($this->_translate[$folder][$file][$locale][$plural[0]][$rule])) {
                            return $this->_translate[$folder][$file][$locale][$plural[0]][$rule];
                        }
                    }
                }
            }
        }

        $this->_log($messageId, $locale);
        if ($plural === null) {
            return $messageId;
        }

        $rule = Zend_Translate_Plural::getPlural($number, $plocale);
        if (!isset($plural[$rule])) {
            $rule = 0;
        }

        return $plural[$rule];

    }

    /**
     * Etablie la langue à utiliser.
     *
     * @param  string|Zend_Locale $locale Langue à utiliser.
     *
     * @throws Zend_Translate_Exception
     *
     * @return Zend_Translate_Adapter Provides fluent interface.
     */
    public function setLocale($locale)
    {
        if (($locale === "auto") || ($locale === null)) {
            $locale = Core_Locale::loadDefault()->getId();
        } else {
            try {
                $locale = Core_Locale::load($locale)->getId();
            } catch (Core_Exception_InvalidArgument $e) {
                $locale = Core_Locale::loadDefault()->getId();
            }
        }

        if ($this->_options['locale'] != $locale) {
            $this->_options['locale'] = $locale;

            if (isset(self::$_cache)) {
                $id = 'Zend_Translate_' . $this->toString() . '_Options';
                self::$_cache->save( serialize($this->_options), $id, array('Zend_Translate'));
            }
        }

        return $this;

    }

    /**
     * Les méthodes qui suivent sont des copies de celles de l'adapter TMX de Zend-1.11.11
     */

    /**
     * Internal method, called by xml element handler at start
     *
     * @param resource $file   File handler
     * @param string   $name   Elements name
     * @param array    $attrib Attributes for this element
     */
    protected function _startElement($file, $name, $attrib)
    {
        if ($this->_seg !== null) {
            $this->_content .= "<".$name;
            foreach ($attrib as $key => $value) {
                $this->_content .= " $key=\"$value\"";
            }
            $this->_content .= ">";
        } else {
            switch(strtolower($name)) {
                case 'header':
                    if (empty($this->_useId) && isset($attrib['srclang'])) {
                        if (Zend_Locale::isLocale($attrib['srclang'])) {
                            $this->_srclang = Zend_Locale::findLocale($attrib['srclang']);
                        } else {
                            if (!$this->_options['disableNotices']) {
                                if ($this->_options['log']) {
                                    $this->_options['log']->notice("The language '{$attrib['srclang']}'"
                                                                   ." can not be set because it does not exist.");
                                } else {
                                    trigger_error("The language '{$attrib['srclang']}' can not be set because"
                                                  ." it does not exist.", E_USER_NOTICE);
                                }
                            }

                            $this->_srclang = $attrib['srclang'];
                        }
                    }
                    break;
                case 'tu':
                    if (isset($attrib['tuid'])) {
                        $this->_tu = $attrib['tuid'];
                    }
                    break;
                case 'tuv':
                    if (isset($attrib['xml:lang'])) {
                        if (Zend_Locale::isLocale($attrib['xml:lang'])) {
                            $this->_tuv = Zend_Locale::findLocale($attrib['xml:lang']);
                        } else {
                            if (!$this->_options['disableNotices']) {
                                if ($this->_options['log']) {
                                    $this->_options['log']->notice("The language '{$attrib['xml:lang']}'"
                                                                   ." can not be set because it does not exist.");
                                } else {
                                    trigger_error("The language '{$attrib['xml:lang']}' can not be set because"
                                                  ." it does not exist.", E_USER_NOTICE);
                                }
                            }

                            $this->_tuv = $attrib['xml:lang'];
                        }

                        if (!isset($this->_data[$this->_tuv])) {
                            $this->_data[$this->_tuv] = array();
                        }
                    }
                    break;
                case 'seg':
                    $this->_seg     = true;
                    $this->_content = null;
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Internal method, called by xml element handler at end
     *
     * @param resource $file   File handler
     * @param string   $name   Elements name
     */
    protected function _endElement($file, $name)
    {
        if (($this->_seg !== null) and ($name !== 'seg')) {
            $this->_content .= "</".$name.">";
        } else {
            switch (strtolower($name)) {
                case 'tu':
                    $this->_tu = null;
                    break;
                case 'tuv':
                    $this->_tuv = null;
                    break;
                case 'seg':
                    $this->_seg = null;
                    if (!empty($this->_srclang) && ($this->_srclang == $this->_tuv)) {
                        $this->_tu = $this->_content;
                    }

                    if (!empty($this->_content) or (!isset($this->_data[$this->_tuv][$this->_tu]))) {
                        $this->_data[$this->_tuv][$this->_tu] = $this->_content;
                    }
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Internal method, called by xml element handler for content
     *
     * @param resource $file File handler
     * @param string   $data Elements content
     */
    protected function _contentElement($file, $data)
    {
        if (($this->_seg !== null) and ($this->_tu !== null) and ($this->_tuv !== null)) {
            $this->_content .= $data;
        }
    }

    /**
     * Internal method, detects the encoding of the xml file
     *
     * @param string $name Filename
     * @return string Encoding
     */
    protected function _findEncoding($filename)
    {
        $file = file_get_contents($filename, null, null, 0, 100);
        if (strpos($file, "encoding") !== false) {
            $encoding = substr($file, strpos($file, "encoding") + 9);
            $encoding = substr($encoding, 1, strpos($encoding, $encoding[0], 1) - 1);
            return $encoding;
        }
        return 'UTF-8';
    }

    /*
     * Fin des méthodes copiées de l'adapter TMX de Zend
     */


    /**
     * Logs a message when the log option is set
     *
     * @param string $message Message to log
     * @param String $locale  Locale to log
     */
    protected function _log($message, $locale) {
        if ($this->_options['logUntranslated']) {
            $message = str_replace('%message%', $message, $this->_options['logMessage']);
            $message = str_replace('%locale%', $locale, $message);
            /** @var $logger Core_Error_Log */
            $logger = Core_Error_Log::getInstance();
            $logger->warning($message);
        }
    }

}
