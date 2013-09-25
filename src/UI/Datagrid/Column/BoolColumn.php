<?php
/**
 * Fichier de la classe BoolColumn.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage Datagrid
 */

namespace UI\Datagrid\Column;

use UI\Datagrid\Datagrid;
use UI_Form_Element_HTML;
use UI_Form_Element_Radio;
use UI_Form_Element_Option;
use UI_HTML_Button;

/**
 * Description of BoolColumn.
 *
 * Une classe permettant de générer une colonne contenant des booleens.
 *
 * @package    UI
 * @subpackage Datagrid
 */
class BoolColumn extends GenericColumn
{
    /**
     * Définition du mot clef du filtre pour l'égalité.
     *
     * @var   string
     */
    public $keywordFilterEqual = null;

    /**
     * Définition de la valeur qui sera affiché dans la cellule si le booléen vaut 'true'.
     *
     * @var   string
     */
    public $valueTrue = null;

    /**
     * Définition de la valeur qui sera affiché dans la cellule si le booléen vaut 'false'.
     *
     * @var   string
     */
    public $valueFalse = null;

    /**
     * Définition du texte qui sera affiché dans l'editeur pour coché la valeur 'true'.
     *
     * @var   string
     */
    public $textTrue = null;

    /**
     * Définition du texte qui sera affiché dans l'editeur pour coché la valeur 'false'.
     *
     * @var   string
     */
    public $textFalse = null;


     /**
      * {@inheritdoc}
      */
    public function __construct($id=null, $label=null)
    {
        parent::__construct($id, $label);
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_CENTER;
        $this->keywordFilterEqual = __('UI', 'datagridFilter', 'ColBoolEqual');
        $this->criteriaFilterOperator = 'eq';
        $this->textTrue = __('UI', 'property', 'true');
        $this->textFalse = __('UI', 'property', 'false');
        $this->valueTrue = '<i class="icon-ok"></i> '.__('UI', 'property', 'true');
        $this->valueFalse = '<i class="icon-remove"></i> '.__('UI', 'property', 'false');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter(Datagrid $datagrid)
    {
        $format = '';

        $format .= 'if (typeof(sData) != "object") {';
        $format .= 'var value = sData;';
        $format .= '} else {';
        $format .= 'var value = sData.value;';
        $format .= '}';
        $format .= 'if (value == true) {';
        $format .= 'content = \''.addslashes($this->valueTrue).'\';';
        $format .= '} else {';
        $format .= 'content = \''.addslashes($this->valueFalse).'\';';
        $format .= '}';

        return $format;
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
        $editorValue = '';

        $editorValue .= 'this.onEventShowCellEditor(oArgs);';
        $editorValue .= 'var radioSelect = column.editor.radios;';
        $editorValue .= 'if ((typeof(sData) != "undefined") && (sData !== null)) {';
        $editorValue .= 'if (typeof(sData) != "object") {';
        $editorValue .= 'var value = sData;';
        $editorValue .= '} else {';
        $editorValue .= 'var value = sData.value;';
        $editorValue .= '}';
        $editorValue .= 'if (value == true) {';
        $editorValue .= 'radioSelect[1].checked = "";';
        $editorValue .= 'radioSelect[0].checked = "checked";';
        $editorValue .= '} else {';
        $editorValue .= 'radioSelect[0].checked = "";';
        $editorValue .= 'radioSelect[1].checked = "checked";';
        $editorValue .= '}';
        $editorValue .= '} else {';
        $editorValue .= 'radioSelect[0].checked = "";';
        $editorValue .= 'radioSelect[1].checked = "";';
        $editorValue .= '}';

        return $editorValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterFormElement(Datagrid $datagrid, $defaultValue=null)
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

        $resetButton = new UI_HTML_Button();
        $resetButton->icon = $datagrid->filterIconResetFieldSuffix;
        $resetAction = '$(\'#'.$this->getFilterFormId($datagrid).' :checked\')';
        $resetAction .= '.removeAttr(\'checked\');';
        $resetButton->addAttribute('onclick', $resetAction);

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
        $resetFields = '';

        $resetFields .= '$(\'#'.$this->getFilterFormId($datagrid).' :checked\').removeAttr(\'checked\');';

        return $resetFields;
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

        $addFormElement->setValue(($this->defaultAddValue) ? 1 : 0);

        return $addFormElement;
    }

}