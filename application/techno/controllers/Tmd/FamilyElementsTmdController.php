<?php
/**
 * @author  matthieu.napoli
 * @package Techno
 */

use Core\Annotation\Secure;

/**
 * @package Techno
 */
class Techno_Tmd_FamilyElementsTmdController extends Core_Controller
{

    /**
     * Create an empty element for a cell
     * @Secure("editTechno")
     */
    public function addElementAction()
    {
        $idFamily = $this->getParam('id');
        /** @var $family Techno_Model_Family */
        $family = Techno_Model_Family::load($idFamily);
        // Récupère la cellule
        $coordinates = explode('#', $this->getParam('coordinates'));
        $members = [];
        $index = 0;
        foreach ($family->getDimensions() as $dimension) {
            $members[] = $dimension->getMember(Keyword_Model_Keyword::loadByRef($coordinates[$index]));
            $index++;
        }
        $cell = $family->getCell($members);
        // Vérifie qu'il n'y a pas déjà d'élément choisi
        $chosenElement = $cell->getChosenElement();
        if ($chosenElement !== null) {
            throw new Core_Exception("Un élément est déjà choisi pour ces coordonnées.");
        }
        // Crée un élément vide et l'ajoute à la cellule
        if ($family instanceof Techno_Model_Family_Process) {
            $element = new Techno_Model_Element_Process();
        } else {
            $element = new Techno_Model_Element_Coeff();
        }
        $element->setBaseUnit($family->getBaseUnit());
        $element->setUnit($family->getUnit());
        $element->save();
        $cell->setChosenElement($element);
        $cell->save();
        $this->entityManager->flush();
        $this->sendJsonResponse(
            [
                'elementId' => $element->getId(),
                'message' => __('Techno', 'element', 'added'),
                'type'    => 'success',
            ]
        );
    }

    /**
     * Create an empty element for a cell
     * @Secure("editTechno")
     */
    public function deleteElementAction()
    {
        $idFamily = $this->getParam('idFamily');
        /** @var $family Techno_Model_Family */
        $family = Techno_Model_Family::load($idFamily);
        // Récupère la cellule
        $coordinates = explode('-', $this->getParam('coordinates'));
        $members = [];
        $index = 0;
        foreach ($family->getDimensions() as $dimension) {
            $members[] = $dimension->getMember(Keyword_Model_Keyword::loadByRef($coordinates[$index]));
            $index++;
        }
        $cell = $family->getCell($members);
        // Vérifie qu'il n'y a pas déjà d'élément choisi
        $chosenElement = $cell->getChosenElement();
        $cell->setChosenElement(null);
        $cell->save();
        $chosenElement->delete();
        $this->entityManager->flush();
        $this->sendJsonResponse(
            [
                'message' => __('Techno', 'element', 'deleted'),
                'type'    => 'success',
            ]
        );
    }

}
