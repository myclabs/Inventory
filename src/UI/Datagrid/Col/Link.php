<?php
use MyCLabs\MUIH\GenericTag;
use MyCLabs\MUIH\GenericVoidTag;

/**
 * Fichier de la classe Colonne Lien.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage Datagrid
 */

/**
 * Description of colonne lien.
 *
 * Une classe permettant de générer une colonne contenant des liens.
 *
 * @deprecated
 *
 * @package    UI
 * @subpackage Datagrid
 */
class UI_Datagrid_Col_Link extends UI_Datagrid_Col_Generic
{
    /**
     * Définition du mot clef du filtre pour l'égalité.
     *
     * @var   string
     */
    public $keywordFilterEqual = null;

    /**
     * Définition de la valeur par defaut qui sera affiché dans la cellule.
     *
     * @var   string
     */
    public $defaultValue = null;

    /**
     * Définit si la valeur de la cellule est l'url du lien (true) ou le texte affiché (false).
     *
     * Par défaut il s'agit de l'url du lien (true).
     *
     * @var   bool
     */
    public $linkValue = true;


    /**
     * Constructeur de la classe ColonneBooleen.
     *
     * @param string $id    Identifiant unique de la colonne.
     * @param string $label Texte afiché en titre de la colone.
     */
    public function __construct($id=null, $label=null)
    {
        parent::__construct($id, $label);
        // Définition du type de la classe.
        $this->_type = self::TYPE_COL_LINK;
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_CENTER;
        $this->keywordFilterEqual = __('UI', 'datagridFilter', 'ColLinkEqual');
        $this->filterOperator = Core_Model_Filter::OPERATOR_CONTAINS;
        $this->defaultValue = '<i class="fa fa-external-link"></i> '.__('UI', 'datagridContent', 'linkLabel');
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

        $format .= 'var url = \'\';';
        $format .= 'var text = \'\';';
        $format .= 'if (typeof(sData) != "object") {';
        if ($this->linkValue === true) {
            $format .= 'url = sData;';
            $format .= 'text = \''.addslashes($this->defaultValue).'\';';
        } else {
            $format .= 'url = \''.addslashes($this->linkValue).'\';';
            $format .= 'text = sData;';
        }
        $format .= '} else {';
        $format .= 'url = sData.value;';
        $format .= 'if (sData.content != null) {';
        $format .= 'text = sData.content;';
        $format .= '} else {';
        $format .= 'text = \''.addslashes($this->defaultValue).'\';';
        $format .= '}';
        $format .= '}';
        $format .= 'content = \'<a href="\' + url + \'">\' + text + \'</a>\';';

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
        if ($this->linkValue !== true) {
            $editorValue .= 'var content = sData.content.toString();';
        } else {
            $editorValue .= 'var content = sData.value.toString();';
        }
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
        if ($this->linkValue !== true) {
            $filterFormElement = new UI_Form_Element_Text($this->getFilterFormId($datagrid));
            $filterFormElement->setLabel($this->getFilterFormLabel());
            $filterFormElement->getElement()->addPrefix($this->keywordFilterEqual);

            // Récupération des valeurs par défaut.
            if (isset($defaultValue[$this->filterOperator])) {
                $filterFormElement->setValue($defaultValue[$this->filterOperator]);
            }

            $filterFormElement->getElement()->addSuffix($this->getResetFieldFilterFormSuffix($datagrid));
        } else {
            $filterFormElement = null;
        }

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
        // Le filtre est possible uniquement sur les colonnes lien (lorsque la valeur de la cellule est le texte).
        if ($this->linkValue !== true) {
            return parent::getFilterValue($datagrid);
        } else {
            return null;
        }
    }

    /**
     * Méthode renvoyant la réinitialisation des champs du filtre de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getResettingFilter($datagrid)
    {
        // Le filtre est possible uniquement sur les colonnes lien (lorsque la valeur de la cellule est le texte).
        if ($this->linkValue !== true) {
            return parent::getResettingFilter($datagrid);
        } else {
            return null;
        }
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
        // Le'ajout est possible uniquement sur les Colonne Lien (lorsque la valeur de la cellule est le texte).
        if ($this->linkValue !== true) {
            $colWrapper = new GenericTag('div');
            $colWrapper->addClass('form-group');

            $colLabel = new GenericTag('label', $this->getAddFormElementLabel());
            $colLabel->setAttribute('for', $this->getAddFormElementId($datagrid));
            $colLabel->addClass('col-sm-2');
            $colLabel->addClass('control-label');
            $colLabel->addClass('field-label');
            $colWrapper->appendContent($colLabel);

            $linkWrapper = new GenericTag('div');
            $linkWrapper->addClass('col-sm-10');

            $linkInput = new GenericVoidTag('input');
            $linkInput->setAttribute('type', 'text');
            $linkInput->setAttribute('name', $this->getAddFormElementId($datagrid));
            $linkInput->setAttribute('id', $this->getAddFormElementId($datagrid));
            $linkInput->setAttribute('value', $this->defaultAddValue);
            $linkInput->addClass('form-control');
            $linkWrapper->appendContent($linkInput);

            $colWrapper->appendContent($linkWrapper);

            return $colWrapper;
        } else {
            return null;
        }
    }

}