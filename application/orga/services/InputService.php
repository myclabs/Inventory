<?php

use AF\Domain\AF;
use AF\Domain\InputSet\PrimaryInputSet;
use Core\Work\ServiceCall\ServiceCallTask;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
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
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var WorkDispatcher
     */
    private $workDispatcher;

    /**
     * @param AF_Service_InputService $afInputService
     * @param EventDispatcher         $eventDispatcher
     * @param WorkDispatcher          $workDispatcher
     */
    public function __construct(
        AF_Service_InputService $afInputService,
        EventDispatcher $eventDispatcher,
        WorkDispatcher $workDispatcher
    ) {
        $this->afInputService = $afInputService;
        $this->eventDispatcher = $eventDispatcher;
        $this->workDispatcher = $workDispatcher;
    }

    /**
     * Modifie la saisie d'une cellule et recalcule les résultats si la saisie est complète
     *
     * @param Orga_Model_Cell $cell
     * @param \AF\Domain\InputSet\PrimaryInputSet $newValues Nouvelles valeurs pour les saisies
     * @throws InvalidArgumentException
     */
    public function editInput(Orga_Model_Cell $cell, PrimaryInputSet $newValues)
    {
        $inputSet = $cell->getAFInputSetPrimary();

        // Si l'AF de la cellule a été changé, on discarde l'ancienne saisie
        if ($inputSet && $inputSet->getAF() !== $newValues->getAF()) {
            $inputSet->setAF($newValues->getAF());
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
            new ServiceCallTask('Orga_Service_ETLData', 'clearDWResultsFromCell', [$cell])
        );
        if ($inputSet->isInputComplete()) {
            $this->workDispatcher->runBackground(
                new ServiceCallTask('Orga_Service_ETLData', 'populateDWResultsFromCell', [$cell])
            );
        }
        // Regénère l'exports de la cellule.
        $this->workDispatcher->runBackground(
            new ServiceCallTask('Orga_Service_Export', 'saveCellInput', [$cell])
        );
    }

    /**
     * Met à jour les résultats d'une saisie
     *
     * @param Orga_Model_Cell           $cell
     * @param \AF\Domain\InputSet\PrimaryInputSet $inputSet
     * @param \AF\Domain\AF|null          $af Permet d'uiliser un AF différent de celui de la saisie
     */
    public function updateResults(Orga_Model_Cell $cell, PrimaryInputSet $inputSet, AF $af = null)
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
