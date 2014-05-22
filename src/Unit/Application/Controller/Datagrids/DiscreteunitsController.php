<?php
/**
 * @author valentin.claras
 * @package Unit
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Unit\Domain\Unit\DiscreteUnit;

/**
 * Unit_Tableau_DiscreteUnitsController
 * @package Unit
 * @subpackage Controller
 */
class Unit_Datagrids_DiscreteunitsController extends UI_Controller_Datagrid
{
    /**
     * @Secure("viewUnit")
     */
    public function getelementsAction()
    {
        /* @var $discreteUnit DiscreteUnit */
        foreach (DiscreteUnit::loadList($this->request) as $discreteUnit) {
            $element = array();
            $idDiscreteUnit = $discreteUnit->getKey();
            $element['index'] = $idDiscreteUnit['id'];
            $element['name'] = $this->cellTranslatedText($discreteUnit->getName());
            $element['ref'] = $discreteUnit->getRef();
            $element['symbole'] = $this->cellTranslatedText($discreteUnit->getSymbol());
            $this->addLine($element);
        }
        $this->totalElements = DiscreteUnit::countTotal($this->request);
        $this->send();
    }
}
