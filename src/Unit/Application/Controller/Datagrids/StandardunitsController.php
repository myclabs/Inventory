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

        // Trie par grandeur physique
        usort($units, function (UnitDTO $a, UnitDTO $b) {
            return strcmp($a->physicalQuantity, $b->physicalQuantity);
        });

        foreach ($units as $unit) {
            if ($unit->type !== UnitDTO::TYPE_STANDARD) {
                continue;
            }

            $data = array();
            $data['index'] = $unit->id;
            $data['name'] = $this->cellTranslatedText($unit->label);
            $data['ref'] = $unit->id;
            $data['symbol'] = $this->cellTranslatedText($unit->symbol);
            $data['physicalQuantity'] = $this->cellList($unit->physicalQuantity);
            $data['unitSystem'] = $this->cellList($unit->unitSystem);

            $this->addLine($data);
        }

        $this->send();
    }
}
