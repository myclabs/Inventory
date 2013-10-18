<?php
/**
 * Fichier de la classe TextColumn.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage Datagrid
 */

namespace UI\Datagrid\Column;

use UI\Datagrid\Datagrid;
use UI_Form_Element_Text;

/**
 * Description of TextColumn.
 *
 * Une classe permettant de générer une colonne contenant des textes.
 *
 * @package    UI
 * @subpackage Datagrid
 */
class TextColumn extends GenericColumn
{
    /**
     * Définition du mot clef du filtre pour l'égalité.
     *
     * @var   string
     */
    public $keywordFilterEqual = null;


     /**
      * {@inheritdoc}
      */
    public function __construct($id=null, $label=null)
    {
        parent::__construct($id, $label);
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_LEFT;
        $this->keywordFilterEqual = __('UI', 'datagridFilter', 'ColTextEqual');
        $this->criteriaFilterOperator = 'contains';
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
    public function getEditorValue(Datagrid $datagrid)
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
     * {@inheritdoc}
     */
    public function getFilterFormElement(Datagrid $datagrid, $defaultValue=null)
    {
        $filterFormElement = new UI_Form_Element_Text($this->getFilterFormId($datagrid));
        $filterFormElement->setLabel($this->getFilterFormLabel());
        $filterFormElement->getElement()->addPrefix($this->keywordFilterEqual);

        // Récupération des valeurs par défaut.
        if (isset($defaultValue[$this->criteriaFilterOperator])) {
            $filterFormElement->setValue($defaultValue[$this->criteriaFilterOperator]);
        }

        $filterFormElement->getElement()->addSuffix($this->getResetFieldFilterFormSuffix($datagrid));

        return $filterFormElement;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddFormElement(Datagrid $datagrid)
    {
        $addFormElement = new UI_Form_Element_Text($this->getAddFormElementId($datagrid));
        $addFormElement->setLabel($this->getAddFormElementLabel());
        $addFormElement->setValue($this->defaultAddValue);

        return $addFormElement;
    }

}