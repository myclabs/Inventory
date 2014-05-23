<?php
/**
 * Fichier de la classe Colonne Date.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage Datagrid
 */

/**
 * Description of colonne date.
 *
 * Une classe permettant de générer une colonne contenant des dates.
 *
 * @deprecated
 *
 * @package UI
 * @subpackage Datagrid
 */
class UI_Datagrid_Col_Date extends UI_Datagrid_Col_Generic
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
    public $filterOperatorLower = null;

    /**
     * Définition de la constante utilisé pour le filtre supérieur sur la colonne.
     *
     * @var   string
     */
    public $filterOperatorHigher = null;


     /**
      * Constructeur de la classe ColonneDate.
      *
      * @param string $id    Identifiant unique de la colonne.
      * @param string $label Texte afiché en titre de la colone.
      */
    public function __construct($id=null, $label=null)
    {
        parent::__construct($id, $label);
        // Définition du type de la classe.
        $this->_type = self::TYPE_COL_DATE;
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_CENTER;
        $this->keywordFilterLower = __('UI', 'datagridFilter', 'ColDateLower');
        $this->keywordFilterHigher = __('UI', 'datagridFilter', 'ColDateHigher');
        $this->filterOperator = Core_Model_Filter::OPERATOR_EQUAL;
        $this->filterOperatorLower = Core_Model_Filter::OPERATOR_LOWER_EQUAL;
        $this->filterOperatorHigher = Core_Model_Filter::OPERATOR_HIGHER_EQUAL;
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
     * Méthode renvoyant les options d'édition de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    protected function getEditableOption($datagrid)
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
     * Méthode renvoyant l'appel à l'édition de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getEditorValue($datagrid)
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
     * Méthode renvoyant le champs du filtre de la colonne.
     *
     * @param UI_Datagrid $datagrid
     * @param array $defaultValue Valeur par défaut du filtre (=null).
     *
     * @return Zend_Form_Element
     */
    public function getFilterFormElement($datagrid, $defaultValue=null)
    {
        throw new Exception('Col Date needs to be refactored before being able to filter element.');
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
        throw new Exception('Col Date needs to be refactored before being able to filter element.');
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
        throw new Exception('Col Date needs to be refactored before being able to filter element.');
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
        throw new Exception('Col Date needs to be refactored before being able to add element.');
    }

}
