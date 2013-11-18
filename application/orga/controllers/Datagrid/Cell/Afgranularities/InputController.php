<?php
/**
 * @author valentin.claras
 * @package Orga
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Orga\Model\ACL\Action\CellAction;
use User\Domain\ACL\ACLService;

/**
 * Controller du datagrid des saisies des formulaires des cellules.
 * @package Orga
 */
class Orga_Datagrid_Cell_Afgranularities_InputController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var ACLService
     */
    private $aclService;

    /**
     * Methode appelee pour remplir le tableau.
     * @Secure("viewCell")
     */
    public function getelementsAction()
    {
        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);

        $inputGranularity = Orga_Model_Granularity::load($this->getParam('idGranularity'));
        try {
            $granularityForInventoryStatus = $cell->getGranularity()->getOrganization()->getGranularityForInventoryStatus();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $granularityForInventoryStatus = false;
        }
        $isInputInInventory = ($granularityForInventoryStatus
            && ($inputGranularity->isNarrowerThan($granularityForInventoryStatus)
                || $inputGranularity->getRef() === $granularityForInventoryStatus->getRef()));

        if ($cell->getGranularity()->getRef() === $inputGranularity->getRef()) {
            $this->addLineData($cell, $inputGranularity, $isInputInInventory, $idCell);
            $this->totalElements = 1;
        } else {
            $customParameters = [];
            $filterConditions = [];
            foreach ($this->request->filter->getConditions() as $filterConditionArray) {
                if ($filterConditionArray['alias'] == Orga_Model_Member::getAlias()) {
                    $customParameters[] = $filterConditionArray;
                } elseif (($filterConditionArray['alias'] == AF_Model_InputSet_Primary::getAlias())
                    && ($filterConditionArray['name'] == AF_Model_InputSet_Primary::QUERY_COMPLETION)
                    && (($filterConditionArray['operator'] == Core_Model_Filter::OPERATOR_LOWER)
                        || (($filterConditionArray['operator'] == Core_Model_Filter::OPERATOR_EQUAL)
                            && ($filterConditionArray['value'] == 0)))
                ) {
                    $subFilter = new Core_Model_Filter();
                    $subFilter->condition = Core_Model_Filter::CONDITION_OR;
                    $subFilter->setConditions(array($filterConditionArray));
                    $subFilter->addCondition(
                        Orga_Model_Cell::QUERY_AFINPUTSETPRIMARY,
                        null,
                        Core_Model_Filter::OPERATOR_NULL,
                        Orga_Model_Cell::getAlias()
                    );
                    $filterConditions[] = array(
                        'name' => AF_Model_InputSet_Primary::QUERY_COMPLETION,
                        'value' => $subFilter,
                        'operator' => Core_Model_Filter::OPERATOR_SUB_FILTER,
                        'alias' => AF_Model_InputSet_Primary::getAlias()
                    );
                } else {
                    $filterConditions[] = $filterConditionArray;
                }
            }
            $this->request->setCustomParameters($customParameters);
            $this->request->filter->setConditions($filterConditions);
            $this->request->filter->addCondition(
                Orga_Model_Cell::QUERY_ALLPARENTSRELEVANT,
                true,
                Core_Model_Filter::OPERATOR_EQUAL,
                Orga_Model_Cell::getAlias()
            );
            $this->request->filter->addCondition(
                Orga_Model_Cell::QUERY_RELEVANT,
                true,
                Core_Model_Filter::OPERATOR_EQUAL,
                Orga_Model_Cell::getAlias()
            );
            $this->request->order->addOrder(
                Orga_Model_Cell::QUERY_MEMBERS_HASHKEY,
                Core_Model_Order::ORDER_ASC,
                Orga_Model_Cell::getAlias()
            );
            foreach ($cell->loadChildCellsForGranularity($inputGranularity, $this->request) as $childCell) {
                $this->addLineData($childCell, $inputGranularity, $isInputInInventory, $idCell);
            }
            $this->totalElements = $cell->countTotalChildCellsForGranularity($inputGranularity, $this->request);
        }

        $this->send();
    }
    
    private function addLineData(Orga_Model_Cell $cell, Orga_Model_Granularity $inputGranularity, $isInputInInventory, $fromIdCell)
    {
        $data = array();
        $data['index'] = $cell->getId();
        foreach ($cell->getMembers() as $member) {
            $data[$member->getAxis()->getRef()] = $member->getCompleteRef();
        }

        if ($isInputInInventory) {
            $data['inventoryStatus'] = $cell->getInventoryStatus();
        }

        if (!$isInputInInventory || ($data['inventoryStatus'] !== Orga_Model_Cell::STATUS_NOTLAUNCHED)) {
            try {
                // Vérification qu'un AF est défini.
                if ($cell->getGranularity()->getRef() === $inputGranularity->getInputConfigGranularity()->getRef()) {
                    $cellsGroup = $cell->getCellsGroupForInputGranularity($inputGranularity);
                } else {
                    $cellsGroup = $cell->getParentCellForGranularity(
                        $inputGranularity->getInputConfigGranularity()
                    )->getCellsGroupForInputGranularity($inputGranularity);
                }
                $cellsGroup->getAF();

                $isUserAllowedToInputCell = $this->aclService->isAllowed(
                    $this->_helper->auth(),
                    CellAction::INPUT(),
                    $cell
                );
                try {
                    $aFInputSetPrimary = $cell->getAFInputSetPrimary();
                    $percent = $aFInputSetPrimary->getCompletion();
                    $progressBarColor = null;
                    switch ($aFInputSetPrimary->getStatus()) {
                        case AF_Model_InputSet_Primary::STATUS_FINISHED:
                            $progressBarColor = 'success';
                            break;
                        case AF_Model_InputSet_Primary::STATUS_COMPLETE:
                            $progressBarColor = 'warning';
                            break;
                        case AF_Model_InputSet_Primary::STATUS_CALCULATION_INCOMPLETE:
                            $progressBarColor = 'danger';
                            break;
                        case AF_Model_InputSet_Primary::STATUS_INPUT_INCOMPLETE:
                            $progressBarColor = 'danger';
                            break;
                    }
                    $data['advancementInput'] = $this->cellPercent($percent, $progressBarColor);
                    $data['stateInput'] = $aFInputSetPrimary->getStatus();
                } catch (Core_Exception_UndefinedAttribute $e) {
                    $aFInputSetPrimary = null;
                    $data['advancementInput'] = $this->cellPercent(0, 'danger');
                    $data['stateInput'] = AF_Model_InputSet_Primary::STATUS_INPUT_INCOMPLETE;
                }
                if (($isUserAllowedToInputCell) || ($aFInputSetPrimary !== null)) {
                    $data['link'] = $this->cellLink('orga/cell/input/idCell/'.$cell->getId().'/fromIdCell/'.$fromIdCell);
                }
            } catch (Core_Exception_UndefinedAttribute $e) {
                // Pas d'AF configuré, donc pas de lien vers la saisie.
                $data['stateInput'] = null;
            }
        } else {
            $data['advancementInput'] = null;
            $data['stateInput'] = null;
        }
        
        $this->addLine($data);
    }

}
