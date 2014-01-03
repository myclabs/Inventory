<?php
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
        $filterFormElement = new UI_Form_Element_Radio($this->getFilterFormId($datagrid));
        $filterFormElement->setLabel($this->getFilterFormLabel());

        $optionTrue = new UI_Form_Element_Option($this->getFilterFormId($datagrid).'_true', 1);
        $optionTrue->label = $this->keywordFilterEqual . ' '. $this->textTrue;
        $filterFormElement->addOption($optionTrue);

        $optionFalse = new UI_Form_Element_Option($this->getFilterFormId($datagrid).'_false', 0);
        $optionFalse->label = $this->keywordFilterEqual . ' '. $this->textFalse;
        $filterFormElement->addOption($optionFalse);

        // Récupération des valeurs par défaut.
        if (isset($defaultValue[$this->filterOperator])) {
            $filterFormElement->setValue($defaultValue[$this->filterOperator]);
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
     * Méthode renvoyant la valeur du champs du filtre de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getFilterValue($datagrid)
    {
        $filterValue = '';

        $filterValue .= 'var inputOptions = $(\'#'.$this->getFilterFormId($datagrid).' :checked\');';

        // Condition de saisie du filtre.
        $filterValue .= 'if (inputOptions.length == 1) {';

        // Ajout au filtre.
        $filterValue .= 'filter += "\"'.$this->getFullFilterName($datagrid).'\": {';
        $filterValue .= '\"'.$this->filterOperator.'\":" + inputOptions.val() + "';
        $filterValue .= '},";';

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

        $resetFields .= '$(\'#'.$this->getFilterFormId($datagrid).' :checked\').removeAttr(\'checked\');';

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