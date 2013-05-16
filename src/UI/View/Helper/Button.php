<?php
/**
 * @author     valentin.claras
 * @package    UI
 * @subpackage View
 */

/**
 * Helper de vue pour générer un bouton.
 * @package    Core
 * @subpackage View
 */
class UI_View_Helper_Button extends Zend_View_Helper_Abstract
{
    /**
     * @var UI_HTML_Button
     */
    protected $_button = null;


    /**
     * Retourne le render de l'actuel bouton de l'aide de vue.
     *
     * @return string
     */
    public function __toString()
    {
        UI_HTML_Button::addHeader($this->_button);
        return $this->_button->getHTML();
    }

    /**
     * Génere un bouton html.
     *
     * @param string $label
     * @param string $icon
     * @param array $attributes
     *
     * @return UI_View_Helper_Button
     */
    public function button($label, $icon=null, $attributes=array())
    {
        $this->_button = new UI_HTML_Button();
        $this->_button->label = $label;
        if ($icon !== null) {
            $this->_button->icon = $icon;
        }
        foreach ($attributes as $name => $value) {
            $this->_button->addAttribute($name, $value);
        }
        return $this;
    }

    /**
     * Ajoute un lien au bouton.
     *
     * @param string $url
     *
     * @return UI_View_Helper_Button
     */
    public function link($url)
    {
        if (!($this->_button instanceof UI_HTML_Button)) {
            throw new Core_Exception_UndefinedAttribute('Invalid usage of ViewHelper Button, first call button().');
        }
        $this->_button->link = $url;
        return $this;
    }

    /**
     * Génere un bouton html affichant un popup.
     *
     * @param string $idPopup
     *
     * @return UI_View_Helper_Button
     */
    public function showPopup($idPopup)
    {
        if (!($this->_button instanceof UI_HTML_Button)) {
            throw new Core_Exception_UndefinedAttribute('Invalid usage of ViewHelper Button, first call button().');
        }
        $this->_button->link = '#';
        $this->_button->addAttribute('data-toggle', 'modal');
        $this->_button->addAttribute('data-remote', 'false');
        $this->_button->addAttribute('data-target', '#'.$idPopup);
        return $this;
    }

    /**
     * Spécifie l'url du popup ajax à ouvrir.
     *
     * @param string $url
     *
     * @return UI_View_Helper_Button
     */
    public function dynamic($url)
    {
        if (!($this->_button instanceof UI_HTML_Button)) {
            throw new Core_Exception_UndefinedAttribute('Invalid usage of ViewHelper Button, first call button().');
        }
        $this->_button->link = $url;
        return $this;
    }

    /**
     * Génere un bouton html cachant un modal.
     *
     * @param string $idPopup
     *
     * @return UI_View_Helper_Button
     */
    public function closePopup($idPopup)
    {
        if (!($this->_button instanceof UI_HTML_Button)) {
            throw new Core_Exception_UndefinedAttribute('Invalid usage of ViewHelper Button, first call button().');
        }
        $this->_button->link = '#';
        $this->_button->addAttribute('data-dismiss', 'modal');
        $this->_button->addAttribute('data-target', '#'.$idPopup);
        return $this;
    }

    /**
     * Génere un bouton html qui soumet un formulaire
     *
     * @param string $idForm
     *
     * @return UI_View_Helper_Button
     */
    public function submitForm($idForm)
    {
        if (!($this->_button instanceof UI_HTML_Button)) {
            throw new Core_Exception_UndefinedAttribute('Invalid usage of ViewHelper Button, first call button().');
        }
        $this->_button->link = '#';
        $this->_button->addAttribute('onClick', '$(\'#' . $idForm . '\').submit(); return false;');
        return $this;
    }

}
