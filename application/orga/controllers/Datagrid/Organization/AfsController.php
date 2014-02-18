<?php

use AF\Domain\AF;
use Core\Annotation\Secure;
use User\Domain\ACL\Action;
use User\Domain\User;

/**
 * @author valentin.claras
 */
class Orga_Datagrid_Organization_AfsController extends UI_Controller_Datagrid
{
    /**
     * @Secure("editOrganization")
     */
    public function getelementsAction()
    {
        $idGranularity = $this->getParam('idGranularity');
        /** @var Orga_Model_Granularity $granularity */
        $inputGranularity = Orga_Model_Granularity::load($idGranularity);
        $configGranularity = $inputGranularity->getInputConfigGranularity();

        $this->request->filter->addCondition(Orga_Model_Cell::QUERY_RELEVANT, true);
        $this->request->filter->addCondition(Orga_Model_Cell::QUERY_ALLPARENTSRELEVANT, true);
        $this->request->filter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $configGranularity);

        $this->request->order->addOrder(Orga_Model_Cell::QUERY_TAG);
        /** @var Orga_Model_Cell $configCell */
        foreach (Orga_Model_Cell::loadList($this->request) as $configCell) {
            $data = [];
            $data['index'] = $configCell->getId();
            foreach ($configCell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getTag();
            }
            try {
                $cellsGroupDataProvider = $configCell->getCellsGroupForInputGranularity($inputGranularity);
                $data['af'] = $this->cellList($cellsGroupDataProvider->getAF()->getRef());
            } catch (Core_Exception_UndefinedAttribute $e) {
                // Aucun AF n'a encore été spécifié pour cette cellule et granularité.
            }

            $this->addLine($data);
        }
        $this->totalElements = Orga_Model_Cell::countTotal($this->request);

        $this->send();
    }

    /**
     * @Secure("editOrganization")
     */
    public function updateelementAction()
    {
        if ($this->update['column'] !== 'af') {
            parent::updateelementAction();
        }

        $inputGranularity = Orga_Model_Granularity::load($this->getParam('idGranularity'));

        $configCell = Orga_Model_Cell::load($this->update['index']);

        $aFRef = $this->update['value'];
        if (empty($aFRef)) {
            $aF = null;
        } else {
            $aF = AF::loadByRef($aFRef);
        }

        $cellsGroupDataProvider = $configCell->getCellsGroupForInputGranularity($inputGranularity);
        $cellsGroupDataProvider->setAF($aF);

        if ($aF !== null) {
            $this->data = $this->cellList($aF->getRef());
        } else {
            $this->data = $this->cellList(null);
        }
        $this->message = __('UI', 'message', 'updated');

        $this->send();
    }

}
