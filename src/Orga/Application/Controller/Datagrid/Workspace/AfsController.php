<?php

use AF\Domain\AF;
use Core\Annotation\Secure;
use Orga\Domain\Cell;
use Orga\Domain\Granularity;

/**
 * @author valentin.claras
 */
class Orga_Datagrid_Workspace_AfsController extends UI_Controller_Datagrid
{
    /**
     * @Secure("editWorkspace")
     */
    public function getelementsAction()
    {
        $granularityId = $this->getParam('granularity');
        /** @var Granularity $granularity */
        $inputGranularity = Granularity::load($granularityId);
        $configGranularity = $inputGranularity->getInputConfigGranularity();

        $this->request->filter->addCondition(Cell::QUERY_RELEVANT, true);
        $this->request->filter->addCondition(Cell::QUERY_ALLPARENTSRELEVANT, true);
        $this->request->filter->addCondition(Cell::QUERY_GRANULARITY, $configGranularity);

        $this->request->order->addOrder(Cell::QUERY_TAG);
        /** @var Cell $configCell */
        foreach (Cell::loadList($this->request) as $configCell) {
            $data = [];
            $data['index'] = $configCell->getId();
            foreach ($configCell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getTag();
            }
            try {
                $subCellsGroup = $configCell->getSubCellsGroupForInputGranularity($inputGranularity);
                $data['af'] = $this->cellList($subCellsGroup->getAF()->getId());
            } catch (Core_Exception_UndefinedAttribute $e) {
                // Aucun AF n'a encore été spécifié pour cette cellule et granularité.
            }

            $this->addLine($data);
        }
        $this->totalElements = Cell::countTotal($this->request);

        $this->send();
    }

    /**
     * @Secure("editWorkspace")
     */
    public function updateelementAction()
    {
        if ($this->update['column'] !== 'af') {
            parent::updateelementAction();
        }

        $inputGranularity = Granularity::load($this->getParam('granularity'));

        /** @var Cell $configCell */
        $configCell = Cell::load($this->update['index']);

        $aFId = $this->update['value'];
        if (empty($aFId)) {
            $aF = null;
        } else {
            /** @var AF $aF */
            $aF = AF::load($aFId);
        }

        $subCellsGroup = $configCell->getSubCellsGroupForInputGranularity($inputGranularity);
        $subCellsGroup->setAF($aF);

        if ($aF !== null) {
            $this->data = $this->cellList($aF->getId());
        } else {
            $this->data = $this->cellList(null);
        }
        $this->message = __('UI', 'message', 'updated');

        $this->send();
    }

}
