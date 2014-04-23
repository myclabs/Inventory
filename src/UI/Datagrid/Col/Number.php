<?php
use MyCLabs\MUIH\GenericTag;
use MyCLabs\MUIH\GenericVoidTag;

/**
 * Fichier de la classe Colonne Number.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage Datagrid
 */

/**
 * Description of colonne number.
 *
 * Une classe permettant de générer une colonne contenant des nombres.
 *
 * @deprecated
 *
 * @package    UI
 * @subpackage Datagrid
 */
class UI_Datagrid_Col_Number extends UI_Datagrid_Col_Generic
{
    /**
     * Définition du mot clef du filtre pour l'égalité.
     *
     * @var   string
     */
    public $keywordFilterEqual = null;

    /**
     * Définition du mot clef du filtre pour l'infériorité.
     *
     * @var   string
     */
    public $keywordFilterLower = null;

    /**
     * Définition du mot clef du filtre pour la supériorité.
     *
     * @var   string
     */
    public $keywordFilterHigher = null;

    /**
     * Définition de la constante utilisé pour le filtre inférieur sur la colonne.
     *
     * @var   string
     */
    public $filterOperatorLower = null;

    /**
     * Définition de la constante utilisé pour le filtre supérieur sur la colonne.
     *
     * @var   string
     */
    public $filterOperatorHigher = null;


     /**
      * Constructeur de la classe Col_Number.
      *
      * @param string $id    Identifiant unique de la colonne.
      * @param string $label Texte afiché en titre de la colone.
      */
    public function __construct($id=null, $label=null)
    {
        parent::__construct($id, $label);
        // Définition du type de la classe.
        $this->_type = self::TYPE_COL_NUMBER;
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_RIGHT;
        $this->keywordFilterEqual = __('UI', 'datagridFilter', 'ColNumberEqual');
        $this->keywordFilterLower = __('UI', 'datagridFilter', 'ColNumberLower');
        $this->keywordFilterHigher = __('UI', 'datagridFilter', 'ColNumberHigher');
        $this->filterOperator = Core_Model_Filter::OPERATOR_EQUAL;
        $this->filterOperatorLower = Core_Model_Filter::OPERATOR_LOWER;
        $this->filterOperatorHigher = Core_Model_Filter::OPERATOR_HIGHER;
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
        $format .= 'content = sData;';
        $format .= '} else {';
        $format .= 'if (sData.content != null) {';
        $format .= 'content = sData.content;';
        $format .= '} else {';
        $format .= 'content = sData.value;';
        $format .= '}';
        $format .= '}';

        return $format;
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
        $editorValue .= 'if ((typeof(sData) == "undefined") || (sData == null)) {';
        $editorValue .= 'var content = \'\';';
        $editorValue .= '} else if (typeof(sData) != "object") {';
        $editorValue .= 'var content = sData.toString();';
        $editorValue .= '} else {';
        $editorValue .= 'content = sData.value.toString();';
        $editorValue .= '}';
        $editorValue .= 'column.editor.textbox.value = content;';

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

        $colLabel = new GenericTag('label', $this->getFilterFormLabel());
        $colLabel->setAttribute('for', $this->getFilterFormId($datagrid));
        $colLabel->addClass('col-sm-2');
        $colLabel->addClass('control-label');
        $colWrapper->appendContent($colLabel);

        $numberWrapper = new GenericTag('div');
        $numberWrapper->addClass('col-sm-3');

        $numberInput = new GenericVoidTag('input');
        $numberInput->setAttribute('type', 'text');
        $numberInput->setAttribute('name', $this->getFilterFormId($datagrid));
        $numberInput->setAttribute('id', $this->getFilterFormId($datagrid));
        // Récupération des valeurs par défaut.
        if (isset($defaultValue[$this->filterOperator])) {
            $numberInput->setAttribute('value', $defaultValue[$this->filterOperator]);
        }
        $numberInput->addClass('form-control');

        $inputGroupWrapper = new GenericTag('div', $numberInput);
        $inputGroupWrapper->addClass('input-group');

        if (!empty($this->keywordFilterEqual)) {
            $keywordFilterPrefix = new GenericTag('span', $this->keywordFilterEqual);
            $keywordFilterPrefix->addClass('input-group-addon');
            $inputGroupWrapper->prependContent($keywordFilterPrefix);
        }

        $inputGroupWrapper->appendContent($this->getResetFieldFilterFormSuffix($datagrid));

        $numberWrapper->appendContent($inputGroupWrapper);

        $colWrapper->appendContent($numberWrapper);


        $numberInferiorWrapper = new GenericTag('div');
        $numberInferiorWrapper->addClass('col-sm-3');
        $numberInferiorWrapper->addClass('col-sm-offset-1');

        $numberInferiorInput = new GenericVoidTag('input');
        $numberInferiorInput->setAttribute('type', 'text');
        $numberInferiorInput->setAttribute('name', $this->getFilterFormId($datagrid).'_lower');
        $numberInferiorInput->setAttribute('id', $this->getFilterFormId($datagrid).'_lower');
        // Récupération des valeurs par défaut.
        if (isset($defaultValue[$this->filterOperatorLower])) {
            $numberInferiorInput->setAttribute('value', $defaultValue[$this->filterOperatorLower]);
        }
        $numberInferiorInput->addClass('form-control');

        $inputGroupWrapper = new GenericTag('div', $numberInferiorInput);
        $inputGroupWrapper->addClass('input-group');

        if (!empty($this->keywordFilterLower)) {
            $keywordFilterPrefix = new GenericTag('span', $this->keywordFilterLower);
            $keywordFilterPrefix->addClass('input-group-addon');
            $inputGroupWrapper->prependContent($keywordFilterPrefix);
        }

        $inputGroupWrapper->appendContent(
            $this->getResetFieldFilterFormSuffix($datagrid)
                ->setAttribute('onclick', '$(\'#'.$this->getFilterFormId($datagrid).'_lower\').val(\'\');')
        );

        $numberInferiorWrapper->appendContent($inputGroupWrapper);

        $colWrapper->appendContent($numberInferiorWrapper);


        $numberSuperiorWrapper = new GenericTag('div');
        $numberSuperiorWrapper->addClass('col-sm-3');

        $numberSuperiorInput = new GenericVoidTag('input');
        $numberSuperiorInput->setAttribute('type', 'text');
        $numberSuperiorInput->setAttribute('name', $this->getFilterFormId($datagrid).'_higher');
        $numberSuperiorInput->setAttribute('id', $this->getFilterFormId($datagrid).'_higher');
        // Récupération des valeurs par défaut.
        if (isset($defaultValue[$this->filterOperatorHigher])) {
            $numberSuperiorInput->setAttribute('value', $defaultValue[$this->filterOperatorHigher]);
        }
        $numberSuperiorInput->addClass('form-control');

        $inputGroupWrapper = new GenericTag('div', $numberSuperiorInput);
        $inputGroupWrapper->addClass('input-group');

        if (!empty($this->keywordFilterHigher)) {
            $keywordFilterPrefix = new GenericTag('span', $this->keywordFilterHigher);
            $keywordFilterPrefix->addClass('input-group-addon');
            $inputGroupWrapper->prependContent($keywordFilterPrefix);
        }

        $inputGroupWrapper->appendContent(
            $this->getResetFieldFilterFormSuffix($datagrid)
                ->setAttribute('onclick', '$(\'#'.$this->getFilterFormId($datagrid).'_higher\').val(\'\');')
        );

        $numberSuperiorWrapper->appendContent($inputGroupWrapper);

        $colWrapper->appendContent($numberSuperiorWrapper);

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

        // Condition de saisie du filtre.
        $filterValue .= 'var valueEqu = $(\'#'.$this->getFilterFormId($datagrid).'\').val();';
        $filterValue .= 'var valueInf = $(\'#'.$this->getFilterFormId($datagrid).'_lower\').val();';
        $filterValue .= 'var valueSup = $(\'#'.$this->getFilterFormId($datagrid).'_higher\').val();';
        $filterValue .= 'if ((valueEqu != \'\') || (valueInf != \'\') || (valueSup != \'\')) {';

        // Ajout au filtre.
        $filterValue .= 'filter += "\"'.$this->getFullFilterName($datagrid).'\": {";';
        $filterValue .= 'if (valueEqu != \'\') {';
        $filterValue .= 'filter += "\"'.$this->filterOperator.'\":\"" + valueEqu + "\"";';
        $filterValue .= '}';
        $filterValue .= 'if (valueInf != \'\') {';
        $filterValue .= 'if (valueEqu != \'\') {';
        $filterValue .= 'filter += ",";';
        $filterValue .= '}';
        $filterValue .= 'filter += "\"'.$this->filterOperatorLower.'\":\"" + valueInf + "\"";';
        $filterValue .= '}';
        $filterValue .= 'if (valueSup != \'\') {';
        $filterValue .= 'if ((valueEqu != \'\') || (valueInf != \'\')) {';
        $filterValue .= 'filter += ",";';
        $filterValue .= '}';
        $filterValue .= 'filter += "\"'.$this->filterOperatorHigher.'\":\"" + valueSup + "\"";';
        $filterValue .= '}';
        $filterValue .= 'filter += "},";';

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
    function getResettingFilter($datagrid)
    {
        $resetFields = '';

        $resetFields .= '$(\'#'.$this->getFilterFormId($datagrid).'\').val(\'\');';
        $resetFields .= '$(\'#'.$this->getFilterFormId($datagrid).'_lower\').val(\'\');';
        $resetFields .= '$(\'#'.$this->getFilterFormId($datagrid).'_higher\').val(\'\');';

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

        $numberWrapper = new GenericTag('div');
        $numberWrapper->addClass('col-sm-10');

        $numberInput = new GenericVoidTag('input');
        $numberInput->setAttribute('type', 'text');
        $numberInput->setAttribute('pattern', '-?[0-9]*[.,]?[0-9]*');
        $numberInput->setAttribute('name', $this->getAddFormElementId($datagrid));
        $numberInput->setAttribute('id', $this->getAddFormElementId($datagrid));
        $numberInput->setAttribute('value', $this->defaultAddValue);
        $numberInput->addClass('form-control');
        $numberWrapper->appendContent($numberInput);

        $colWrapper->appendContent($numberWrapper);

        return $colWrapper;
    }

}