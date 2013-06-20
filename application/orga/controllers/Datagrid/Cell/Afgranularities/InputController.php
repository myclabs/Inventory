<?php
/**
 * @author valentin.claras
 * @package Orga
 */

use Core\Annotation\Secure;

/**
 * Controller du datagrid des saisies des formulaires des cellules.
 * @package Orga
 */
class Orga_Datagrid_Cell_Afgranularities_InputController extends UI_Controller_Datagrid
{

    /**
     * Methode appelee pour remplir le tableau.
     * @Secure("viewCell")
     */
    public function getelementsAction()
    {
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

        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);

        $inputGranularity = Orga_Model_Granularity::load($this->getParam('idGranularity'));

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
            $data = array();
            $data['index'] = $childCell->getId();
            foreach ($childCell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getRef();
            }

            $data['inventoryStatus'] = $childCell->getInventoryStatus();

            if ($data['inventoryStatus'] !== Orga_Model_Cell::STATUS_NOTLAUNCHED) {
                try {
                    $aFInputSetPrimary = $childCell->getAFInputSetPrimary();
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
                    $data['advancementInput'] = $this->cellPercent(0, 'danger');
                    $data['stateInput'] = AF_Model_InputSet_Primary::STATUS_INPUT_INCOMPLETE;
                }

                try {
                    // Vérification qu'un AF est défini.
                    $cellsGroup = $childCell->getParentCellForGranularity($inputGranularity->getInputConfigGranularity())
                        ->getCellsGroupForInputGranularity($inputGranularity);
                    $cellsGroup->getAF();

                    $isUserAllowedToInputCell = User_Service_ACL::getInstance()->isAllowed(
                        $this->_helper->auth(),
                        Orga_Action_Cell::INPUT(),
                        $childCell
                    );
                    try {
                        $inputSetPrimary = $childCell->getAFInputSetPrimary();
                    } catch (Core_Exception_UndefinedAttribute $e) {
                        $inputSetPrimary = null;
                    }
                    if (($isUserAllowedToInputCell) || ($inputSetPrimary !== null)) {
                        $data['link'] = $this->cellLink('orga/cell/input/idCell/'.$childCell->getId().'/fromIdCell/'.$idCell);
                    }
                } catch (Core_Exception_UndefinedAttribute $e) {
                    // Pas d'AF configuré, donc pas de lien vers la saisie.
                }
            } else {
                $data['advancementInput'] = null;
                $data['stateInput'] = null;
            }

            $this->addLine($data);
        }
        $this->totalElements = $cell->countTotalChildCellsForGranularity($inputGranularity, $this->request);

        $this->send();
    }

}
