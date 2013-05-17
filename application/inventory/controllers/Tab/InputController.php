<?php
/**
 * @author valentin.claras
 * @package Inventory
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Controlleur des onglets de la saisie d'une cellule.
 * @author valentin.claras
 * @package Inventory
 * @subpackage Controller
 */
class Inventory_Tab_InputController extends Core_Controller_Ajax
{
    use UI_Controller_Helper_Form;

    /**
     * Action fournissant la vue des documents pour l'input.
     * @Secure("viewCell")
     */
    public function commentsAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->_getParam('idCell');
        $this->view->idCell = $idCell;
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);

        $this->view->idCell = $idCell;
        $this->view->comments = $cellDataProvider->getSocialCommentsForInputSetPrimary();
        $this->view->isUserAbleToComment = User_Service_ACL::getInstance()->isAllowed(
            $this->_helper->auth(),
            Inventory_Action_Cell::INPUT(),
            $cellDataProvider
        );
    }

    /**
     * Action fournissant la vue des documents pour l'input.
     * @Secure("inputCell")
     */
    public function commentAddAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->_getParam('idCell');
        $this->view->idCell = $idCell;
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);

        $author = $this->_helper->auth();
        $formData = $this->getFormData('addComment');

        $content = $formData->getValue('content');
        if (empty($content)) {
            $this->addFormError('content', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        if (!$this->hasFormError()) {

            // Ajoute le commentaire
            $comment = new Social_Model_Comment($author);
            $comment->setText($content);
            $comment->save();
            $cellDataProvider->addSocialCommentForInputSetPrimary($comment);
            $cellDataProvider->save();

            // Retourne la vue du commentaire
            $this->_forward('comment-added', 'comment', 'social', ['comment' => $comment]);
            return;
        }
        $this->sendFormResponse();
    }

    /**
     * Action fournissant la vue des documents pour l'input.
     * @Secure("viewCell")
     */
    public function docsAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->_getParam('idCell');
        $this->view->idCell = $idCell;
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);

        $this->view->documentLibrary = null;
        if ($cellDataProvider->getGranularityDataProvider()->getCellsWithInputDocs()) {
            $this->view->documentLibrary = $cellDataProvider->getDocLibraryForAFInputSetsPrimary();
        } else {
            foreach ($orgaCell->getGranularity()->getBroaderGranularities() as $orgaGranularity) {
                $granularityDataProvider = Inventory_Model_GranularityDataProvider::loadByOrgaGranularity(
                    $orgaGranularity
                );
                if ($granularityDataProvider->getCellsWithInputDocs()) {
                    $parentCellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell(
                        $orgaCell->getParentCellForGranularity($orgaGranularity)
                    );
                    $this->view->documentLibrary = $parentCellDataProvider->getDocLibraryForAFInputSetsPrimary();
                    break;
                }
            }
        }
        $this->view->documentBibliography = $cellDataProvider->getDocBibliographyForAFInputSetPrimary();
    }

}
