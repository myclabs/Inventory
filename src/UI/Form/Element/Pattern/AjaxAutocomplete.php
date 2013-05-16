<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Generate an aucompleted input text.
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Element_Pattern_AjaxAutocomplete extends UI_Form_Element_Text
{
    /**
     * @var UI_JS_AutoComplete
     */
    protected $_autocomplete = null;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $source
     * @param string $placeholder
     *
     * @throws Core_Exception_InvalidArgument if $name is not valid.
     */
    public function __construct($name, $source=null, $placeholder=null)
    {
        if (!is_string($source) && ($source !== null)) {
            throw new Core_Exception_InvalidArgument('The source needs to be a string, for fixed list use Select');
        }
        parent::__construct($name);

        $this->_autocomplete = new UI_JS_AutoComplete();
        $this->_autocomplete->source = $source;
        $this->_autocomplete->placeholder = $placeholder;
    }

    /**
     * @return UI_JS_AutoComplete
     */
    public function getAutocomplete()
    {
        return $this->_autocomplete;
    }

    /**
     * Utilisé par UI_Form pour fournir les scripts javascripts.
     *
     * @return string
     */
    public function getScript()
    {
        $this->_autocomplete->id = $this->getId();

        return $this->_autocomplete->getScript();
    }

}