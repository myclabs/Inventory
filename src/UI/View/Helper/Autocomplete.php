<?php
/**
 * @author     valentin.claras
 * @package    UI
 * @subpackage View
 */

/**
 * Helper de vue pour générer l'autocompletion sur un input.
 * @package    Core
 * @subpackage View
 */
class UI_View_Helper_Autocomplete extends Zend_View_Helper_Abstract
{
    /**
     * @var UI_JS_AutoComplete
     */
    protected $_autocomplete = null;


    /**
     * Retourne le render de l'actuel autocomplete de l'aide de vue.
     *
     * @return string
     */
    public function __toString()
    {
        UI_JS_AutoComplete::addHeader($this->_autocomplete);
        return $this->_autocomplete->getHTML();
    }

    /**
     * Génere le javascript de l'autocomplete pour une vue.
     *
     * @param string $id
     * @param string $source
     * @param string $placeholder
     *
     * @return UI_JS_AutoComplete
     */
    public function autocomplete($id, $source, $placeholder=null)
    {
        $this->_autocomplete = new UI_JS_AutoComplete($id, $source);
        $this->_autocomplete->placeholder = $placeholder;
        return $this;
    }

    /**
     * Spécifie le nombre minimum de caractère pour afficher le résultat.
     *
     * @param int $minimumInputLength
     *
     * @return UI_JS_AutoComplete
     */
    public function minimumInputLength($minimumInputLength)
    {
        $this->_autocomplete->minimumInputLength = $minimumInputLength;
        return $this;
    }

    /**
     * Spécifie si le nombre d'élément séléctionné peut être multiple.
     *
     * @param bool $multiple
     *
     * @return UI_JS_AutoComplete
     */
    public function multipleSelection($multiple)
    {
        $this->_autocomplete->multiple = $multiple;
        return $this;
    }

    /**
     * Ajoute un attribut HTML.
     *
     * @param string $attributeName
     * @param string $attributeValue
     *
     * @return UI_JS_AutoComplete
     */
    public function addAttribute($attributeName, $attributeValue)
    {
        $this->_autocomplete->addAttribute($attributeName, $attributeValue);
        return $this;
    }

    /**
     * Ajoute une option javascript.
     *
     * @param string $optionName
     * @param string $optionValue
     *
     * @return UI_JS_AutoComplete
     */
    public function addOption($optionName, $optionValue)
    {
        $this->_autocomplete->addOption($optionName, $optionValue);
        return $this;
    }

}