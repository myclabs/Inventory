<?php

namespace UI\Datagrid\Column;

use UI\Datagrid\Datagrid;
use UI_Form_Element_Textarea;

/**
 * Classe représentant une colonne contenant des textes longs.
 *
 * @author valentin.claras
 */
class LongTextColumn extends PopupColumn
{
    /**
     * Définition du message affiché lors du chargement du texte brute.
     *
     * @var string
     */
    public $loadingText;

    /**
     * Définition du message affiché lorsqu'une erreur se produit au chargement de texte brut.
     *
     * @var string
     */
    public $errorText;

    /**
     * Permet de savoir si le texte est textile et sera édité avec MarkItUp.
     *
     * Par défaut oui.
     *
     * @var bool
     */
    public $textileEditor = true;


    public function __construct($id = null, $label = null)
    {
        parent::__construct($id, $label);
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_LEFT;
        $this->defaultValue = '<i class="icon-zoom-in"></i> '.__('UI', 'name', 'details');
        $this->loadingText = __('UI', 'loading', 'loading');
        $this->errorText = str_replace('\'', '\\\'', __('UI', 'loading', 'error'));
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter(Datagrid $datagrid)
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
     * {@inheritdoc}
     */
    protected function addEditableFormatter()
    {
        return GenericColumn::addEditableFormatter();
    }

    /**
     * {@inheritdoc}
     */
    public function getEditableOption(Datagrid $datagrid)
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
     * {@inheritdoc}
     */
    public function getEditorValue(Datagrid $datagrid)
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
     * {@inheritdoc}
     */
    public function getAddFormElement(Datagrid $datagrid)
    {
        $addFormElement = new UI_Form_Element_Textarea($this->getAddFormElementId($datagrid));
        $addFormElement->setLabel($this->getAddFormElementLabel());
        $addFormElement->setValue($this->defaultAddValue);

        $addFormElement->setWithMarkItUp($this->textileEditor);

        return $addFormElement;
    }
}
