<?php
/**
 * Fichier de la classe Popup.
 *
 * @author     valentin.claras
 * @package    UI
 * @subpackage Popup
 */

/**
 * Description of popup.
 * @package    UI
 * @subpackage Popup
 *
 * Une classe mère (abstraite) permettant de générer des popups très rapidement
 */
abstract class UI_Popup_Generic extends UI_Generic
{
    /**
     * Constante definissant un type de popup
     * ici un popup statique et unique.
     */
    const TYPE_POPUP_STATIQUE = 'UI_Popup_Static';

    /**
     * Constante definissant un type de popup
     * ici un popup dont le contenu sera chargé en ajax.
     */
    const TYPE_POPUP_AJAX = 'UI_Popup_Ajax';

    /**
     * Niveau du titre du modal : par défaut h3.
     *
     * @var string
     */
    public $levelTitle = 'h3';

    /**
     * Définit si le popup peut-être fermé avec une croix dans le titre.
     *
     * @var string
     */
    public $closeWithCross = true;

    /**
     * Définit si le popup peut-être fermé avec la touche échap.
     *
     * @var string
     */
    public $closeWithEscape = true;

    /**
     * Définit si le popup peut-être fermé lors d'un clique sur le fond.
     *
     * @var string
     */
    public $closeWithClick = true;

    /**
     * Définit si le popup est modal : par défaut oui.
     *
     * @var string
     */
    public $modal = true;


    /**
     * Identifiant unique du popup.
     *
     * Cet identifiant sert à la fois pour l'html
     *  et pour le nom de l'objet javascript
     *
     * @var   string
     */
    public $id = null;

    /**
     * Texte du header.
     *
     * Ce texte est utilisé comme titre du popup
     *  si il reste nul, le popup n'aura pas de titre.
     *
     * @var   string
     */
    public $title = null;

    /**
     * Footer du popup.
     *
     * Il s'agit d'un tableau d'éléments qui seront affichés dans le footer.
     * Ces éléments peuvent être des UI_HTML_Boutons ou des chaînes html.
     * Ces éléments sont indexés par leur position (de gauche à droite)
     *
     * @var   string
     *
     * @see   addElementFooter
     */
    public $footer = null;

    /**
     * Tableau d'attributs optionnels.
     *
     * Permet de stocker des attributs YUI.
     *
     * @var   array
     *
     * @see   addAttribute
     */
    protected $_attributes = array();

    /**
     * Type du popup.
     *
     * Permet de connaître le type du popup depuis la classe mère.
     *
     * @var   const
     *
     * @see   TYPE_POPUP_STATIQUE
     * @see   TYPE_POPUP_AJAX
     */
    protected $_type = null;


    /**
     * Fonction permettant d'ajouter des attributs au popup.
     *
     * @param string $attributeName    Nom de l'attribut à ajouter.
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
     * Fournit le corps du popup.
     *
     * @return string
     */
    protected abstract function getBody();

    /**
     * Méthode affichant le popup.
     *
     * @return mixed (void|string) chaine html du popup.
     */
    public function getHTML()
    {
        $html = '';

        $html .= '<div id="'.$this->id.'" class="'.$this->_attributes['class'].'" ';
        if ($this->closeWithClick !== true) {
            $html .= 'data-backdrop="static" ';
        } else if ($this->modal !== true) {
            $html .= 'data-backdrop="false" ';
        }
        if ($this->closeWithEscape !== true) {
            $html .= 'data-keyboard="false" ';
        }
        $html = substr($html, 0, -1).'>';

        $html .= '<div class="modal-dialog">';
        $html .= '<div class="modal-content">';

        // Header.
        if ($this->title !== null) {
            $html .= '<div class="modal-header">';
            if ($this->closeWithCross === true) {
                $html .= '<button type="button" class="close" data-dismiss="modal" data-target="'.$this->id.'">';
                $html .= '×';
                $html .= '</button>';
            }
            $html .= '<'.$this->levelTitle.'>'.$this->title.'</'.$this->levelTitle.'>';
            $html .= '</div>';
        } else if ($this->closeWithCross === true) {
            $html .= '<button type="button" class="close" data-dismiss="modal">×&nbsp;</button>';
        }

        // Body.
        $html .= '<div class="modal-body">';
        $html .= '<p>'.$this->getBody().'</p>';
        $html .= '</div>';

        // Footer.
        if ($this->footer !== null) {
            $html .= '<div class="modal-footer">'.$this->footer.'</div>';
        }

        $html .= '</div>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

}