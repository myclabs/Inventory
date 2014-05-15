<?php
/**
 * Fichier de la classe Colonne Ordre.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage Datagrid
 */

/**
 * Description of colonne order.
 *
 * @deprecated
 *
 * @package    UI
 * @subpackage Datagrid
 *
 * Une classe permettant de générer une colonne gérant l'ordre des éléments.
 */
class UI_Datagrid_Col_Order extends UI_Datagrid_Col_Generic
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
     * Définition du message affiché lors du chargement de la liste.
     *
     * @var string
     */
    public $loadingText = null;

    /**
     * Label affiché sur le lien du goFirst.
     *
     * @var string
     */
    public $labelGoFirst = null;

    /**
     * Label affiché sur le lien du goFirst impossible.
     *
     * @var string
     */
    public $labelForbiddenGoFirst = null;

    /**
     * Label affiché sur le lien du goUp.
     *
     * @var string
     */
    public $labelGoUp = null;

    /**
     * Label affiché sur le lien du goUp impossible.
     *
     * @var string
     */
    public $labelForbiddenGoUp = null;

    /**
     * Label affiché sur le lien du goDown.
     *
     * @var string
     */
    public $labelGoDown = null;

    /**
     * Label affiché sur le lien du goDown impossible.
     *
     * @var string
     */
    public $labelForbiddenGoDown = null;

    /**
     * Label affiché sur le lien du goLast.
     *
     * @var string
     */
    public $labelGoLast = null;

    /**
     * Label affiché sur le lien du goLast impossible.
     *
     * @var string
     */
    public $labelForbiddenGoLast = null;

    /**
     * Label affiché dans le popup d'ajout pour mettre en premier.
     *
     * @var string
     */
    public $labelAddFirst = null;

    /**
     * Label affiché dans le popup d'ajout pour mettre en dernier.
     *
     * @var string
     */
    public $labelAddLast = null;

    /**
     * Label affiché dans le popup d'ajout pour mettre en premier.
     *
     * @var string
     */
    public $labelAddAfter = null;


    /*
     *  Attributs
     */

    /**
     * Permet d'ajouter une colonne plaçant l'objet en premier.
     *
     * @var bool
     */
    public $useGoFirst = true;

    /**
     * Permet d'ajouter une colonne remontant l'objet d'une place.
     *
     * @var bool
     */
    public $useGoUp = true;

    /**
     * Permet d'ajouter une colonne descendant l'objet d'une place.
     *
     * @var bool
     */
    public $useGoDown = true;

    /**
     * Permet d'ajouter une colonne plaçant l'objet en dernier.
     *
     * @var bool
     */
    public $useGoLast = true;

    /**
     * Url de l'action où récupérer la liste des posiiton pour l'ajout et le filtre.
     *
     * @var string
     */
    public $listPosition = null;


    /*
     *  Méthodes
     */

     /**
      * Constructeur de la classe Col_Order.
      *
      * @param string $id    Identifiant unique de la colonne.
      * @param string $label Texte afiché en titre de la colone.
      */
    public function __construct($id=null, $label=null)
    {
        if ($label === null) {
            $label = __('UI', 'name', 'order');
        }
        parent::__construct($id, $label);
        // Définition du type de la classe.
        $this->_type = self::TYPE_COL_ORDER;
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_CENTER;
        $this->keywordFilterEqual = __('UI', 'datagridFilter', 'ColOrderEqual');
        $this->keywordFilterLower = __('UI', 'datagridFilter', 'ColOrderLower');
        $this->keywordFilterHigher = __('UI', 'datagridFilter', 'ColOrderHigher');
        $this->filterOperator = Core_Model_Filter::OPERATOR_EQUAL;
        $this->filterOperatorLower = Core_Model_Filter::OPERATOR_LOWER;
        $this->filterOperatorHigher = Core_Model_Filter::OPERATOR_HIGHER;
        $this->loadingText = __('UI', 'loading', 'loading');
        $this->errorText = str_replace('\'', '\\\'', __('UI', 'loading', 'error'));
        $this->labelGoFirst = '<i class="fa fa-angle-double-up"></i>';
        $this->labelForbiddenGoFirst = '<i class="fa fa-angle-double-up"></i>';
        $this->labelGoUp = '<i class="fa fa-angle-up"></i>';
        $this->labelForbiddenGoUp = '<i class="fa fa-angle-up"></i>';
        $this->labelGoDown = '<i class="fa fa-angle-down"></i>';
        $this->labelForbiddenGoDown = '<i class="fa fa-angle-down"></i>';
        $this->labelGoLast = '<i class="fa fa-angle-double-down"></i>';
        $this->labelForbiddenGoLast = '<i class="fa fa-angle-double-down"></i>';
        $this->labelAddFirst = __('UI', 'other', 'first');
        $this->labelAddLast = __('UI', 'other', 'last');
        $this->labelAddAfter = __('UI', 'other', 'after');
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
        $format .= 'content = sData.value;';
        $format .= 'var canUp = sData.up;';
        $format .= 'var canDown = sData.down;';
        $format .= '}';


        if ($this->editable === true) {
            $functionUpdate = $datagrid->id.'.updateOrder'.$this->id;

            if ($this->useGoFirst === true || $this->useGoUp === true) {
                $format .= 'if (canUp) {';
            }
            if ($this->useGoUp === true) {
                $format .= 'content = \'';
                $format .= '<span ';
                $format .= 'onclick="'.$functionUpdate.'(\\\'goUp\\\',\\\'\' + oRecord._oData.index + \'\\\');"';
                $format .= '> ';
                $format .= $this->labelGoUp;
                $format .= '</span>';
                $format .= '\' + content;';
            }
            if ($this->useGoFirst === true) {
                $format .= 'content = \'';
                $format .= '<span ';
                $format .= 'onclick="'.$functionUpdate.'(\\\'goFirst\\\',\\\'\' + oRecord._oData.index + \'\\\');"';
                $format .= '> ';
                $format .= $this->labelGoFirst;
                $format .= '</span>';
                $format .= '\' + content;';
            }
            if ($this->useGoFirst === true || $this->useGoUp === true) {
                $format .= '} else {';
                $format .= 'content = \'';
                $format .= $this->labelForbiddenGoFirst;
                $format .= $this->labelForbiddenGoUp;
                $format .= '\' + content;';
                $format .= '}';
            }

            if ($this->useGoDown === true || $this->useGoLast === true) {
                $format .= 'if (canDown) {';
            }
            if ($this->useGoDown === true) {
                $format .= 'content += \'';
                $format .= ' <span ';
                $format .= 'onclick="'.$functionUpdate.'(\\\'goDown\\\',\\\'\' + oRecord._oData.index + \'\\\');"';
                $format .= '>';
                $format .= $this->labelGoDown;
                $format .= '</span>';
                $format .= '\';';
            }
            if ($this->useGoLast === true) {
                $format .= 'content += \'';
                $format .= ' <span ';
                $format .= 'onclick="'.$functionUpdate.'(\\\'goLast\\\',\\\'\' + oRecord._oData.index + \'\\\');"';
                $format .= '>';
                $format .= $this->labelGoLast;
                $format .= '</span>';
                $format .= '\';';
            }
            if ($this->useGoDown === true || $this->useGoLast === true) {
                $format .= '} else {';
                $format .= 'content += \'';
                $format .= $this->labelForbiddenGoDown;
                $format .= $this->labelForbiddenGoLast;
                $format .= '\';';
                $format .= '}';
            }
        }
        $format .= ';';

        return $format;
    }

    /**
     * Méthode renvoyant d'éventuelles fonctions complémentaires nécéssaires à la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    protected function getComplementaryFunction($datagrid)
    {
        $complementaryFunction = '';

        // Ajout d'une fonction permettant l'update de la position.
        if (($this->useGoFirst === true)
            || ($this->useGoUp === true)
            || ($this->useGoDown === true)
            || ($this->useGoLast === true)) {
            $complementaryFunction .= 'this.updateOrder'.$this->id.' = function(modif, index) {';
            $complementaryFunction .= $datagrid->id.'.StartLoading();';

            $complementaryFunction .= 'var params = \'index=\' + index + \'&column='.$this->id.'&value=\' + modif;';
            $complementaryFunction .= '$.post(';
            $complementaryFunction .= '\''.$datagrid->getActionUrl('updateelement').'\', ';
            $complementaryFunction .= 'params, ';
            $complementaryFunction .= 'function(data) {';
            $complementaryFunction .= 'if (data.message != "") {';
            $complementaryFunction .= 'addMessage(data.message, "success");';
            $complementaryFunction .= '}';
            if ($datagrid->pagination === true) {
                $complementaryFunction .= 'var paginator = '.$datagrid->id.'.Datagrid.getState().pagination.paginator;';
                $complementaryFunction .= 'var currentPage = paginator.getStartIndex() / paginator.getRowsPerPage();';
                $complementaryFunction .= $datagrid->id.'.filter(currentPage + 1);';
            } else {
                $complementaryFunction .= $datagrid->id.'.filter();';
            }
            $complementaryFunction .= $datagrid->id.'.EndLoading();';
            $complementaryFunction .= '}).error(function(data) {';
            $complementaryFunction .= 'errorHandler(data);';
            $complementaryFunction .= $datagrid->id.'.EndLoading();';
            $complementaryFunction .= '});';
            $complementaryFunction .= '};';
        }

        return $complementaryFunction;
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
        throw new Exception('Col Order needs to be refactored before being able to filter element.');
        $filterFormElement = new UI_Form_Element_Numeric($this->getFilterFormId($datagrid));
        $filterFormElement->setLabel($this->getFilterFormLabel());
        $filterFormElement->getElement()->addPrefix($this->keywordFilterEqual);

        // Récupération des valeurs par défaut.
        if (isset($defaultValue[$this->filterOperator])) {
            $filterFormElement->setValue($defaultValue[$this->filterOperator]);
        }

        $filterFormElement->getElement()->addSuffix($this->getResetFieldFilterFormSuffix($datagrid));

        // Champs pour le fitre <=.
        $filterFormElementInferior = new UI_Form_Element_Numeric($this->getFilterFormId($datagrid).'_lower');
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
        $filterFormElementSuperior = new UI_Form_Element_Numeric($this->getFilterFormId($datagrid).'_higher');
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
        throw new Exception('Col Order needs to be refactored before being able to filter element.');
        $filterValue = '';

        // Condition de saisie du filtre.
        $filterValue .= 'var valueEqu = $(\'#'.$this->getFilterFormId($datagrid).'\').val();';
        $filterValue .= 'var valueInf = $(\'#'.$this->getFilterFormId($datagrid).'_lower\').val();';
        $filterValue .= 'var valueSup = $(\'#'.$this->getFilterFormId($datagrid).'_higher\').val();';
        $filterValue .= 'if ((valueEqu != \'\') || (valueInf != \'\') || (valueSup != \'\')) {';

        // Ajout au filtre.
        $filterValue .= 'filter += "{\"'.$this->getFullFilterName($datagrid).'\": {";';
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
        $filterValue .= 'filter += "}},";';

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
        throw new Exception('Col Order needs to be refactored before being able to filter element.');
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
        throw new Exception('Col Order needs to be refactored before being able to add element.');
        $addFormElement = new UI_Form_Element_Radio($this->getAddFormElementId($datagrid));
        $addFormElement->setLabel($this->getAddFormElementLabel());

        $optionFirst = new UI_Form_Element_Option($this->getAddFormElementId($datagrid).'_first', 'first');
        $optionFirst->label = $this->labelAddFirst;
        $addFormElement->addOption($optionFirst);

        $optionLast = new UI_Form_Element_Option($this->getAddFormElementId($datagrid).'_last', 'last');
        $optionLast->label = $this->labelAddLast;
        $addFormElement->addOption($optionLast);

        if ($this->listPosition != null) {
            $optionAfter = new UI_Form_Element_Option($this->getAddFormElementId($datagrid).'_after', 'after');
            $optionAfter->label = $this->labelAddAfter;
            $addFormElement->addOption($optionAfter);

            $selectAfter = new UI_Form_Element_Select($this->getAddFormElementId($datagrid).'_select');

            $optionLoading = new UI_Form_Element_Option($this->getAddFormElementId($datagrid).'_load');
            $optionLoading->label = $this->loadingText;
            $selectAfter->addOption($optionLoading);
            $selectAfter->getElement()->hidden = true;

            $addFormElement->getElement()->addElement($selectAfter);

            $conditionShowSelect = new UI_Form_Condition_Elementary($this->getAddFormElementId($datagrid).'_equal');
            $conditionShowSelect->element = $addFormElement;
            $conditionShowSelect->relation = UI_Form_Condition::EQUAL;
            $conditionShowSelect->value = $optionAfter->value;

            $actionShowSelect = new UI_Form_Action_Show($this->getAddFormElementId($datagrid).'_show');
            $actionShowSelect->condition = $conditionShowSelect;

            $selectAfter->getElement()->addAction($actionShowSelect);

            $actionFillSelect = new UI_Form_Action_SetOptions($this->getAddFormElementId($datagrid).'_fill');
            $actionFillSelect->condition = $conditionShowSelect;
            $actionFillSelect->request = $this->listPosition;
            $actionFillSelect->backValue = array($this->loadingText);

            $selectAfter->getElement()->addAction($actionFillSelect);
        }

        $addFormElement->setValue($this->defaultAddValue);

        return $addFormElement;
    }

}
