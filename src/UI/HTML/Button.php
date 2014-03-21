<?php
/**
 * Fichier de la classe Button.
 *
 * @author     valentin.claras
 * @package    UI
 * @subpackage HTML
 */

/**
 * Description of Button.
 *
 * Une classe permettant de générer un bouton très rapidement.
 *
 * @package UI
 * @subpackage HTML
 */
class UI_HTML_Button extends UI_Generic
{
    /**
     * Identifiant unique du bouton.
     *
     * @var   string
     */
    public $id = null;

    /**
     * Texte affiché sur le bouton.
     *
     * @var   string
     */
    public $label = null;

    /**
     * Icône qui pourra être affichée dans le bouton.
     *
     * @var   string
     *
     * @see   http://twitter.github.com/bootstrap/base-css.html#icons
     */
    public $icon = null;

    /**
     * URL vers laquelle peut pointer le bouton.
     *
     * @var   string
     */
    public $link = null;

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
     * Constructeur de la classe Button.
     *
     * @param string $label Texte affiché dans le bouton.
     */
    public function  __construct($label=null)
    {
        $this->label = $label;
        $this->_attributes['class'] = 'btn btn-default';
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
            case 'href':
                $this->link = $attributeValue;
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
     * Fournit le corps du bouton ou du lien.
     * @return string
     */
    protected function renderBody()
    {
        $body = '';

        // Ajoute l'icône si présente.
        if ($this->icon !== null) {
            $body .= '<i class="fa fa-'.$this->icon.'"></i> ';
        }
        // Ajoute le texte.
        $body .= $this->label;

        return $body;
    }

    /**
     * Génère le code HTML.
     *
     * @return string
     */
    public function getHTML()
    {
        // Formattage du texte pour permettre l'affichage.
        if ($this->label != null) {
            $this->label = preg_replace('#\r#', '', $this->label);
            $this->label = preg_replace('#\n#', '<br>', $this->label);
        }

        $html = '';

        if ($this->link !== null) {
            // Ouverture de la balise.
            $html .= '<a ';
            // Ajout des propiétés.
            $html .= $this->renderMainProperties();
            $html .= ' href="'.$this->link.'"';
            $html .= '>';
            // Ajout du texte et de l'éventuel image.
            $html .= $this->renderBody();
            // Fermeture de la balise.
            $html .= '</a>';
        } else {
            // Spécification d'un type de bouton par défaut.
            if (!isset($this->_attributes['type'])) {
                $this->_attributes['type'] = 'button';
            }
            // Ouverture de la balise.
            $html .= '<button ';
            // Ajout des propiétés.
            $html .= $this->renderMainProperties();
            $html .= '>';
            // Ajout du texte et de l'éventuel image.
            $html .= $this->renderBody();
            // Fermeture de la balise.
            $html .= '</button>';
        }

        return $html;
    }

}
