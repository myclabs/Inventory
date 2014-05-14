<?php

use Core\Annotation\Secure;
use Unit\Domain\PhysicalQuantity;

class Unit_Datagrids_PhysicalquantitiesController extends UI_Controller_Datagrid
{
    /**
     * @Secure("viewUnit")
     */
    public function getelementsAction()
    {
        $queryBasePhyscialQuantity = new Core_Model_Query();
        $queryBasePhyscialQuantity->filter->addCondition(PhysicalQuantity::QUERY_ISBASE, true);
        $basePhyscialQuantities = PhysicalQuantity::loadList($queryBasePhyscialQuantity);

        /* @var $physicalQuantity PhysicalQuantity */
        foreach (PhysicalQuantity::loadList($this->request) as $physicalQuantity) {
            $element = array();
            $idPhysicalQuantity = $physicalQuantity->getKey();
            $element['index'] = $idPhysicalQuantity['id'];
            $element['name'] = $this->cellTranslatedText($physicalQuantity->getName());
            $element['ref'] = $physicalQuantity->getRef();
            $idReferenceUnit = $physicalQuantity->getReferenceUnit()->getKey();
            $element['referenceUnit'] = $this->cellList($idReferenceUnit['id']);
            foreach ($basePhyscialQuantities as $basePhyscialQuantity) {
                $element[$basePhyscialQuantity->getRef()] = 0;
            }
            foreach ($physicalQuantity->getReferenceUnit()->getNormalizedUnit() as $unitArray) {
                $element[$unitArray['unit']->getPhysicalQuantity()->getRef()] = $unitArray['exponent'];
            }
            $this->addLine($element);
        }
        $this->totalElements = PhysicalQuantity::countTotal($this->request);
        $this->send();
    }
}
