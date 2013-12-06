<?php

use Core\Annotation\Secure;
use MyCLabs\UnitAPI\UnitService;
use Unit\Domain\Unit\StandardUnit;
use Unit\Domain\PhysicalQuantity;
use Unit\Domain\ComposedUnit\ComposedUnit;

/**
 * @author valentin.claras
 * @author matthieu.napoli
 */
class Unit_ConsultController extends Core_Controller
{
    /**
     * @Inject
     * @var UnitService
     */
    private $unitService;

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
     * Liste les unités standards.
     *
     * @Secure("viewUnit")
     */
    public function standardunitsAction()
    {
        $this->view->assign('unitSystems', $this->unitService->getUnitSystems());
        $this->view->assign('physicalQuantities', $this->unitService->getPhysicalQuantities());
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
