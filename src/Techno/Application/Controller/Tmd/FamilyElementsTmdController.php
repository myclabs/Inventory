<?php

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Keyword\Application\Service\KeywordService;
use Techno\Domain\Element\CoeffElement;
use Techno\Domain\Element\ProcessElement;
use Techno\Domain\Family\Family;
use Techno\Domain\Family\ProcessFamily;

/**
 * @author matthieu.napoli
 */
class Techno_Tmd_FamilyElementsTmdController extends Core_Controller
{
    /**
     * @Inject
     * @var KeywordService
     */
    protected $keywordService;

    /**
     * Create an empty element for a cell
     * @Secure("editTechno")
     */
    public function addElementAction()
    {
        /** @var $family Family */
        $family = Family::load($this->getParam('id'));
        // Récupère la cellule
        $coordinates = explode('#', $this->getParam('coordinates'));
        $members = [];
        $index = 0;
        foreach ($family->getDimensions() as $dimension) {
            $members[] = $dimension->getMember($this->keywordService->get($coordinates[$index]));
            $index++;
        }
        $cell = $family->getCell($members);
        // Vérifie qu'il n'y a pas déjà d'élément choisi
        $chosenElement = $cell->getChosenElement();
        if ($chosenElement !== null) {
            throw new Core_Exception("Un élément est déjà choisi pour ces coordonnées.");
        }
        // Crée un élément vide et l'ajoute à la cellule
        if ($family instanceof ProcessFamily) {
            $element = new ProcessElement();
        } else {
            $element = new CoeffElement();
        }
        $element->setBaseUnit($family->getBaseUnit());
        $element->setUnit($family->getUnit());
        $element->save();
        $cell->setChosenElement($element);
        $cell->save();
        $this->entityManager->flush();
        $this->sendJsonResponse(['elementId' => $element->getId()]);
    }

    /**
     * Create an empty element for a cell
     * @Secure("editTechno")
     */
    public function deleteElementAction()
    {
        /** @var $family Family */
        $family = Family::load($this->getParam('idFamily'));
        // Récupère la cellule
        $coordinates = explode('-', $this->getParam('coordinates'));
        $members = [];
        $index = 0;
        foreach ($family->getDimensions() as $dimension) {
            $members[] = $dimension->getMember($this->keywordService->get($coordinates[$index]));
            $index++;
        }
        $cell = $family->getCell($members);
        // Vérifie qu'il n'y a pas déjà d'élément choisi
        $chosenElement = $cell->getChosenElement();
        $cell->setChosenElement(null);
        $cell->save();
        $chosenElement->delete();
        $this->entityManager->flush();
        if ($chosenElement->getValue()->getDigitalValue() !== null) {
            $this->sendJsonResponse(['message' => __('UI', 'message', 'deleted')]);
        } else {
            $this->sendJsonResponse(['message' => '']);
        }
    }
}
