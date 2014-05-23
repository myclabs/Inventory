<?php

/**
 * Helper de vue pour générer un datagrid de traduction.
 *
 * @author valentin.claras
 */
class UI_View_Helper_TranslateDatagrid extends Zend_View_Helper_Abstract
{
    /**
     * @var UI_Datagrid
     */
    protected $datagrid;

    /**
     * @var string[]
     */
    private $translationLanguages;

    public function __construct()
    {
        $this->translationLanguages = \Core\ContainerSingleton::getContainer()->get('translation.languages');
    }

    /**
     * Retourne le render de l'actuel autocomplete de l'aide de vue.
     *
     * @return string
     */
    public function __toString()
    {
        UI_Datagrid::addHeader($this->datagrid);
        return $this->datagrid->getHTML();
    }

    /**
     * Génere une datagrid de traduction
     *
     * @param string $className
     * @param string $attribute
     * @param string $controller
     * @param string $module
     *
     * @return UI_View_Helper_TranslateDatagrid
     */
    public function translateDatagrid($className, $attribute, $controller, $module = null)
    {
        $id = 'datagridTranslate_' . str_replace('\\', '_', $className) . '_' . $attribute;
        $this->datagrid = new UI_Datagrid($id, $controller, $module);
        $this->datagrid->automaticFiltering = false;

        return $this;
    }

    /**
     * Ajoute un identifier et les colonnes de langues en simple texte.
     *
     * @param bool $editable
     *
     * @return UI_View_Helper_TranslateDatagrid
     */
    public function simple($editable = true)
    {
        return $this->addIdentifierCol()->addLanguagesTextCols($editable);
    }

    /**
     * Ajoute la colonne identifier.
     *
     * @return UI_View_Helper_TranslateDatagrid
     */
    public function addIdentifierCol()
    {
        $identifierColumn = new UI_Datagrid_Col_Text('identifier', __('UI', 'name', 'identifier'));
        $identifierColumn->editable = false;
        $this->datagrid->addCol($identifierColumn);

        return $this;
    }

    /**
     * Ajoute une colonne de texte pour chaque langue.
     *
     * @param bool $editable
     *
     * @return UI_View_Helper_TranslateDatagrid
     */
    public function addLanguagesTextCols($editable = true)
    {
        foreach ($this->translationLanguages as $language) {
            $languageColumn = new UI_Datagrid_Col_Text($language, __('UI', 'translate', 'language' . $language));
            $languageColumn->editable = $editable;
            $this->datagrid->addCol($languageColumn);
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
    public function addLanguagesLongTextCols($editable = true)
    {
        foreach ($this->translationLanguages as $language) {
            $languageColumn = new UI_Datagrid_Col_LongText($language, __('UI', 'translate', 'language' . $language));
            $languageColumn->editable = $editable;
            $this->datagrid->addCol($languageColumn);
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
        $this->datagrid->addCol($column);

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
        $this->datagrid->addParam($parameterName, $parameterValue);

        return $this;
    }

    public function disablePagination()
    {
        $this->datagrid->pagination = false;

        return $this;
    }

    /**
     * @return UI_Datagrid
     */
    public function getDatagrid()
    {
        return $this->datagrid;
    }
}
