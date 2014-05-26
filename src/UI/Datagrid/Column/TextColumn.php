<?php

namespace UI\Datagrid\Column;

use MyCLabs\MUIH\GenericTag;
use MyCLabs\MUIH\GenericVoidTag;
use UI\Datagrid\Datagrid;
use AF\Application\Form\Element\TextField;

/**
 * Classe représentant une colonne contenant des textes.
 *
 * @author valentin.claras
 */
class TextColumn extends GenericColumn
{
    /**
     * Définition du mot clef du filtre pour l'égalité.
     *
     * @var string
     */
    public $keywordFilterEqual;


    public function __construct($id = null, $label = null)
    {
        parent::__construct($id, $label);
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_LEFT;
        $this->keywordFilterEqual = __('UI', 'datagridFilter', 'ColTextEqual');
        $this->criteriaFilterOperator = 'contains';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter(Datagrid $datagrid)
    {
        return <<<JS
if (typeof(sData) != "object") {
    content = sData;
} else {
    if (sData.content != null) {
        content = sData.content;
    } else {
        content = sData.value;
    }
}
JS;
    }

    /**
     * {@inheritdoc}
     */
    public function getEditorValue(Datagrid $datagrid)
    {
        return <<<JS
this.onEventShowCellEditor(oArgs);
if ((typeof(sData) == "undefined") || (sData == null)) {
    var content = '';
} else if (typeof(sData) != "object") {
    var content = sData.toString();
} else {
    var content = sData.value.toString();
}
column.editor.textbox.value = content;
JS;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterFormElement(Datagrid $datagrid, $defaultValue = null)
    {
        $colWrapper = new GenericTag('div');
        $colWrapper->addClass('form-group');

        $colLabel = new GenericTag('label', $this->getFilterFormLabel());
        $colLabel->setAttribute('for', $this->getFilterFormId($datagrid));
        $colLabel->addClass('col-sm-2');
        $colLabel->addClass('control-label');
        $colWrapper->appendContent($colLabel);

        $textWrapper = new GenericTag('div');
        $textWrapper->addClass('col-sm-10');

        $textInput = new GenericVoidTag('input');
        $textInput->setAttribute('type', 'text');
        $textInput->setAttribute('name', $this->getFilterFormId($datagrid));
        $textInput->setAttribute('id', $this->getFilterFormId($datagrid));
        // Récupération des valeurs par défaut.
        if (isset($defaultValue[$this->criteriaFilterOperator])) {
            $textInput->setAttribute('value', $defaultValue[$this->criteriaFilterOperator]);
        }
        $textInput->addClass('form-control');

        $inputGroupWrapper = new GenericTag('div', $textInput);
        $inputGroupWrapper->addClass('input-group');

        if (!empty($this->keywordFilterEqual)) {
            $keywordFilterPrefix = new GenericTag('span', $this->keywordFilterEqual);
            $keywordFilterPrefix->addClass('input-group-addon');
            $inputGroupWrapper->prependContent($keywordFilterPrefix);
        }

        $inputGroupWrapper->appendContent($this->getResetFieldFilterFormSuffix($datagrid));

        $textWrapper->appendContent($inputGroupWrapper);

        $colWrapper->appendContent($textWrapper);

        return $colWrapper;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddFormElement(Datagrid $datagrid)
    {
        $colWrapper = new GenericTag('div');
        $colWrapper->addClass('form-group');

        $colLabel = new GenericTag('label', $this->getAddFormElementLabel());
        $colLabel->setAttribute('for', $this->getAddFormElementId($datagrid));
        $colLabel->addClass('col-sm-2');
        $colLabel->addClass('control-label');
        $colLabel->addClass('field-label');
        $colWrapper->appendContent($colLabel);

        $textWrapper = new GenericTag('div');
        $textWrapper->addClass('col-sm-10');

        $textInput = new GenericVoidTag('input');
        $textInput->setAttribute('type', 'text');
        $textInput->setAttribute('name', $this->getAddFormElementId($datagrid));
        $textInput->setAttribute('id', $this->getAddFormElementId($datagrid));
        $textInput->setAttribute('value', $this->defaultAddValue);
        $textInput->addClass('form-control');
        $textWrapper->appendContent($textInput);

        $colWrapper->appendContent($textWrapper);

        return $colWrapper;
    }
}
