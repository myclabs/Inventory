<?php
/**
 * @author matthieu.napoli
 * @package Techno
 */

use Core\Annotation\Secure;
use Keyword\Domain\Keyword;

/**
 * Controleur des dimensions
 * @package Techno
 */
class Techno_DimensionController extends Core_Controller
{

    use UI_Controller_Helper_Form;

    /**
     * Détails d'une dimension d'une famille
     * @Secure("editTechno")
     */
    public function detailsAction()
    {
        $idDimension = $this->getParam('id');
        $this->view->dimension = Techno_Model_Family_Dimension::load($idDimension);
        $this->view->family = $this->view->dimension->getFamily();
        $this->view->keywords = Keyword::loadList();
    }

    /**
     * Modification de la requête d'une dimension
     * @Secure("editTechno")
     */
    public function editQuerySubmitAction()
    {
        $formData = $this->getFormData('editQuery');
        $idDimension = $formData->getValue('id');
        /** @var $dimension Techno_Model_Family_Dimension */
        $dimension = Techno_Model_Family_Dimension::load($idDimension);
        $dimension->setQuery($formData->getValue('query'));
        $dimension->save();
        $this->entityManager->flush();

        $this->setFormMessage(__('UI', 'message', 'updated'));
        $this->sendFormResponse();
    }

}
