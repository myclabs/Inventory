<?php
/**
 * @author valentin.claras
 * @package Unit
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Unit\Domain\PhysicalQuantity;

/**
 * Unit_Datagrids_PhysicalQuantitiesController
 * @package Unit
 * @subpackage Controller
 */
class Unit_Datagrids_PhysicalquantitiesController extends UI_Controller_Datagrid
{
    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * Récupération des paramètres de tris et filtres de la manière suivante :
     *  $this->request.
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * Renvoie la liste d'éléments, le nombre total et un message optionnel.
     *
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
            $element['name'] = $physicalQuantity->getName();
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