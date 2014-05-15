<?php
use MyCLabs\MUIH\Button;
use MyCLabs\MUIH\GenericTag;
use MyCLabs\MUIH\GenericVoidTag;
use MyCLabs\MUIH\Icon;

/**
 * Fichier de la classe Colonne Booleen.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage Datagrid
 */

/**
 * Description of colonne bool.
 *
 * Une classe permettant de générer une colonne contenant des bools.
 *
 * @deprecated
 *
 * @package    UI
 * @subpackage Datagrid
 */
class UI_Datagrid_Col_Bool extends UI_Datagrid_Col_Generic
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
      * Constructeur de la classe Col_Bool.
      *
      * @param string $id    Identifiant unique de la colonne.
      * @param string $label Texte afiché en titre de la colone.
      */
    public function __construct($id=null, $label=null)
    {
        parent::__construct($id, $label);
        // Définition du type de la classe.
        $this->_type = self::TYPE_COL_BOOL;
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_CENTER;
        $this->keywordFilterEqual = __('UI', 'datagridFilter', 'ColBoolEqual');
        $this->filterOperator = Core_Model_Filter::OPERATOR_EQUAL;
        $this->textTrue = __('UI', 'property', 'true');
        $this->textFalse = __('UI', 'property', 'false');
        $this->valueTrue = '<i class="fa fa-check"></i> '.__('UI', 'property', 'true');
        $this->valueFalse = '<i class="fa fa-times"></i> '.__('UI', 'property', 'false');
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
     * Méthode renvoyant les options d'édition de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getEditableOption($datagrid)
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
     * Méthode renvoyant l'appel à l'édition de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getEditorValue($datagrid)
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
     * Méthode renvoyant le champs du filtre de la colonne.
     *
     * @param UI_Datagrid $datagrid
     * @param array $defaultValue Valeur par défaut du filtre (=null).
     *
     * @return Zend_Form_Element
     */
    public function getFilterFormElement($datagrid, $defaultValue=null)
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
        if (isset($defaultValue[$this->filterOperator]) && ($defaultValue[$this->filterOperator]) == true) {
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
        if (isset($defaultValue[$this->filterOperator]) && ($defaultValue[$this->filterOperator]) == false) {
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
     * Méthode renvoyant la valeur du champs du filtre de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getFilterValue($datagrid)
    {
        $filterValue = '';

        $filterValue .= 'var inputOptions = $(\'input[name="'.$this->getFilterFormId($datagrid).'"]:checked\');';

        // Condition de saisie du filtre.
        $filterValue .= 'if (inputOptions.length == 1) {';

        // Ajout au filtre.
        $filterValue .= 'filter += "{\"'.$this->getFullFilterName($datagrid).'\": {';
        $filterValue .= '\"'.$this->filterOperator.'\":" + inputOptions.val() + "';
        $filterValue .= '}},";';

        $filterValue .= '}';

        return $filterValue;
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
        $resetFields = '';

        $resetFields .= '$(\'input[name="'.$this->getFilterFormId($datagrid).'"]:checked\').removeAttr(\'checked\');';

        return $resetFields;
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