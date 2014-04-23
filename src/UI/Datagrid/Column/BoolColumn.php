<?php

namespace UI\Datagrid\Column;

use MyCLabs\MUIH\Button;
use MyCLabs\MUIH\GenericTag;
use MyCLabs\MUIH\GenericVoidTag;
use MyCLabs\MUIH\Icon;
use UI\Datagrid\Datagrid;
use UI_Form_Element_HTML;
use UI_Form_Element_Radio;
use UI_Form_Element_Option;

/**
 * Classe représentant une colonne contenant des booleens.
 *
 * @author valentin.claras
 */
class BoolColumn extends GenericColumn
{
    /**
     * Définition du mot clef du filtre pour l'égalité.
     *
     * @var string
     */
    public $keywordFilterEqual;

    /**
     * Définition de la valeur qui sera affiché dans la cellule si le booléen vaut 'true'.
     *
     * @var string
     */
    public $valueTrue;

    /**
     * Définition de la valeur qui sera affiché dans la cellule si le booléen vaut 'false'.
     *
     * @var string
     */
    public $valueFalse;

    /**
     * Définition du texte qui sera affiché dans l'editeur pour coché la valeur 'true'.
     *
     * @var string
     */
    public $textTrue;

    /**
     * Définition du texte qui sera affiché dans l'editeur pour coché la valeur 'false'.
     *
     * @var string
     */
    public $textFalse;


    public function __construct($id = null, $label = null)
    {
        parent::__construct($id, $label);
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_CENTER;
        $this->keywordFilterEqual = __('UI', 'datagridFilter', 'ColBoolEqual');
        $this->criteriaFilterOperator = 'eq';
        $this->textTrue = __('UI', 'property', 'true');
        $this->textFalse = __('UI', 'property', 'false');
        $this->valueTrue = '<i class="fa fa-check"></i> '.__('UI', 'property', 'true');
        $this->valueFalse = '<i class="fa fa-times"></i> '.__('UI', 'property', 'false');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter(Datagrid $datagrid)
    {
        $escapedTrueValue = addslashes($this->valueTrue);
        $escapedFalseValue = addslashes($this->valueFalse);

        return <<<JS
if (typeof(sData) != "object") {
    var value = sData;
} else {
    var value = sData.value;
}
if (value == true) {
    content = '$escapedTrueValue';
} else {
    content = '$escapedFalseValue';
}
JS;
    }

    /**
     * {@inheritdoc}
     */
    public function getEditableOption(Datagrid $datagrid)
    {
        $editOption = '';

        $editOption .= ', editor:new YAHOO.widget.RadioCellEditor({multiple:false,';
        $editOption .= ' radioOptions:[';
        $editOption .= '{value:"1",label:"'.$this->textTrue.'"},';
        $editOption .= '{value:"0",label:"'.$this->textFalse.'"}';
        $editOption .= '],';
        $editOption .= 'asyncSubmitter: function(callback, newValue) {},';
        $editOption .= 'LABEL_SAVE: \''.$this->editLabelSave.'\',';
        $editOption .= 'LABEL_CANCEL: \''.$this->editLabelCancel.'\'';
        $editOption .= '})';

        return $editOption;
    }

    /**
     * {@inheritdoc}
     */
    public function getEditorValue(Datagrid $datagrid)
    {
        return <<<JS
this.onEventShowCellEditor(oArgs);
var radioSelect = column.editor.radios;
if ((typeof(sData) != "undefined") && (sData !== null)) {
    if (typeof(sData) != "object") {
        var value = sData;
    } else {
        var value = sData.value;
    }
    if (value == true) {
        radioSelect[1].checked = "";
        radioSelect[0].checked = "checked";
    } else {
        radioSelect[0].checked = "";
        radioSelect[1].checked = "checked";
    }
} else {
    radioSelect[0].checked = "";
    radioSelect[1].checked = "";
}
JS;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterFormElement(Datagrid $datagrid, $defaultValue = null)
    {
        $colWrapper = new GenericTag('div');
        $colWrapper->addClass('form-group');

        $colLabel = new GenericTag('span', $this->getFilterFormLabel());
        $colLabel->addClass('col-sm-2');
        $colLabel->addClass('control-label');
        $colLabel->addClass('static');
        $colWrapper->appendContent($colLabel);

        $boolWrapper = new GenericTag('div');
        $boolWrapper->addClass('col-sm-10');

        $trueOption = new GenericVoidTag('input');
        $trueOption->setAttribute('type', 'radio');
        $trueOption->setAttribute('name', $this->getFilterFormId($datagrid));
        $trueOption->setAttribute('id', $this->getFilterFormId($datagrid).'_true');
        $trueOption->setAttribute('value', '1');
        // Récupération des valeurs par défaut.
        if (isset($defaultValue[$this->criteriaFilterOperator]) && ($defaultValue[$this->criteriaFilterOperator]) == true) {
            $trueOption->setBooleanAttribute('checked');
        }

        $trueLabel = new GenericTag('label', $this->keywordFilterEqual . ' '. $this->textTrue);
        $trueLabel->prependContent(' ');
        $trueLabel->prependContent($trueOption);
        $boolWrapper->appendContent($trueLabel);

        $falseOption = new GenericVoidTag('input');
        $falseOption->setAttribute('type', 'radio');
        $falseOption->setAttribute('name', $this->getFilterFormId($datagrid));
        $falseOption->setAttribute('id', $this->getFilterFormId($datagrid).'_false');
        $falseOption->setAttribute('value', '1');
        // Récupération des valeurs par défaut.
        if (isset($defaultValue[$this->criteriaFilterOperator]) && ($defaultValue[$this->criteriaFilterOperator]) == false) {
            $falseOption->setBooleanAttribute('checked');
        }

        $falseLabel = new GenericTag('label', $this->keywordFilterEqual . ' '. $this->textFalse);
        $falseLabel->prependContent(' ');
        $falseLabel->prependContent($falseOption);
        $boolWrapper->appendContent(' ');
        $boolWrapper->appendContent($falseLabel);

        $resetFieldIcon = new Icon($datagrid->filterIconResetFieldSuffix);
        $resetFieldIcon->addClass('reset');

        $resetFieldSuffix = new Button($resetFieldIcon);
        $resetFieldSuffix->setAttribute('onclick', '$(\'input[name=\\\''.$this->getFilterFormId($datagrid).'\\\']:checked\').removeAttr(\'checked\');');
        $boolWrapper->appendContent(' ');
        $boolWrapper->appendContent($resetFieldSuffix);

        $colWrapper->appendContent($boolWrapper);

        return $colWrapper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterValue(Datagrid $datagrid)
    {
        $filterValue = '';

        $filterValue .= 'var inputOptions = $(\'input[name="'.$this->getFilterFormId($datagrid).'"]:checked\');';

        // Condition de saisie du filtre.
        $filterValue .= 'if (inputOptions.length == 1) {';

        // Ajout au filtre.
        $filterValue .= 'filter += "\"'.$this->getFullFilterName($datagrid).'\": {';
        $filterValue .= '\"'.$this->criteriaFilterOperator.'\":" + inputOptions.val() + "';
        $filterValue .= '},";';

        $filterValue .= '}';

        return $filterValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getResettingFilter(Datagrid $datagrid)
    {
        $resetFields = '';

        $resetFields .= '$(\'input[name="'.$this->getFilterFormId($datagrid).'"]:checked\').removeAttr(\'checked\');';

        return $resetFields;
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

        $optionsWrapper = new GenericTag('div');
        $optionsWrapper->setAttribute('id', $this->getAddFormElementId($datagrid));
        $optionsWrapper->addClass('col-sm-10');

        $trueInput = new GenericVoidTag('input');
        $trueInput->setAttribute('type', 'radio');
        $trueInput->setAttribute('name', $this->getAddFormElementId($datagrid));
        $trueInput->setAttribute('value', 1);
        $trueInput->setAttribute('id', $this->getAddFormElementId($datagrid).'_true');
        $trueOption = new GenericTag('label');
        $trueOption->addClass('radio-inline');
        $trueOption->appendContent($trueInput);
        $trueOption->appendContent($this->textTrue);
        $optionsWrapper->appendContent($trueOption);

        $falseInput = new GenericVoidTag('input');
        $falseInput->setAttribute('type', 'radio');
        $falseInput->setAttribute('name', $this->getAddFormElementId($datagrid));
        $falseInput->setAttribute('value', 0);
        $falseInput->setAttribute('id', $this->getAddFormElementId($datagrid).'_false');
        $falseOption = new GenericTag('label');
        $falseOption->addClass('radio-inline');
        $falseOption->appendContent($falseInput);
        $falseOption->appendContent($this->textFalse);
        $optionsWrapper->appendContent($falseOption);

        $colWrapper->appendContent($optionsWrapper);

        if ($this->defaultAddValue) {
            $trueInput->setBooleanAttribute('checked');
        } else {
            $falseInput->setBooleanAttribute('checked');
        }

        return $colWrapper;
    }
}
