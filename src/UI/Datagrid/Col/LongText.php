<?php
/**
 * Fichier de la classe Colonne de Texte Long.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage Datagrid
 */

/**
 * Description of colonne texte long.
 *
 * Une classe permettant de générer une colonne contenant des textes longs.
 *
 * @package    UI
 * @subpackage Datagrid
 */
class UI_Datagrid_Col_LongText extends UI_Datagrid_Col_Popup
{
    /**
     * Définition du message affiché lors du chargement du texte brute.
     *
     * @var string
     */
    public $loadingText = null;

    /**
     * Définition du message affiché lorsqu'une erreur se produit au chargement de texte brut.
     *
     * @var string
     */
    public $errorText = null;

    /**
     * Permet de savoir si le texte est textile et sera édité avec MarkItUp.
     *
     * Par défaut oui.
     *
     * @var   bool
     */
    public $textileEditor = true;


     /**
      * Constructeur de la classe ColonneTexte.
      *
      * @param string $id    Identifiant unique de la colonne.
      * @param string $label Texte afiché en titre de la colone.
      */
    public function __construct($id=null, $label=null)
    {
        parent::__construct($id, $label);
        // Définition du type de la classe.
        $this->_type = self::TYPE_COL_LONGTEXT;
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_LEFT;
        $this->defaultValue = '<i class="icon-zoom-in"></i> '.__('UI', 'name', 'details');
        $this->loadingText = __('UI', 'loading', 'loading');
        $this->errorText = str_replace('\'', '\\\'', __('UI', 'loading', 'error'));
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
        $format .= 'var href = sData;';
        $format .= 'content = \''.addslashes($this->defaultValue).'\';';
        $format .= '} else {';
        $format .= 'var href = sData.value.desc;';
        $format .= 'if (sData.content != null) {';
        $format .= 'content = sData.content;';
        $format .= '} else {';
        $format .= 'content = \''.addslashes($this->defaultValue).'\';';
        $format .= '}';
        $format .= 'content = \'<a href="\' + href + \'"';
        $format .= ' data-target="#'.$datagrid->id.'_'.$this->id.'_popup" data-toggle="modal" data-remote="false">\'';
        $format .= ' + content + \'</a>\';';
        $format .= '}';

        return $format;
    }

    /**
     * Ajoute l'icone d'édition à la cellule.
     *
     * @return string
     */
    protected function addEditableFormatter()
    {
        return UI_Datagrid_Col_Generic::addEditableFormatter();
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

        $editOption .= ', editor:new YAHOO.widget.TextareaCellEditor({';
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
        $editorValue .= 'column.editor.textarea.value = \''.$this->loadingText.'\';';
        $editorValue .= 'if ((typeof(sData) == "undefined") || (sData == null)) {';
        $editorValue .= 'column.editor.textarea.value = \'\';';
        $editorValue .= '} else {';
        $editorValue .= 'if (typeof(sData) != "object") {';
        $editorValue .= 'var url = sData.toString();';
        $editorValue .= '} else if (sData.value.brut != \'\') {';
        $editorValue .= 'var url = sData.value.brut.toString();';
        $editorValue .= '} else {';
        $editorValue .= 'var url = sData.value.desc.toString();';
        $editorValue .= '}';
        $editorValue .= $datagrid->id.'.StartLoading();';
        $editorValue .= '$.get(url, function(data) {';
        $editorValue .= 'var brutText = data;';
        $editorValue .= 'brutText = brutText.replace("\n", \'\');';
        $editorValue .= 'brutText = brutText.replace(/<br ?\/?>/gi, "\n");';
        $editorValue .= 'brutText = brutText.replace(/<.*?>/g, \'\');';
        $editorValue .= 'column.editor.textarea.value = brutText;';
        $editorValue .= $datagrid->id.'.EndLoading();';
        $editorValue .= '}).error(function(data) {';
        $editorValue .= 'column.editor.textarea.value = \''.$this->errorText.'\';';
        $editorValue .= 'errorHandler(data);';
        $editorValue .= $datagrid->id.'.EndLoading();';
        $editorValue .= '});';
        $editorValue .= '}';
        if ($this->textileEditor === true) {
            $editorValue .= 'if (column.editor.textarea.className != \'markItUpEditor\') {';
            $editorValue .= '$(column.editor.textarea).markItUp(mySettings);';
            $editorValue .= '}';
        }

        return $editorValue;
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
        $addFormElement = new UI_Form_Element_Textarea($this->getAddFormElementId($datagrid));
        $addFormElement->setLabel($this->getAddFormElementLabel());
        $addFormElement->setValue($this->defaultAddValue);

        $addFormElement->setWithMarkItUp($this->textileEditor);

        return $addFormElement;
    }

}