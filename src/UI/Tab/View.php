<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Tab
 */

/**
 * Allow to create YUI Tabs
 *
 * @package UI
 * @subpackage Tab
 */
class UI_Tab_View extends UI_Generic
{
    /**
     * Unique identifiant
     *
     * @var string
     */
    public $id;

    /**
     * All of the Tabs belonging to the TabView.
     *
     * @var UI_Tab[]
     */
    protected $_tabs = array();

    /**
     * Tableau d'attributs optionnels.
     *
     * Permet de stocker d'autres attributs via la méthode addAttribute.
     * (name, class, onclick, ...)
     *
     * @var   array
     *
     * @see   addAttribute
     */
    protected $_attributes = array();


    /**
     * Constructor
     *
     * @param string $id
     *
     * @throws Core_Exception_Systeme
     *             if $id is unvalid
     */
    public function __construct($id)
    {
        $this->id = $id;

        $this->_attributes['class'] = 'nav nav-tabs';
    }

    /**
     * Add a Tab to the TabView
     *
     * @param UI_Tab $tab
     *
     * @throws Core_Exception_Systeme
     *             if $tab is unvalid
     */
    public function addTab(UI_Tab $tab)
    {
        if (!isset($tab)) {
            throw new Core_Exception_UndefinedAttribute('$tab is unvalid');
        }
        $this->_tabs[$tab->id] = $tab;
    }

    /**
     * Add specifics files to use the TabVIew.
     *
     * @param UI_Tab_View $instance Allows to set headers in line of the instance.
     */
    public static function addHeader($instance=null)
    {
        parent::addHeader($instance);
    }

    /**
     * Fournit les propriétés principale au bouton ou au lien.
     * @return string.
     */
    protected function renderMainProperties()
    {
        $properties = '';

        // Ajout de l'id.
        if ($this->id !== null) {
            $properties .= 'id="'.$this->id.'" ';
        }
        // Ajout des attributs optionnels.
        foreach ($this->_attributes as $name => $value) {
            $properties .= $name.'="'.$value.'" ';
        }
        $properties = substr($properties, 0, -1);

        return $properties;
    }

    /**
     * Renvoi le javascript de l'interface.
     *
     * @return string
     */
    public function getScript()
    {
        $script = '';

        foreach ($this->_tabs as $tab) {
            $script .= $tab->getScript($this);
        }

        return $script;
    }

    /**
     * Renvoi l'HTML de l'interface.
     *
     * @return string
     */
    public function getHTML()
    {
        $html = '';

        $html .= '<ul ';
        $html .= $this->renderMainProperties();
        $html .= '>';
        foreach ($this->_tabs as $tab) {
            $html .= $tab->getHTMLNav($this);
        }
        $html .= '</ul>';

        $html .= '<div class="tab-content">';
        foreach ($this->_tabs as $tab) {
            $html .= $tab->getHTMLTab($this);
        }
        $html .= '</div>';

        return $html;
    }
}