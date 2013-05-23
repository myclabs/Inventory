<?php
/**
 * @author     valentin.claras
 * @package    UI
 * @subpackage View
 */

/**
 * Helper de vue pour générer un datagrid de traduction.
 * @package    Core
 * @subpackage View
 */
class UI_View_Helper_TranslateDatagrid extends Zend_View_Helper_Abstract
{
    /**
     * @var UI_Datagrid
     */
    protected $_datagrid = null;


    /**
     * Retourne le render de l'actuel autocomplete de l'aide de vue.
     *
     * @return string
     */
    public function __toString()
    {
        UI_Datagrid::addHeader($this->_datagrid);
        return $this->_datagrid->getHTML();
    }

    /**
     * Génere une datagrid de traduction
     *
     * @param string $className
     * @param string $attribute
     * @param string $controller
     * @param string $module
     * @param bool   $editable
     *
     * @return UI_View_Helper_TranslateDatagrid
     */
    public function translateDatagrid($className, $attribute, $controller, $module = null)
    {
        $id = 'datagridTranslate_' . $className . '_' . $attribute;
        $this->_datagrid = new UI_Datagrid($id, $controller, $module);
        $this->_datagrid->automaticFiltering = false;

        return $this;
    }

    /**
     * Ajoute un identifier et les colonnes de langues en simple texte.
     *
     * @param bool $editbale
     *
     * @return UI_View_Helper_TranslateDatagrid
     */
    public function simple($editbale=true)
    {
        return $this->addIdentifierCol()->addLanguagesTextCols($editbale);
    }

    /**
     * Ajoute la colonne identifier.
     *
     * @return UI_View_Helper_TranslateDatagrid
     */
    public function addIdentifierCol()
    {
        $identifierColumn = new UI_Datagrid_Col_Text('identifier', __('UI', 'translate', 'identifier'));
        $identifierColumn->editable = false;
        $this->_datagrid->addCol($identifierColumn);

        return $this;
    }

    /**
     * Ajoute une colonne de texte pour chaque langue.
     *
     * @param bool $editable
     *
     * @return UI_View_Helper_TranslateDatagrid
     */
    public function addLanguagesTextCols($editable=true)
    {
        foreach (Zend_Registry::get('languages') as $language) {
            $languageColumn = new UI_Datagrid_Col_Text($language, __('UI', 'translate', 'language' . $language));
            $languageColumn->editable = $editable;
            $this->_datagrid->addCol($languageColumn);
        }

        return $this;
    }

    /**
     * Ajoute une colonne de texte long pour chaque langue.
     *
     * @param bool $editable
     *
     * @return UI_View_Helper_TranslateDatagrid
     */
    public function addLanguagesLongTextCols($editable=true)
    {
        foreach (Zend_Registry::get('languages') as $language) {
            $languageColumn = new UI_Datagrid_Col_LongText($language, __('UI', 'translate', 'language' . $language));
            $languageColumn->editable = $editable;
            $this->_datagrid->addCol($languageColumn);
        }

        return $this;
    }

    /**
     * Aloute une colonne personnalisée.
     *
     * @param UI_Datagrid_Col_Generic $column
     *
     * @return UI_View_Helper_TranslateDatagrid
     */
    public function addCol(UI_Datagrid_Col_Generic $column)
    {
        $this->_datagrid->addCol($column);

        return $this;
    }

    /**
     * Ajoute un paramètre à la datagrid.
     *
     * @param string $parameterName
     * @param mixed $parameterValue
     *
     * @return UI_View_Helper_TranslateDatagrid
     */
    public function addParam($parameterName, $parameterValue)
    {
        $this->_datagrid->addParam($parameterName, $parameterValue);

        return $this;
    }

}
