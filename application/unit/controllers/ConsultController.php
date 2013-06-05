<?php
/**
 * @author valentin.claras
 * @package Unit
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Unit\Domain\Unit\StandardUnit;
use Unit\Domain\PhysicalQuantity;
use Unit\Domain\UnitSystem;

/**
 * Unit_ConsultController
 * @package Unit
 * @subpackage Controller
 */
class Unit_ConsultController extends Core_Controller
{

    /**
     * Liste les PhysicalQuantity.
     *
     * @Secure("viewUnit")
     */
    public function physicalquantitiesAction()
    {
        $this->view->listStandardUnits = array();
        /* @var $standardUnit StandardUnit */
        foreach (StandardUnit::loadList() as $standardUnit) {
            $idStandardUnit = $standardUnit->getKey();
            $this->view->listStandardUnits[$idStandardUnit['id']] = $standardUnit->getName();
        }

        $queryBasePhyscialQuantity = new Core_Model_Query();
        $queryBasePhyscialQuantity->filter->addCondition(PhysicalQuantity::QUERY_ISBASE, true);
        $this->view->basePhyscialQuantities = PhysicalQuantity::loadList($queryBasePhyscialQuantity);
    }

    /**
     * Liste les UnitSystem.
     *
     * @Secure("viewUnit")
     */
    public function unitsystemsAction()
    {
    }

    /**
     * Liste les DiscreteUnit.
     *
     * @Secure("viewUnit")
     */
    public function discreteunitsAction()
    {
    }

    /**
     * Liste les DiscreteUnit.
     *
     * @Secure("viewUnit")
     */
    public function extendedunitsAction()
    {
    }

    /**
     * Liste les DiscreteUnit.
     *
     * @Secure("viewUnit")
     */
    public function standardunitsAction()
    {
        $this->view->listPhysicalQuantities = array();
        /* @var $physicalQuantity PhysicalQuantity */
        foreach (PhysicalQuantity::loadList() as $physicalQuantity) {
            $idPhysicalQuantity = $physicalQuantity->getKey();
            $this->view->listPhysicalQuantities[$idPhysicalQuantity['id']] = $physicalQuantity->getName();
        }

        $this->view->listUnitSystems = array();
        /* @var $idUnitSystem UnitSystem */
        foreach (UnitSystem::loadList() as $unitSystem) {
            $idUnitSystem = $unitSystem->getKey();
            $this->view->listUnitSystems[$idUnitSystem['id']] = $unitSystem->getName();
        }
    }

}