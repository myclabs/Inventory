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
use Unit\Domain\ComposedUnit\ComposedUnit;

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
        $this->view->listStandardUnits = [];
        foreach (StandardUnit::loadList() as $standardUnit) {
            /* @var $standardUnit StandardUnit */
            $idStandardUnit = $standardUnit->getKey();
            $this->view->listStandardUnits[$idStandardUnit['id']] = $this->translator->toString($standardUnit->getName());
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
            $this->view->listPhysicalQuantities[$idPhysicalQuantity['id']] = $this->translator->toString($physicalQuantity->getName());
        }

        $this->view->listUnitSystems = array();
        /* @var $idUnitSystem UnitSystem */
        foreach (UnitSystem::loadList() as $unitSystem) {
            $idUnitSystem = $unitSystem->getKey();
            $this->view->listUnitSystems[$idUnitSystem['id']] = $this->translator->toString($unitSystem->getName());
        }
    }

    /**
     * Liste exemple de ComposedUnit.
     *
     * @Secure("viewUnit")
     */
    public function composedunitsAction()
    {
        $composedUnitRefs = [
            'homme.jour',
            'km.h^-1',
            'kg.m3^-1',
            'g_co2e.vehicule^-1.km^-1',
            'passager.vehicule^-1',
            't_co2e.mwh^-1'
        ];

        $this->view->composedUnits = [];
        foreach ($composedUnitRefs as $composedUnitRef) {
            $this->view->composedUnits[] = new ComposedUnit($composedUnitRef);
        }
    }

}
