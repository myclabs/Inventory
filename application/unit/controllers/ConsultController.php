<?php
/**
 * @author valentin.claras
 * @package Unit
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Unit_ConsultController
 * @package Unit
 * @subpackage Controller
 */
class Unit_ConsultController extends Core_Controller
{

    /**
     * Liste les Unit_Model_PhysicalQuantity.
     *
     * @Secure("viewUnit")
     */
    public function physicalquantitiesAction()
    {
        $this->view->listStandardUnits = array();
        /* @var $standardUnit Unit_Model_Unit_Standard */
        foreach (Unit_Model_Unit_Standard::loadList() as $standardUnit) {
            $idStandardUnit = $standardUnit->getKey();
            $this->view->listStandardUnits[$idStandardUnit['id']] = $standardUnit->getName();
        }

        $queryBasePhyscialQuantity = new Core_Model_Query();
        $queryBasePhyscialQuantity->filter->addCondition(Unit_Model_PhysicalQuantity::QUERY_ISBASE, true);
        $this->view->basePhyscialQuantities = Unit_Model_PhysicalQuantity::loadList($queryBasePhyscialQuantity);
    }

    /**
     * Liste les Unit_Model_Unit_System.
     *
     * @Secure("viewUnit")
     */
    public function unitsystemsAction()
    {
    }

    /**
     * Liste les Unit_Model_Unit_Discrete.
     *
     * @Secure("viewUnit")
     */
    public function discreteunitsAction()
    {
    }

    /**
     * Liste les Unit_Model_Unit_Discrete.
     *
     * @Secure("viewUnit")
     */
    public function extendedunitsAction()
    {
    }

    /**
     * Liste les Unit_Model_Unit_Discrete.
     *
     * @Secure("viewUnit")
     */
    public function standardunitsAction()
    {
        $this->view->listPhysicalQuantities = array();
        /* @var $physicalQuantity Unit_Model_PhysicalQuantity */
        foreach (Unit_Model_PhysicalQuantity::loadList() as $physicalQuantity) {
            $idPhysicalQuantity = $physicalQuantity->getKey();
            $this->view->listPhysicalQuantities[$idPhysicalQuantity['id']] = $physicalQuantity->getName();
        }

        $this->view->listUnitSystems = array();
        /* @var $idUnitSystem Unit_Model_Unit_System */
        foreach (Unit_Model_Unit_System::loadList() as $unitSystem) {
            $idUnitSystem = $unitSystem->getKey();
            $this->view->listUnitSystems[$idUnitSystem['id']] = $unitSystem->getName();
        }
    }

}