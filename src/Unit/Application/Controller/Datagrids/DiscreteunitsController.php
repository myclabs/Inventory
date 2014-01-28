<?php

use Core\Annotation\Secure;
use MyCLabs\UnitAPI\DTO\UnitDTO;
use MyCLabs\UnitAPI\UnitService;

class Unit_Datagrids_DiscreteunitsController extends UI_Controller_Datagrid
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
            if ($unit->type !== UnitDTO::TYPE_DISCRETE) {
                continue;
            }

            $element = array();
            $element['index'] = $unit->id;
            $element['label'] = $unit->label;
            $element['id'] = $unit->id;

            $this->addLine($element);
        }

        $this->send();
    }
}
