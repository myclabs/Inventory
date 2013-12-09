<?php
/**
 * @author valentin.claras
 * @author maxime.fourt
 * @package Orga
 */

use Core\Annotation\Secure;

/**
 * Classe NavigationController
 * @package Orga
 * @subpackage Controller
 */
class Orga_Forms_NavigationController extends Core_Controller
{
    /**
     * accéder à une cellule par le volet de navigation
     * @Secure("viewCell")
     */
    public function gotocellAction()
    {
        if ($this->getRequest()->isPost()) {
            $departureCell = Orga_Model_Cell::load($this->getParam('idCell'));
            $narrowerGranularity = Orga_Model_Granularity::load($this->getParam('idGranularity'));

            $listMembers = array();
            if ($departureCell->hasMembers()) {
                foreach ($departureCell->getMembers() as $member) {
                    foreach ($narrowerGranularity->getAxes() as $narrowerAxis) {
                        if ($member->getAxis() === $narrowerAxis) {
                            $listMembers[] = $member;
                        } elseif (!$departureCell->getGranularity()->hasAxis($narrowerAxis)) {
                            $listMembers[] = $narrowerAxis->getMemberByCompleteRef($this->getParam($narrowerAxis->getRef()));
                        }
                    }
                }
                $listMembers = array_unique($listMembers);
            } else {
                foreach ($narrowerGranularity->getAxes() as $narrowerAxis) {
                    $listMembers[] = $narrowerAxis->getMemberByCompleteRef($this->getParam($narrowerAxis->getRef()));
                }
            }

            $arrivalCell = $narrowerGranularity->getCellByMembers($listMembers);
            if (!$arrivalCell->isRelevant()) {
                UI_Message::addMessageStatic(
                    __('Orga', 'navigation', 'irrelevantCell',
                        array('cell' => $arrivalCell->getLabel()))
                );
                $idCell = $departureCell->getId();
            } else {
                $idCell = $arrivalCell->getId();
            }
            $this->redirect('orga/cell/details/idCell/'.$idCell);
        }
    }
}
