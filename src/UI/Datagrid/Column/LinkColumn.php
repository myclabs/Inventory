<?php

namespace UI\Datagrid\Column;

use MyCLabs\MUIH\GenericTag;
use MyCLabs\MUIH\GenericVoidTag;
use UI\Datagrid\Datagrid;
use AF\Application\Form\Element\TextField;

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
        $this->defaultValue = '<i class="fa fa-external-link"></i> '.__('UI', 'datagridContent', 'linkLabel');
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
            $colWrapper = new GenericTag('div');
            $colWrapper->addClass('form-group');

            $colLabel = new GenericTag('label', $this->getFilterFormLabel());
            $colLabel->setAttribute('for', $this->getFilterFormId($datagrid));
            $colLabel->addClass('col-sm-2');
            $colLabel->addClass('control-label');
            $colWrapper->appendContent($colLabel);

            $linkWrapper = new GenericTag('div');
            $linkWrapper->addClass('col-sm-10');

            $linkInput = new GenericVoidTag('input');
            $linkInput->setAttribute('type', 'text');
            $linkInput->setAttribute('name', $this->getFilterFormId($datagrid));
            $linkInput->setAttribute('id', $this->getFilterFormId($datagrid));
            // Récupération des valeurs par défaut.
            if (isset($defaultValue[$this->criteriaFilterOperator])) {
                $linkInput->setAttribute('value', $defaultValue[$this->criteriaFilterOperator]);
            }
            $linkInput->addClass('form-control');

            $inputGroupWrapper = new GenericTag('div', $linkInput);
            $inputGroupWrapper->addClass('input-group');

            if (!empty($this->keywordFilterEqual)) {
                $keywordFilterPrefix = new GenericTag('span', $this->keywordFilterEqual);
                $keywordFilterPrefix->addClass('input-group-addon');
                $inputGroupWrapper->prependContent($keywordFilterPrefix);
            }

            $inputGroupWrapper->appendContent($this->getResetFieldFilterFormSuffix($datagrid));

            $linkWrapper->appendContent($inputGroupWrapper);

            $colWrapper->appendContent($linkWrapper);

            return $colWrapper;
        }

        return null;
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
        // Le'ajout est possible uniquement sur les Colonne Lien (lorsque la valeur de la cellule est le texte).
        if ($this->linkValue !== true) {
            $colWrapper = new GenericTag('div');
            $colWrapper->addClass('form-group');

            $colLabel = new GenericTag('label', $this->getAddFormElementLabel());
            $colLabel->setAttribute('for', $this->getAddFormElementId($datagrid));
            $colLabel->addClass('col-sm-2');
            $colLabel->addClass('control-label');
            $colLabel->addClass('field-label');
            $colWrapper->appendContent($colLabel);

            $linkWrapper = new GenericTag('div');
            $linkWrapper->addClass('col-sm-10');

            $linkInput = new GenericVoidTag('input');
            $linkInput->setAttribute('type', 'text');
            $linkInput->setAttribute('name', $this->getAddFormElementId($datagrid));
            $linkInput->setAttribute('id', $this->getAddFormElementId($datagrid));
            $linkInput->setAttribute('value', $this->defaultAddValue);
            $linkInput->addClass('form-control');
            $linkWrapper->appendContent($linkInput);

            $colWrapper->appendContent($linkWrapper);

            return $colWrapper;
        } else {
            return null;
        }
    }
}
