<?php
/**
 * @author     valentin.claras
 * @package    UI
 * @subpackage View
 */

/**
 * Helper de vue pour générer un collapse.
 * @package    Core
 * @subpackage View
 */
class UI_View_Helper_Collapse extends Zend_View_Helper_Abstract
{
    /**
     * @var UI_HTML_Collapse
     */
    protected $_collapse = null;


    /**
     * Retourne le render de l'actuel image de l'aide de vue.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->_collapse->getHTML().'<script type="text/javascript">'.$this->_collapse->getScript().'</script>';
    }

    /**
     * Génere un collapse html
     *
     * @param string $id
     * @param string $title
     * @param string $body
     * @param array $attributes
     *
     * @return string
     */
    public function collapse($id, $title, $body, $attributes=array())
    {
        $this->_collapse = new UI_HTML_Collapse($id, $title, $body);
        foreach ($attributes as $name => $value) {
            $this->_collapse->addAttribute($name, $value);
        }
        return $this;
    }

    /**
     * Définie le Collapse comme étant fermé par défaut.
     *
     * @return string
     */
    public function collapsed()
    {
        if (!($this->_collapse instanceof UI_HTML_Collapse)) {
            throw new Core_Exception_UndefinedAttribute('Invalid usage of ViewHelper Collapse, first call collapse.');
        }
        $this->_collapse->foldedByDefault = true;
        return $this;
    }

}