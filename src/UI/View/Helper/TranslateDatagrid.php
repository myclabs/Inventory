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
     * @param string $class
     * @param string $attribute
     * @param string $controller
     * @param string $module
     *
     * @return UI_View_Helper_TranslateDatagrid
     */
    public function translateDatagrid($className, $attribute, $controller, $module=null)
    {
        $id = 'datagrideTranslate_'.$className::getAlias().'_'.$attribute;
        $this->_datagrid = new UI_Datagrid($id, $controller, $module);
        $this->_datagrid->automaticFiltering = false;

        $identifierColumn = new UI_Datagrid_Col_Text('identifier', __('UI', 'translate', 'identifier'));
        $identifierColumn->editable = false;
        $this->_datagrid->addCol($identifierColumn);

        foreach (Zend_Registry::get('languages') as $language) {
            $languageColumn = new UI_Datagrid_Col_Text($language, __('UI', 'translate', 'language'.$language));
            $languageColumn->editable = true;
            $this->_datagrid->addCol($languageColumn);
        }

        return $this;
    }

    public function readableOnly()
    {
        foreach ($this->_datagrid->getCols() as $column) {
            $column->editable = false;
        }
    }

}
