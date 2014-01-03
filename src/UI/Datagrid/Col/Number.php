<?php
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
        $filterFormElement = new UI_Form_Element_Numeric($this->getFilterFormId($datagrid));
        $filterFormElement->setLabel($this->getFilterFormLabel());
        $filterFormElement->getElement()->addPrefix($this->keywordFilterEqual);

        // Récupération des valeurs par défaut.
        if (isset($defaultValue[$this->filterOperator])) {
            $filterFormElement->setValue($defaultValue[$this->filterOperator]);
        }

        $filterFormElement->getElement()->addSuffix($this->getResetFieldFilterFormSuffix($datagrid));

        // Champs pour le fitre <=.
        $filterFormElementInferior = new UI_Form_Element_Text($this->getFilterFormId($datagrid).'_lower');
        $filterFormElementInferior->getElement()->addPrefix($this->keywordFilterLower);
        if (isset($defaultValue[$this->filterOperatorLower])) {
            $filterFormElementInferior->setValue($defaultValue[$this->filterOperatorLower]);
        }
        $resetFieldInferior = '<i ';
        $resetFieldInferior .= 'class="fa fa-'.$datagrid->filterIconResetFieldSuffix.' reset" ';
        $resetFieldInferior .= 'onclick="$(\'#'.$this->getFilterFormId($datagrid).'_lower\').val(\'\');"';
        $resetFieldInferior .= '>';
        $resetFieldInferior .= '</i>';
        $filterFormElementInferior->getElement()->addSuffix($resetFieldInferior);

        $filterFormElement->getElement()->addElement($filterFormElementInferior);

        // Champs pour le fitre >=.
        $filterFormElementSuperior = new UI_Form_Element_Text($this->getFilterFormId($datagrid).'_higher');
        $filterFormElementSuperior->getElement()->addPrefix($this->keywordFilterHigher);
        if (isset($defaultValue[$this->filterOperatorHigher])) {
            $filterFormElementSuperior->setValue($defaultValue[$this->filterOperatorHigher]);
        }
        $resetFieldSuperior = '<i ';
        $resetFieldSuperior .= 'class="fa fa-'.$datagrid->filterIconResetFieldSuffix.' reset" ';
        $resetFieldSuperior .= 'onclick="$(\'#'.$this->getFilterFormId($datagrid).'_higher\').val(\'\');"';
        $resetFieldSuperior .= '>';
        $resetFieldSuperior .= '</i>';
        $filterFormElementSuperior->getElement()->addSuffix($resetFieldSuperior);

        $filterFormElement->getElement()->addElement($filterFormElementSuperior);

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
        $addFormElement = new UI_Form_Element_Numeric($this->getAddFormElementId($datagrid));
        $addFormElement->setLabel($this->getAddFormElementLabel());
        $addFormElement->setValue($this->defaultAddValue);

        return $addFormElement;
    }

}