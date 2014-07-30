<?php

use Core\Annotation\Secure;
use Orga\Domain\Cell;

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
        $cellId = $this->getParam('cell');
        $this->view->assign('cellId', $cellId);
        $cell = Cell::load($cellId);

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
