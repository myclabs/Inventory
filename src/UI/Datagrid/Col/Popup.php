<?php
/**
 * Fichier de la classe Colonne Popup.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage Datagrid
 */

/**
 * Description of colonne popup.
 *
 * Une classe permettant de générer une colonne contenant des popups.
 *
 * @deprecated
 *
 * @package    UI
 * @subpackage Datagrid
 */
class UI_Datagrid_Col_Popup extends UI_Datagrid_Col_Generic
{
    /**
     * Définition de la valeur par defaut qui sera affiché dans la cellule.
     *
     * @var   string
     */
    public $defaultValue = null;

    /**
     * Popup Ajax qui sera affiché dans la colonne.
     *
     * @var   UI_Popup_Ajax
     */
    public $popup = null;


     /**
      * Constructeur de la classe ColonnePopup.
      *
      * @param string $id    Identifiant unique de la colonne.
      * @param string $label Texte afiché en titre de la colone.
      */
    public function __construct($id=null, $label=null)
    {
        parent::__construct($id, $label);
        // Définition du type de la classe.
        $this->_type = self::TYPE_COL_POPUP;
        $this->popup = new UI_Popup_Ajax('temp');
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_CENTER;
        $this->defaultValue = '<i class="fa fa-search-plus"></i> '.__('UI', 'name', 'details');
    }

    /**
     * Méthode renvoyant le popup de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return UI_Popup_Ajax
     */
    public function getPopup($datagrid)
    {
        if ($this->popup === null) {
            $this->popup = new UI_Popup_Ajax($datagrid->id.'_'.$this->id.'_popup');
            $this->popup->addAttribute('class', 'large');
        } else {
            $this->popup->id = $datagrid->id.'_'.$this->id.'_popup';
        }
        return $this->popup;
    }

    /**
     * Méthode renvoyant le formatter de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getFormatter($datagrid)
    {
        $format = '';

        $format .= 'if (typeof(sData) != "object") {';
        $format .= 'var href = sData;';
        $format .= 'content = \''.addslashes($this->defaultValue).'\';';
        $format .= '} else {';
        $format .= 'var href = sData.value;';
        $format .= 'if (sData.content != null) {';
        $format .= 'content = sData.content;';
        $format .= '} else {';
        $format .= 'content = \''.addslashes($this->defaultValue).'\';';
        $format .= '}';
        $format .= 'if (href != null) {';
        $format .= 'content = \'<a href="\' + href + \'"';
        $format .= ' data-target="#'.$datagrid->id.'_'.$this->id.'_popup" data-toggle="modal" data-remote="false">\'';
        $format .= ' + content + \'</a>\';';
        $format .= '}';
        $format .= '}';

        return $format;
    }

    /**
     * Ajoute l'icone d'édition à la cellule.
     *
     * @return string
     */
    protected function addEditableFormatter()
    {
        // Pas d'édition possible sur des pourcentages.
        return '';
    }

    /**
     * Méthode renvoyant les options d'édition de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getEditableOption($datagrid)
    {
        // Pas d'édition possible sur des popups.
        return '';
    }

    /**
     * Méthode renvoyant l'appel à l'édition de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getEditorValue($datagrid)
    {
        // Pas d'édition possible sur des popups.
        return '';
    }

    /**
     * Méthode renvoyant le champs du filtre de la colonne.
     *
     * @param UI_Datagrid $datagrid
     * @param array $defaultValue Valeur par défaut du filtre (=null).
     *
     * @return Zend_Form_Element
     */
    public function getFilterFormElement($datagrid, $defaultValue=null)
    {
        // Pas de filtre possible sur des popups.
        return null;
    }

    /**
     * Méthode renvoyant la valeur du champs du filtre de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getFilterValue($datagrid)
    {
        // Pas de filtre possible sur des popups.
        return '';
    }

    /**
     * Méthode renvoyant la réinitialisation des champs du filtre de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getResettingFilter($datagrid)
    {
        // Pas de filtre posible sur des popups.
        return '';
    }

    /**
     * Méthode renvoyant le champs du formulaire d'ajout de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return Zend_Form_Element
     */
    public function getAddFormElement($datagrid)
    {
        // Pas d'ajout possible sur des popups.
        return null;
    }

}