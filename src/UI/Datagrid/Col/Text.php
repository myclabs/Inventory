<?php
use MyCLabs\MUIH\GenericTag;
use MyCLabs\MUIH\GenericVoidTag;

/**
 * Fichier de la classe Colonne Texte.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage Datagrid
 */

/**
 * Description of colonne texte.
 *
 * Une classe permettant de générer une colonne contenant des textes.
 *
 * @deprecated
 *
 * @package    UI
 * @subpackage Datagrid
 */
class UI_Datagrid_Col_Text extends UI_Datagrid_Col_Generic
{
    /**
     * Définition du mot clef du filtre pour l'égalité.
     *
     * @var   string
     */
    public $keywordFilterEqual = null;


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
        $this->_type = self::TYPE_COL_TEXT;
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_LEFT;
        $this->keywordFilterEqual = __('UI', 'datagridFilter', 'ColTextEqual');
        $this->filterOperator = Core_Model_Filter::OPERATOR_CONTAINS;
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
        $editorValue .= 'var content = sData.value.toString();';
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
     * @return GenericTag
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

        $textWrapper = new GenericTag('div');
        $textWrapper->addClass('col-sm-10');

        $textInput = new GenericVoidTag('input');
        $textInput->setAttribute('type', 'text');
        $textInput->setAttribute('name', $this->getFilterFormId($datagrid));
        $textInput->setAttribute('id', $this->getFilterFormId($datagrid));
        // Récupération des valeurs par défaut.
        if (isset($defaultValue[$this->filterOperator])) {
            $textInput->setAttribute('value', $defaultValue[$this->filterOperator]);
        }
        $textInput->addClass('form-control');

        $inputGroupWrapper = new GenericTag('div', $textInput);
        $inputGroupWrapper->addClass('input-group');

        if (!empty($this->keywordFilterEqual)) {
            $keywordFilterPrefix = new GenericTag('span', $this->keywordFilterEqual);
            $keywordFilterPrefix->addClass('input-group-addon');
            $inputGroupWrapper->prependContent($keywordFilterPrefix);
        }

        $inputGroupWrapper->appendContent($this->getResetFieldFilterFormSuffix($datagrid));

        $textWrapper->appendContent($inputGroupWrapper);

        $colWrapper->appendContent($textWrapper);

        return $colWrapper;
    }

    /**
     * Méthode renvoyant le champs du formulaire d'ajout de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return GenericTag
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

        $textWrapper = new GenericTag('div');
        $textWrapper->addClass('col-sm-10');

        $textInput = new GenericVoidTag('input');
        $textInput->setAttribute('type', 'text');
        $textInput->setAttribute('name', $this->getAddFormElementId($datagrid));
        $textInput->setAttribute('id', $this->getAddFormElementId($datagrid));
        $textInput->setAttribute('value', $this->defaultAddValue);
        $textInput->addClass('form-control');
        $textWrapper->appendContent($textInput);

        $colWrapper->appendContent($textWrapper);

        return $colWrapper;
    }

}