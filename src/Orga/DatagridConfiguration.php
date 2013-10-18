<?php
/**
 * @author valentin.claras
 * @author diana.dragusin
 * @package    Orga
 * @subpackage DatagridConfiguration
 */

/**
 * Classe de cnfiguration pour le datagrid d'une cellule.
 *
 * @package Orga
 */
class Orga_DatagridConfiguration
{
    /**
     * Le datagrid qui sera affiché.
     *
     * @var UI_Datagrid
     */
    public $datagrid;

    /**
     * Constructeur.
     *
     * @param string $id
     * @param string $controller
     * @param string $module
     * @param Orga_Model_Cell $cell
     * @param Orga_Model_Granularity $granularity
     */
    public function __construct($id, $controller, $module, $cell, $granularity)
    {
        $this->datagrid= new UI_Datagrid($id, $controller, $module);
        $this->datagrid->addParam('idCell', $cell->getId());
        $this->datagrid->addParam('idGranularity', $granularity->getId());
        $this->addAxes($cell, $granularity);
    }

    /**
     * Ajoute les colonnes des axes au datagrid.
     *
     * @param Orga_Model_Cell $cell
     * @param Orga_Model_Granularity $granularity
     */
    protected function addAxes($cell, $granularity)
    {
        foreach ($granularity->getOrganization()->getFirstOrderedAxes() as $axis) {
            if ($granularity->hasAxis($axis) && !$cell->getGranularity()->hasAxis($axis)) {
                $this->addAxis($axis, $cell);
            }
        }
    }

    /**
     * Ajoute l'axe données aux colonnes du datagrid.
     *
     * @param Orga_Model_Axis $axis
     * @param Orga_Model_Cell $cell
     */
    protected function addAxis($axis, $cell)
    {
        $columnAxis = new UI_Datagrid_Col_List($axis->getRef(), $axis->getLabel());
        $columnAxis->list = array();

        if ($axis->hasMembers()) {
            $childMembers = $axis->getMembers()->toArray();
            foreach ($cell->getMembers() as $cellMember) {
                if ($cellMember->getAxis()->isBroaderThan($axis)) {
                    $childMembers = array_intersect($childMembers, $cellMember->getChildrenForAxis($axis));
                }
            }
            $columnAxis->list = array();
            foreach ($childMembers as $childMember) {
                $columnAxis->list[$childMember->getRef()] = $childMember->getLabel();
            }
            if (count($columnAxis->list) > 5) {
                $sizeFilter = 5;
            } else if (count($columnAxis->list) > 1) {
                $sizeFilter = 3;
            } else {
                $sizeFilter = 2;
            }
            $columnAxis->filterName = $axis->getRef();
            $columnAxis->entityAlias = Orga_Model_Member::getAlias();
            $columnAxis->withEmptyElement = false;
            $columnAxis->multipleFilters = true;
            $columnAxis->multipleListSize = $sizeFilter;
            $columnAxis->fieldType = UI_Datagrid_Col_List::FIELD_AUTOCOMPLETE;
        }
        $this->datagrid->addCol($columnAxis);
    }

}