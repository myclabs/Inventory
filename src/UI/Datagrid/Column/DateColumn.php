<?php
/**
 * Fichier de la classe DateColumn.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage Datagrid
 */

namespace UI\Datagrid\Column;

use UI\Datagrid\Datagrid;
use UI_Form_Element_Pattern_Date;

/**
 * Description of DateColumn.
 *
 * Une classe permettant de générer une colonne contenant des dates.
 *
 * @package UI
 * @subpackage Datagrid
 */
class DateColumn extends GenericColumn
{
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
    public $criteriaFilterOperatorLower = null;

    /**
     * Définition de la constante utilisé pour le filtre supérieur sur la colonne.
     *
     * @var   string
     */
    public $criteriaFilterOperatorHigher = null;


     /**
      * {@inheritdoc}
      */
    public function __construct($id=null, $label=null)
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
        $editorValue = '';

        $editorValue .= 'if (typeof(sData) != "string") {';
        $editorValue .= 'var date = sData.value;';
        $editorValue .= '} else {';
        $editorValue .= 'var date = sData';
        $editorValue .= '}';
        // @ todo Date -> Col_Date::getEditorValue -> utiliser le format de l'utilisateur.
        $editorValue .= 'var dateComponents = date.split(\'/\');';
        $editorValue .= 'var day = dateComponents[0];';
        $editorValue .= 'var month = dateComponents[1];';
        $editorValue .= 'var year = dateComponents[2];';
        $editorValue .= 'record.setData(column.key, new Date(month + \'/\' + day + \'/\' + year));';
        $editorValue .= 'this.onEventShowCellEditor(oArgs);';
        $editorValue .= '$(\'#\' + column.editor.calendar.id).parent().parent().addClass(\'yui-skin-sam\');';
        $editorValue .= 'record.setData(column.key, sData);';

        return $editorValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterFormElement(Datagrid $datagrid, $defaultValue=null)
    {
        // Champs pour le fitre >=.
        $filterFormElementSuperior = new UI_Form_Element_Pattern_Date($this->getFilterFormId($datagrid).'_higher');
        $filterFormElementSuperior->setLabel($this->getFilterFormLabel());
        $filterFormElementSuperior->getElement()->addPrefix($this->keywordFilterHigher);
        if (isset($defaultValue[$this->criteriaFilterOperatorHigher])) {
            $filterFormElementSuperior->setValue($defaultValue[$this->criteriaFilterOperatorHigher]);
        }
        $resetFieldSuperior = '<i ';
        $resetFieldSuperior .= 'class="icon-'.$datagrid->filterIconResetFieldSuffix.' reset" ';
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
        $resetFieldInferior .= 'class="icon-'.$datagrid->filterIconResetFieldSuffix.' reset" ';
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
        $filterValue = '';

        // Condition de saisie du filtre.
        $filterValue .= 'var valueSup = $(\'#'.$this->getFilterFormId($datagrid).'_higher\').val();';
        $filterValue .= 'var valueInf = $(\'#'.$this->getFilterFormId($datagrid).'_lower\').val();';
        $filterValue .= 'if ((valueSup != \'\') || (valueInf != \'\')) {';

        // Ajout au filtre.
        $filterValue .= 'filter += "\"'.$this->getFullFilterName($datagrid).'\": {";';
        $filterValue .= 'if (valueSup != \'\') {';
        $filterValue .= 'filter += "\"'.$this->criteriaFilterOperatorHigher.'\":\"" + valueSup + "\"";';
        $filterValue .= '}';
        $filterValue .= 'if ((valueSup != \'\') && (valueInf != \'\')) {';
        $filterValue .= 'filter += ",";';
        $filterValue .= '}';
        $filterValue .= 'if (valueInf != \'\') {';
        $filterValue .= 'filter += "\"'.$this->criteriaFilterOperatorLower.'\":\"" + valueInf + "\"";';
        $filterValue .= '}';
        $filterValue .= 'filter += "},";';

        $filterValue .= '}';

        return $filterValue;
    }

    /**
     * {@inheritdoc}
     */
    function getResettingFilter(Datagrid $datagrid)
    {
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
        $addFormElement = new UI_Form_Element_Pattern_Date($this->getAddFormElementId($datagrid));
        $addFormElement->setLabel($this->getAddFormElementLabel());
        $addFormElement->setValue($this->defaultAddValue);

        return $addFormElement;
    }

}