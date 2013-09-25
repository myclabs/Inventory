<?php

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Service responsable de la gestion des saisies
 *
 * @author  matthieu.napoli
 */
class Orga_Service_InputService
{
    /**
     * @var AF_Service_InputService
     */
    private $afInputService;

    /**
     * @var Orga_Service_ETLData
     */
    private $etlDataService;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var Core_Work_Dispatcher
     */
    private $workDispatcher;

    /**
     * @param AF_Service_InputService $afInputService
     * @param Orga_Service_ETLData    $etlDataService
     * @param EventDispatcher         $eventDispatcher
     * @param Core_Work_Dispatcher    $workDispatcher
     */
    public function __construct(
        AF_Service_InputService $afInputService,
        Orga_Service_ETLData $etlDataService,
        EventDispatcher $eventDispatcher,
        Core_Work_Dispatcher $workDispatcher
    ) {
        $this->afInputService = $afInputService;
        $this->etlDataService = $etlDataService;
        $this->eventDispatcher = $eventDispatcher;
        $this->workDispatcher = $workDispatcher;
    }

    /**
     * Modifie la saisie d'une cellule et recalcule les résultats si la saisie est complète
     *
     * @param Orga_Model_Cell $cell
     * @param AF_Model_InputSet_Primary $newValues Nouvelles valeurs pour les saisies
     * @throws InvalidArgumentException
     */
    public function editInput(Orga_Model_Cell $cell, AF_Model_InputSet_Primary $newValues)
    {
        try {
            $inputSet = $cell->getAFInputSetPrimary();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $inputSet = null;
        }

        // Si l'AF de la cellule a été changé, on discarde l'ancienne saisie
        if ($inputSet && $inputSet->getAF() !== $newValues->getAF()) {
            $inputSet = null;
        }

        // Injecte les coordonnées orga à la saisie en tant que ContextValue
        foreach ($cell->getMembers() as $member) {
            $newValues->setContextValue($member->getAxis()->getRef(), $member->getRef());
            // Membres parents
            foreach ($member->getAllParents() as $parentMember) {
                $newValues->setContextValue($parentMember->getAxis()->getRef(), $parentMember->getRef());
            }
        }

        if ($inputSet) {
            // Modification de la saisie
            $this->afInputService->editInputSet($inputSet, $newValues);

            $event = new Orga_Service_InputEditedEvent($cell);
        } else {
            // Création de la saisie
            $inputSet = $newValues;

            // Sauvegarde et attache à la cellule
            $inputSet->save();
            $cell->setAFInputSetPrimary($inputSet);
            $this->afInputService->updateResults($inputSet);

            $event = new Orga_Service_InputCreatedEvent($cell);
        }

        // Lance l'évènement
        $this->eventDispatcher->dispatch($event::NAME, $event);

        // Regénère DW
        $this->workDispatcher->runBackground(
            new Core_Work_ServiceCall_Task('Orga_Service_ETLData', 'clearDWResultsFromCell', [$cell])
        );
        if ($inputSet->isInputComplete()) {
            $this->workDispatcher->runBackground(
                new Core_Work_ServiceCall_Task('Orga_Service_ETLData', 'populateDWResultsFromCell', [$cell])
            );
        }
    }

    /**
     * Met à jour les résultats d'une saisie
     *
     * @param Orga_Model_Cell           $cell
     * @param AF_Model_InputSet_Primary $inputSet
     * @param AF_Model_AF|null          $af Permet d'uiliser un AF différent de celui de la saisie
     */
    public function updateResults(Orga_Model_Cell $cell, AF_Model_InputSet_Primary $inputSet, AF_Model_AF $af = null)
    {
        // Injecte les coordonnées orga à la saisie en tant que ContextValue
        foreach ($cell->getMembers() as $member) {
            $inputSet->setContextValue($member->getAxis()->getRef(), $member->getRef());
            // Membres parents
            foreach ($member->getAllParents() as $parentMember) {
                $inputSet->setContextValue($parentMember->getAxis()->getRef(), $parentMember->getRef());
            }
        }

        // Met à jour les résultats
        $this->afInputService->updateResults($inputSet, $af);
    }
}
