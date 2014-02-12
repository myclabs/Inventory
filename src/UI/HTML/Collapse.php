<?php
/**
 * Fichier de la classe Collapse.
 *
 * @author     valentin.claras
 * @package    UI
 * @subpackage HTML
 */

/**
 * Description of Collapse.
 *
 * @package UI
 * @subpackage HTML
 */
class UI_HTML_Collapse extends UI_Generic
{
    /**
     * Définition de l'icône présente à gauche de la légende.
     * Il s'agit de l'icône affiché lorsque le wrapper est plié.
     *
     * @var   string
     */
    public $iconFold = null;

    /**
     * Définition de l'icône présente à gauche de la légende.
     * Il s'agit de l'icône affiché lorsque le wrapper est déplié.
     *
     * @var   string
     */
    public $iconUnfold = null;

    /**
     * Définition de l'icône présente à gauche de la légende.
     * Il s'agit de l'icône affiché lorsque le wrapper est déroulé.
     *
     * @var   string
     */
    public $foldedByDefault = false;

    /**
     * Identifiant du wrapper.
     *
     * @var   string
     */
    public $id = null;

    /**
     * Légende du wrapper.
     *
     * @var   string
     */
    public $title = '';

    /**
     * Corps qui sera affichée dans le wrapper.
     *
     * @var   string
     */
    public $body = '';

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
     * Constructeur de la classe Collapse.
     *
     * @param string $id Identifiant unique du fieldset.
     * @param string $title Texte affiché dans la légende du wrapper.
     * @param string $body Texte affiché dans le wrapper.
     */
    public function  __construct($id=null, $title=null, $body=null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->body = $body;

        $this->_attributes['class'] = 'wrapper';
        $this->iconFold = 'chevron-right';
        $this->iconUnfold = 'chevron-down';
    }

    /**
     * Fonction permettant d'ajouter des attributs au bouton/lien.
     *
     * @param string $attributeName  Nom de l'attribut à ajouter.
     * @param string $attributeValue Valeur de l'attribut à ajouter.
     *
     * @return void
     */
    public function addAttribute($attributeName, $attributeValue)
    {
        switch ($attributeName) {
            case 'id':
                $this->id = $attributeValue;
                break;
            case 'class':
                $this->_attributes[$attributeName] = $this->_attributes[$attributeName].' '.$attributeValue;
                break;
            default:
                $this->_attributes[$attributeName] = $attributeValue;
                break;
        }
    }

    /**
     * Renvoi le javascript de l'interface.
     *
     * @return string
     */
    public function getScript()
    {
        $script = '';

        // if (e.currentTarget == e.target) {
        // Est nécéssaire car un groupe enfant lance aussi l'évent sur ses parents.

        // Modifie l'image down -> up lors du collapse show.
        $script .= '$(\'#'.$this->id.'_wrapper\').on(\'show\', function(e) {';
        $script .= 'if (e.currentTarget == e.target) {';
        $script .= '$(\'#'.$this->id.' > legend > i.filterChevron\').removeClass(\'fa-'.$this->iconFold.'\');';
        $script .= '$(\'#'.$this->id.' > legend > i.filterChevron\').addClass(\'fa-'.$this->iconUnfold.'\');';
        $script .= '}';
        $script .= '});';

        // Modifie l'image up -> down lors du collapse hide.
        $script .= '$(\'#'.$this->id.'_wrapper\').on(\'hidden\', function(e) {';
        $script .= 'if (e.currentTarget == e.target) {';
        $script .= '$(\'#'.$this->id.' > legend > i.filterChevron\').removeClass(\'fa-'.$this->iconUnfold.'\');';
        $script .= '$(\'#'.$this->id.' > legend > i.filterChevron\').addClass(\'fa-'.$this->iconFold.'\');';
        $script .= '}';
        $script .= '});';

        return $script;
    }

    /**
     * Fournit les propriétés principale au bouton ou au lien.
     *
     * @return string.
     */
    protected function renderMainProperties()
    {
        $properties = '';

        // Ajout de l'id.
        $properties .= 'id="'.$this->id.'" ';
        // Ajout des attributs optionnels.
        foreach ($this->_attributes as $name => $value) {
            $properties .= $name.'="'.$value.'" ';
        }
        $properties = substr($properties, 0, -1);

        return $properties;
    }

    /**
     * Génère le code HTML de la légende.
     *
     * @return string
     */
    protected function getLegend()
    {
        $legend = '';

        $legend .= '<legend ';
        $legend .= 'data-toggle="collapse" ';
        $legend .= 'data-target="#'.$this->id.'_wrapper" ';
        $legend .= '>';
        $chevron = $this->iconUnfold;
        if ($this->foldedByDefault === true) {
            $chevron = $this->iconFold;
        }
        $legend .= '<i class="filterChevron fa fa-'.$chevron.'"></i> ';
        $legend .= $this->title;
        $legend .= '</legend>';

        return $legend;
    }

    /**
     * Génère le code HTML du corps.
     *
     * @return string
     */
    protected function getBody()
    {
        $body = '';

        $body .= '<div ';
        $body .= 'id="'.$this->id.'_wrapper" ';
        $class = 'collapse';
        if ($this->foldedByDefault !== true) {
            $class = 'in '.$class;
            $body .= 'style="height: auto;" ';
        } else {
            $body .= 'style="height: 0px;" ';
        }
        $body .= 'class="'.$class.'"';
        $body .= '>';
        $body .= $this->body;
        $body .= '</div>';

        return $body;
    }

    /**
     * Génère le code HTML.
     *
     * @return string
     */
    public function getHTML()
    {
        $html = '';

        $html .= '<fieldset ';
        $html .= $this->renderMainProperties('fieldset');
        $html .= '>';

        $html .= $this->getLegend();

        $html .= $this->getBody();

        $html .= '</fieldset>';

        return $html;
    }

}