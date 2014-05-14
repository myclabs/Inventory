<?php
/**
 * @author yoann.croizer
 * @package Unit
 */

use Core\Annotation\Secure;
use Unit\Domain\Unit\StandardUnit;

/**
 * Unit_Tableau_ListeunitsController
 * @package Unit
 * @subpackage Controller
 */
class Unit_Datagrids_StandardunitsController extends UI_Controller_Datagrid
{
    /**
     * @Secure("viewUnit")
     */
    public function getelementsAction()
    {
        /* @var $standardUnit \Unit\Domain\Unit\StandardUnit */
        foreach (StandardUnit::loadList($this->request) as $standardUnit) {
            $element = array();
            $idStandardUnit = $standardUnit->getKey();
            $element['index'] = $idStandardUnit['id'];
            $element['name'] = $this->cellTranslatedText($standardUnit->getName());
            $element['ref'] = $standardUnit->getRef();
            $element['symbol'] = $this->cellTranslatedText($standardUnit->getSymbol());
            $idPhysicalQuantity = $standardUnit->getPhysicalQuantity()->getKey();
            $element['physicalQuantity'] = $this->cellList($idPhysicalQuantity['id']);
            $element['multiplier'] = Core_Locale::loadDefault()->formatNumber($standardUnit->getMultiplier(), 10);
            $idUnitSystem = $standardUnit->getUnitSystem()->getKey();
            $element['unitSystem'] = $this->cellList($idUnitSystem['id']);
            $this->addLine($element);
        }
        $this->totalElements = StandardUnit::countTotal($this->request);
        $this->send();
    }
}
