<?php
/**
 * @author  matthieu.napoli
 * @package Techno
 */

use Core\Annotation\Secure;

/**
 * @package Techno
 */
class Techno_Tmd_FamilyElementsTmdController extends Core_Controller_Ajax
{

    /**
     * Create an empty element for a cell
     * @Secure("editTechno")
     */
    public function addElementAction()
    {
        $idFamily = $this->_getParam('id');
        /** @var $family Techno_Model_Family */
        $family = Techno_Model_Family::load($idFamily);
        // Récupère la cellule
        $coordinates = $this->_getParam('coordinates');
        $members = [];
        $index = 0;
        foreach ($family->getDimensions() as $dimension) {
            $dimensionMembers = $dimension->getMembers();
            $members[] = $dimensionMembers[$coordinates[$index]];
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
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->sendJsonResponse([
                                'message' => 'Un élément vide a été créé.',
                                'type'    => 'success',
                                ]);
    }

}
