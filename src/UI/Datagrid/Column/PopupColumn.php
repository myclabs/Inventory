<?php

namespace UI\Datagrid\Column;

use MyCLabs\MUIH\Modal;
use UI\Datagrid\Datagrid;

/**
 * Classe représentant une colonne affichant des popups.
 *
 * @author valentin.claras
 */
class PopupColumn extends GenericColumn
{
    /**
     * Définition de la valeur par defaut qui sera affiché dans la cellule.
     *
     * @var string
     */
    public $defaultValue;

    /**
     * Popup Ajax qui sera affiché dans la colonne.
     *
     * @var Modal
     */
    public $popup;


    public function __construct($id = null, $label = null)
    {
        parent::__construct($id, $label);
        $this->popup = new Modal();
        $this->popup->large();
        $this->popup->ajax(true);
        $this->popup->addTitle($this->label);
        $this->popup->addDefaultDismissButton();
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_CENTER;
        $this->defaultValue = '<i class="fa fa-search-plus"></i> '.__('UI', 'name', 'details');
    }

    /**
     * Méthode renvoyant le popup de la colonne.
     *
     * @param Datagrid $datagrid
     *
     * @return Modal
     */
    public function getPopup(Datagrid $datagrid)
    {
        if ($this->popup === null) {
            $this->popup = new Modal();
            $this->popup->setAttribute('id', $datagrid->id.'_'.$this->id.'_popup');
            $this->popup->large();
            $this->popup->ajax(true);
            $this->popup->addTitle($this->label);
            $this->popup->addDefaultDismissButton();
        } else {
            $this->popup->setAttribute('id', $datagrid->id.'_'.$this->id.'_popup');
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
    public function getFilterFormElement(Datagrid $datagrid, $defaultValue = null)
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
