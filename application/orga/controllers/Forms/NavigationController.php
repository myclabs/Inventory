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
class Orga_Forms_NavigationController extends Core_Controller_Ajax
{
    /**
     * accéder à une cellule par le volet de navigation
     * @Secure("viewCell")
     */
    public function gotocellAction()
    {
        if ($this->getRequest()->isPost()) {
            $departureCell = Orga_Model_Cell::load(array('id' => $this->getParam('idCell')));
            $narrowerGranularity = Orga_Model_Granularity::load($this->getParam('idGranularity'));

            $listMembers = array();
            if ($departureCell->hasMembers()) {
                foreach ($departureCell->getMembers() as $member) {
                    foreach ($narrowerGranularity->getAxes() as $narrowerAxis) {
                        if ($member->getAxis() === $narrowerAxis) {
                            $listMembers[] = $member;
                        } elseif (!$departureCell->getGranularity()->hasAxis($narrowerAxis)) {
                            $listMembers[] = Orga_Model_Member::loadByCompleteRefAndAxis(
                                $this->getParam($narrowerAxis->getRef()),
                                $narrowerAxis
                            );
                        }
                    }
                }
                $listMembers = array_unique($listMembers);
            } else {
                foreach ($narrowerGranularity->getAxes() as $narrowerAxis) {
                    $listMembers[] = Orga_Model_Member::loadByCompleteRefAndAxis(
                        $this->getParam($narrowerAxis->getRef()),
                        $narrowerAxis
                    );
                }
            }

            $arrivalCell = Orga_Model_Cell::loadByGranularityAndListMembers($narrowerGranularity, $listMembers);
            if (!$arrivalCell->isRelevant()) {
                UI_Message::addMessageStatic(
                    __('Orga', 'navigation', 'irrelevantCell',
                        array('cell' => $arrivalCell->getLabel()))
                );
                $idCell = $departureCell->getKey()['id'];
            } else {
                $idCell = $arrivalCell->getKey()['id'];
            }
            $this->redirect(urldecode($this->getParam('url')).'idCell='.$idCell);
        }
    }
}
