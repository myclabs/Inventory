<?php

use Core\Annotation\Secure;
use MyCLabs\UnitAPI\UnitService;
use Unit\Domain\PhysicalQuantity;

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
     * Liste les unités discrètes.
     *
     * @Secure("viewUnit")
     */
    public function discreteunitsAction()
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
}
