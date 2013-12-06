<?php

use Core\Annotation\Secure;
use MyCLabs\UnitAPI\DTO\UnitDTO;
use MyCLabs\UnitAPI\UnitService;

class Unit_Datagrids_StandardunitsController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var UnitService
     */
    private $unitService;

    /**
     * @Secure("viewUnit")
     */
    public function getelementsAction()
    {
        $units = $this->unitService->getUnits();

        foreach ($units as $unit) {
            if ($unit->type !== UnitDTO::TYPE_STANDARD) {
                continue;
            }

            $data = array();
            $data['index'] = $unit->id;
            $data['name'] = $unit->label;
            $data['ref'] = $unit->id;
            $data['symbol'] = $unit->symbol;
            $data['physicalQuantity'] = '';
            $data['unitSystem'] = $unit->unitSystem;

            $this->addLine($data);
        }

        $this->send();
    }
}
