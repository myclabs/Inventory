<?php

use Core\Annotation\Secure;

/**
 * @author valentin.claras
 */
class Orga_Tab_InputController extends Core_Controller
{
    use UI_Controller_Helper_Form;

    /**
     * @Secure("viewCell")
     */
    public function docsAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->getParam('idCell');
        $this->view->idCell = $idCell;
        $cell = Orga_Model_Cell::load($idCell);

        $this->view->documentLibrary = null;
        if ($cell->getGranularity()->getCellsWithInputDocuments()) {
            $this->view->documentLibrary = $cell->getDocLibraryForAFInputSetPrimary();
        } else {
            foreach ($cell->getGranularity()->getBroaderGranularities() as $granularity) {
                if ($granularity->getCellsWithInputDocuments()) {
                    $parentCell = $cell->getParentCellForGranularity($granularity);
                    $this->view->documentLibrary = $parentCell->getDocLibraryForAFInputSetPrimary();
                    break;
                }
            }
        }
    }
}
