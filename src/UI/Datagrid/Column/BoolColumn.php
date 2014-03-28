<?php

namespace UI\Datagrid\Column;

use MyCLabs\MUIH\Button;
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
        $filterFormElement = new UI_Form_Element_Radio($this->getFilterFormId($datagrid));
        $filterFormElement->setLabel($this->getFilterFormLabel());

        $optionTrue = new UI_Form_Element_Option($this->getFilterFormId($datagrid).'_true', 1);
        $optionTrue->label = $this->keywordFilterEqual . ' '. $this->textTrue;
        $filterFormElement->addOption($optionTrue);

        $optionFalse = new UI_Form_Element_Option($this->getFilterFormId($datagrid).'_false', 0);
        $optionFalse->label = $this->keywordFilterEqual . ' '. $this->textFalse;
        $filterFormElement->addOption($optionFalse);

        // Récupération des valeurs par défaut.
        if (isset($defaultValue[$this->criteriaFilterOperator])) {
            $filterFormElement->setValue($defaultValue[$this->criteriaFilterOperator]);
        }

        $resetButton = new Button(new Icon($datagrid->filterIconResetFieldSuffix));
        $resetAction = '$(\'#'.$this->getFilterFormId($datagrid).' :checked\')';
        $resetAction .= '.removeAttr(\'checked\');';
        $resetButton->setAttribute('onclick', $resetAction);

        $resetElement = new UI_Form_Element_HTML($this->getFilterFormId($datagrid).'_reset');
        $resetElement->content = $resetButton->getHTML();

        $filterFormElement->getElement()->addElement($resetElement);

        return $filterFormElement;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterValue(Datagrid $datagrid)
    {
        $filterValue = '';

        $filterValue .= 'var inputOptions = $(\'#'.$this->getFilterFormId($datagrid).' :checked\');';

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
        return "$('#" . $this->getFilterFormId($datagrid) . " :checked').removeAttr('checked');";
    }

    /**
     * {@inheritdoc}
     */
    public function getAddFormElement(Datagrid $datagrid)
    {
        $addFormElement = new UI_Form_Element_Radio($this->getAddFormElementId($datagrid));
        $addFormElement->setLabel($this->getAddFormElementLabel());

        $optionTrue = new UI_Form_Element_Option($this->getAddFormElementId($datagrid).'_true', 1);
        $optionTrue->label = $this->textTrue;
        $addFormElement->addOption($optionTrue);

        $optionFalse = new UI_Form_Element_Option($this->getAddFormElementId($datagrid).'_false', 0);
        $optionFalse->label = $this->textFalse;
        $addFormElement->addOption($optionFalse);

        $addFormElement->setValue($this->defaultAddValue ? 1 : 0);

        return $addFormElement;
    }
}
