<?php

namespace UI\Datagrid\Column;

use UI\Datagrid\Datagrid;
use UI_Form_Element_Text;

/**
 * Classe représentant une colonne contenant des liens.
 *
 * @author valentin.claras
 */
class LinkColumn extends GenericColumn
{
    /**
     * Définition du mot clef du filtre pour l'égalité.
     *
     * @var string
     */
    public $keywordFilterEqual;

    /**
     * Définition de la valeur par defaut qui sera affiché dans la cellule.
     *
     * @var string
     */
    public $defaultValue;

    /**
     * Définit si la valeur de la cellule est l'url du lien (true) ou le texte affiché (false).
     *
     * Par défaut il s'agit de l'url du lien (true).
     *
     * @var bool
     */
    public $linkValue = true;


    public function __construct($id = null, $label = null)
    {
        parent::__construct($id, $label);
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_CENTER;
        $this->keywordFilterEqual = __('UI', 'datagridFilter', 'ColLinkEqual');
        $this->criteriaFilterOperator = 'contains';
        $this->defaultValue = '<i class="icon-share-alt"></i> '.__('UI', 'datagridContent', 'linkLabel');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter(Datagrid $datagrid)
    {
        $format = '';

        $format .= 'var url = \'\';';
        $format .= 'var text = \'\';';
        $format .= 'if (typeof(sData) != "object") {';
        if ($this->linkValue === true) {
            $format .= 'url = sData;';
            $format .= 'text = \''.addslashes($this->defaultValue).'\';';
        } else {
            $format .= 'url = \''.addslashes($this->linkValue).'\';';
            $format .= 'text = sData;';
        }
        $format .= '} else {';
        $format .= 'url = sData.value;';
        $format .= 'if (sData.content != null) {';
        $format .= 'text = sData.content;';
        $format .= '} else {';
        $format .= 'text = \''.addslashes($this->defaultValue).'\';';
        $format .= '}';
        $format .= '}';
        $format .= 'content = \'<a href="\' + url + \'">\' + text + \'</a>\';';

        return $format;
    }

    /**
     * {@inheritdoc}
     */
    public function getEditorValue(Datagrid $datagrid)
    {
        $editorValue = '';

        $editorValue .= 'this.onEventShowCellEditor(oArgs);';
        $editorValue .= 'if ((typeof(sData) == "undefined") || (sData == null)) {';
        $editorValue .= 'var content = \'\';';
        $editorValue .= '} else if (typeof(sData) != "object") {';
        $editorValue .= 'var content = sData.toString();';
        $editorValue .= '} else {';
        if ($this->linkValue !== true) {
            $editorValue .= 'var content = sData.content.toString();';
        } else {
            $editorValue .= 'var content = sData.value.toString();';
        }
        $editorValue .= '}';
        $editorValue .= 'column.editor.textbox.value = content;';

        return $editorValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterFormElement(Datagrid $datagrid, $defaultValue = null)
    {
        if ($this->linkValue !== true) {
            $filterFormElement = new UI_Form_Element_Text($this->getFilterFormId($datagrid));
            $filterFormElement->setLabel($this->getFilterFormLabel());
            $filterFormElement->getElement()->addPrefix($this->keywordFilterEqual);

            // Récupération des valeurs par défaut.
            if (isset($defaultValue[$this->criteriaFilterOperator])) {
                $filterFormElement->setValue($defaultValue[$this->criteriaFilterOperator]);
            }

            $filterFormElement->getElement()->addSuffix($this->getResetFieldFilterFormSuffix($datagrid));
        } else {
            $filterFormElement = null;
        }

        return $filterFormElement;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterValue(Datagrid $datagrid)
    {
        if ($this->linkValue !== true) {
            return parent::getFilterValue($datagrid);
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getResettingFilter(Datagrid $datagrid)
    {
        if ($this->linkValue !== true) {
            return parent::getResettingFilter($datagrid);
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAddFormElement(Datagrid $datagrid)
    {
        if ($this->linkValue !== true) {
            $addFormElement = new UI_Form_Element_Text($this->getAddFormElementId($datagrid));
            $addFormElement->setLabel($this->getAddFormElementLabel());
            $addFormElement->setValue($this->defaultAddValue);

            return $addFormElement;
        } else {
            return null;
        }
    }
}
