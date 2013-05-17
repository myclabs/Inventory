<?php
/**
 * @author valentin.claras
 * @package Inventory
 */

use Core\Annotation\Secure;

/**
 * Controller du datagrid des saisies des formulaires des cellules.
 * @package Inventory
 */
class Inventory_Datagrid_Cell_Afgranularities_InputController extends UI_Controller_Datagrid
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
                    Inventory_Model_CellDataProvider::QUERY_AFINPUTSETPRIMARY,
                    null,
                    Core_Model_Filter::OPERATOR_NULL,
                    Inventory_Model_CellDataProvider::getAlias()
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

        $idCell = $this->_getParam('idCell');
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);

        $aFInputOrgaGranularity = Orga_Model_Granularity::load(array('id' => $this->_getParam('idGranularity')));

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

        foreach ($cellDataProvider->getChildCellsForGranularity($aFInputOrgaGranularity, $this->request)
                 as $childCellDataProvider) {
            $childOrgaCell = $childCellDataProvider->getOrgaCell();

            $data = array();
            $data['index'] = $childOrgaCell->getKey()['id'];
            foreach ($childOrgaCell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getRef();
            }

            $data['inventoryStatus'] = $childCellDataProvider->getInventoryStatus();

            if ($data['inventoryStatus'] !== Inventory_Model_CellDataProvider::STATUS_NOTLAUNCHED) {
                try {
                    $aFInputSetPrimary = $childCellDataProvider->getAFInputSetPrimary();
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
                    $gesIndicator = Classif_Model_Indicator::loadByRef('ges');
                    if ($aFInputSetPrimary->getOutputSet()) {
                        $output = $aFInputSetPrimary->getOutputSet()->getTotalByIndicator($gesIndicator);
                    } else {
                        $output = null;
                    }
                    if ($output === null) {
                        $data['totalValueGESInput'] = $this->cellNumber(0);
                        $data['totalUncertaintyGESInput'] = $this->cellNumber(0, null, 0);
                    } else {
                        $value = $output->getValue();
                        $data['totalValueGESInput'] = $this->cellNumber($value->digitalValue);
                        $data['totalUncertaintyGESInput'] = $this->cellNumber($value->relativeUncertainty, null, 0);
                    }
                } catch (Core_Exception_UndefinedAttribute $e) {
                    $data['advancementInput'] = $this->cellPercent(0, 'danger');
                    $data['stateInput'] = AF_Model_InputSet_Primary::STATUS_INPUT_INCOMPLETE;
                    $data['totalValueGESInput'] = $this->cellNumber(0);
                    $data['totalUncertaintyGESInput'] = $this->cellNumber(0);
                }

                try {
                    // Vérification qu'un AF est défini.
                    $aFGranularities = Inventory_Model_AFGranularities::loadByAFInputOrgaGranularity(
                        $childOrgaCell->getGranularity()
                    );
                    $cellsGroupDataProvider = $aFGranularities->getCellsGroupDataProviderForContainerCellDataProvider(
                        Inventory_Model_CellDataProvider::loadByOrgaCell(
                            $childOrgaCell->getParentCellForGranularity($aFGranularities->getAFConfigOrgaGranularity())
                        )
                    );

                    $isUserAllowedToInputCell = User_Service_ACL::getInstance()->isAllowed(
                        $this->_helper->auth(),
                        Inventory_Action_Cell::INPUT(),
                        $childCellDataProvider
                    );
                    try {
                        $inputSetPrimary = $childCellDataProvider->getAFInputSetPrimary();
                    } catch (Core_Exception_UndefinedAttribute $e) {
                        $inputSetPrimary = null;
                    }
                    if (($isUserAllowedToInputCell) || ($inputSetPrimary !== null)) {
                        $data['link'] = $this->cellLink(
                            'inventory/cell/input/idCell/'.$childOrgaCell->getKey()['id'].'/fromIdCell/'.$idCell
                        );
                    }
                } catch (Core_Exception_NotFound $e) {
                    // Pas d'AF configuré, donc pas de lien vers la saisie.
                }
            } else {
                $data['advancementInput'] = null;
                $data['stateInput'] = null;
                $data['totalValueGESInput'] = null;
                $data['totalUncertaintyGESInput'] = null;
            }

            $this->addLine($data);
        }
        $this->totalElements = $cellDataProvider->countTotalChildCellsForGranularity($aFInputOrgaGranularity,
                                                                                     $this->request);

        $this->send();
    }

}
