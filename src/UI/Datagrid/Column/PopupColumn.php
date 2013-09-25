<?php
/**
 * Fichier de la classe PopupColumn.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage Datagrid
 */

namespace UI\Datagrid\Column;

use UI\Datagrid\Datagrid;
use UI_Popup_Ajax;

/**
 * Description of PopupColumn.
 *
 * Une classe permettant de générer une colonne contenant des popups.
 *
 * @package    UI
 * @subpackage Datagrid
 */
class PopupColumn extends GenericColumn
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
        $this->popup = new UI_Popup_Ajax('temp');
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_CENTER;
        $this->defaultValue = '<i class="icon-zoom-in"></i> '.__('UI', 'name', 'details');
    }

    /**
     * Méthode renvoyant le popup de la colonne.
     *
     * @param Datagrid $datagrid
     *
     * @return UI_Popup_Ajax
     */
    public function getPopup(Datagrid $datagrid)
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
     * {@inheritdoc}
     */
    public function getFormatter(Datagrid $datagrid)
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
     * {@inheritdoc}
     */
    protected function addEditableFormatter()
    {
        // Pas d'édition possible sur des pourcentages.
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getEditableOption(Datagrid $datagrid)
    {
        // Pas d'édition possible sur des popups.
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getEditorValue(Datagrid $datagrid)
    {
        // Pas d'édition possible sur des popups.
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterFormElement(Datagrid $datagrid, $defaultValue=null)
    {
        // Pas de filtre possible sur des popups.
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterValue(Datagrid $datagrid)
    {
        // Pas de filtre possible sur des popups.
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getResettingFilter(Datagrid $datagrid)
    {
        // Pas de filtre posible sur des popups.
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getAddFormElement(Datagrid $datagrid)
    {
        // Pas d'ajout possible sur des popups.
        return null;
    }

}