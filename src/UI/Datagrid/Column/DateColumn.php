<?php

namespace UI\Datagrid\Column;

use Exception;
use UI\Datagrid\Datagrid;
use UI_Form_Element_Pattern_Date;

/**
 * Une classe représentant une colonne contenant des dates.
 *
 * @author valentin.claras
 */
class DateColumn extends GenericColumn
{
    /**
     * Définition du mot clef du filtre pour l'infériorité.
     *
     * @var string
     */
    public $keywordFilterLower;

    /**
     * Définition du mot clef du filtre pour la supériorité.
     *
     * @var string
     */
    public $keywordFilterHigher;

    /**
     * Définition de la constante utilisé pour le filtre inférieur sur la colonne.
     *
     * @var string
     */
    public $criteriaFilterOperatorLower;

    /**
     * Définition de la constante utilisé pour le filtre supérieur sur la colonne.
     *
     * @var string
     */
    public $criteriaFilterOperatorHigher;


    public function __construct($id = null, $label = null)
    {
        parent::__construct($id, $label);
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_CENTER;
        $this->keywordFilterLower = __('UI', 'datagridFilter', 'ColDateLower');
        $this->keywordFilterHigher = __('UI', 'datagridFilter', 'ColDateHigher');
        $this->criteriaFilterOperator = 'eq';
        $this->criteriaFilterOperatorLower = 'lte';
        $this->criteriaFilterOperatorHigher = 'gte';
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
    protected function getEditableOption(Datagrid $datagrid)
    {
        $editOption = '';

        $editOption .= ', editor:new YAHOO.widget.DateCellEditor({';
        $editOption .= 'calendarOptions: { navigator: true },';
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
        // @ todo Date -> Col_Date::getEditorValue -> utiliser le format de l'utilisateur.
        return <<<JS
if (typeof(sData) != "string") {
    var date = sData.value;
} else {
    var date = sData;
}
var dateComponents = date.split(\'/\');
var day = dateComponents[0];
var month = dateComponents[1];
var year = dateComponents[2];
record.setData(column.key, new Date(month + '/' + day + '/' + year));
this.onEventShowCellEditor(oArgs);
$('#' + column.editor.calendar.id).parent().parent().addClass('yui-skin-sam');
record.setData(column.key, sData);
JS;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterFormElement(Datagrid $datagrid, $defaultValue = null)
    {
        throw new Exception('Col Date needs to be refactored before being able to filter element.');
        // Champs pour le fitre >=.
        $filterFormElementSuperior = new UI_Form_Element_Pattern_Date($this->getFilterFormId($datagrid).'_higher');
        $filterFormElementSuperior->setLabel($this->getFilterFormLabel());
        $filterFormElementSuperior->getElement()->addPrefix($this->keywordFilterHigher);
        if (isset($defaultValue[$this->criteriaFilterOperatorHigher])) {
            $filterFormElementSuperior->setValue($defaultValue[$this->criteriaFilterOperatorHigher]);
        }
        $resetFieldSuperior = '<i ';
        $resetFieldSuperior .= 'class="fa fa-'.$datagrid->filterIconResetFieldSuffix.' reset" ';
        $resetFieldSuperior .= 'onclick="$(\'#'.$this->getFilterFormId($datagrid).'_higher\').val(\'\');"';
        $resetFieldSuperior .= '>';
        $resetFieldSuperior .= '</i>';
        $filterFormElementSuperior->getElement()->addSuffix($resetFieldSuperior);

        // Champs pour le fitre <=.
        $filterFormElementInferior = new UI_Form_Element_Pattern_Date($this->getFilterFormId($datagrid).'_lower');
        $filterFormElementInferior->getElement()->addPrefix($this->keywordFilterLower);
        if (isset($defaultValue[$this->criteriaFilterOperatorLower])) {
            $filterFormElementInferior->setValue($defaultValue[$this->criteriaFilterOperatorLower]);
        }
        $resetFieldInferior = '<i ';
        $resetFieldInferior .= 'class="fa fa-'.$datagrid->filterIconResetFieldSuffix.' reset" ';
        $resetFieldInferior .= 'onclick="$(\'#'.$this->getFilterFormId($datagrid).'_lower\').val(\'\');"';
        $resetFieldInferior .= '>';
        $resetFieldInferior .= '</i>';
        $filterFormElementInferior->getElement()->addSuffix($resetFieldInferior);

        $filterFormElementSuperior->getElement()->addElement($filterFormElementInferior);

        return $filterFormElementSuperior;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterValue(Datagrid $datagrid)
    {
        throw new Exception('Col Date needs to be refactored before being able to filter element.');
        $filterValue = '';

        // Condition de saisie du filtre.
        $filterValue .= 'var valueSup = $(\'#'.$this->getFilterFormId($datagrid).'_higher\').val();';
        $filterValue .= 'var valueInf = $(\'#'.$this->getFilterFormId($datagrid).'_lower\').val();';
        $filterValue .= 'if ((valueSup != \'\') || (valueInf != \'\')) {';

        // Ajout au filtre.
        $filterValue .= 'filter += "{\"'.$this->getFullFilterName($datagrid).'\": {";';
        $filterValue .= 'if (valueSup != \'\') {';
        $filterValue .= 'filter += "\"'.$this->criteriaFilterOperatorHigher.'\":\"" + valueSup + "\"";';
        $filterValue .= '}';
        $filterValue .= 'if ((valueSup != \'\') && (valueInf != \'\')) {';
        $filterValue .= 'filter += ",";';
        $filterValue .= '}';
        $filterValue .= 'if (valueInf != \'\') {';
        $filterValue .= 'filter += "\"'.$this->criteriaFilterOperatorLower.'\":\"" + valueInf + "\"";';
        $filterValue .= '}';
        $filterValue .= 'filter += "}},";';

        $filterValue .= '}';

        return $filterValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getResettingFilter(Datagrid $datagrid)
    {
        throw new Exception('Col Date needs to be refactored before being able to filter element.');
        $resetFields = '';

        $resetFields .= '$(\'#'.$this->getFilterFormId($datagrid).'_higher\').val(\'\');';
        $resetFields .= '$(\'#'.$this->getFilterFormId($datagrid).'_lower\').val(\'\');';

        return $resetFields;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddFormElement(Datagrid $datagrid)
    {
        throw new Exception('Col Date needs to be refactored before being able to add element.');
        $addFormElement = new UI_Form_Element_Pattern_Date($this->getAddFormElementId($datagrid));
        $addFormElement->setLabel($this->getAddFormElementLabel());
        $addFormElement->setValue($this->defaultAddValue);

        return $addFormElement;
    }
}
