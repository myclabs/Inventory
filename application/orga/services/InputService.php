<?php

use AF\Domain\AF;
use AF\Domain\InputService;
use AF\Domain\InputService\InputSetInconsistencyFinder;
use AF\Domain\InputSet\PrimaryInputSet;
use Core\Work\ServiceCall\ServiceCallTask;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Service responsable de la gestion des saisies
 *
 * @author matthieu.napoli
 */
class Orga_Service_InputService
{
    /**
     * @var InputService
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

    public function __construct(
        InputService $afInputService,
        EventDispatcher $eventDispatcher,
        WorkDispatcher $workDispatcher
    ) {
        $this->afInputService = $afInputService;
        $this->eventDispatcher = $eventDispatcher;
        $this->workDispatcher = $workDispatcher;
    }

    /**
     * @param Orga_Model_Organization $organization
     */
    public function updateInconsistentInputsFromOrganization(Orga_Model_Organization $organization)
    {
        foreach ($organization->getInputGranularities() as $inputGranularity) {
            foreach ($inputGranularity->getCells() as $inputCell) {
                $this->updateInconsistentInputSetFromPreviousValue($inputCell);
            }
        }
    }

    /**
     * @param Orga_Model_Cell $cell
     */
    public function updateInconsistentInputSetFromPreviousValue(Orga_Model_Cell $cell)
    {
        $inputSet = $cell->getAFInputSetPrimary();
        if ($inputSet !== null) {
            // Saisie de l'année précédente
            $timeAxis = $cell->getGranularity()->getOrganization()->getTimeAxis();
            if ($timeAxis && $cell->getGranularity()->hasAxis($timeAxis)) {
                $previousCell = $cell->getPreviousCellForAxis($timeAxis);
                if ($previousCell) {
                    $previousInput = $previousCell->getAFInputSetPrimary();
                    if ($previousInput !== null) {
                        $inconsistencyFinder = new InputSetInconsistencyFinder($inputSet, $previousInput);
                        $cell->setNumberOfInconsistenciesInInputSet($inconsistencyFinder->run());
                        return;
                    }
                }
            }
        }

        $cell->setNumberOfInconsistenciesInInputSet(0);
    }

    /**
     * Modifie la saisie d'une cellule et recalcule les résultats si la saisie est complète
     *
     * @param Orga_Model_Cell $cell
     * @param PrimaryInputSet $newValues Nouvelles valeurs pour les saisies
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
            $cell->updateInputStatus();

            $event = new Orga_Service_InputEditedEvent($cell);
        } else {
            // Création de la saisie
            $inputSet = $newValues;

            // Sauvegarde et attache à la cellule
            $inputSet->save();
            $cell->setAFInputSetPrimary($inputSet);
            $cell->updateInputStatus();
            $this->afInputService->updateResults($inputSet);

            $event = new Orga_Service_InputCreatedEvent($cell);
        }

        // Lance l'évènement
        $this->eventDispatcher->dispatch($event::NAME, $event);

        // Vérification des valeurs précédente.
        $this->updateInconsistentInputSetFromPreviousValue($cell);
        // Mise à jour des valeurs suivantes.
        $timeAxis = $cell->getGranularity()->getOrganization()->getTimeAxis();
        if ($timeAxis && $cell->getGranularity()->hasAxis($timeAxis)) {
            $nextCell = $cell->getNextCellForAxis($timeAxis);
            if ($nextCell) {
                $this->updateInconsistentInputSetFromPreviousValue($nextCell);
            }
        }

        // Regénère DW
        $this->workDispatcher->run(
            new ServiceCallTask('Orga_Service_ETLData', 'clearDWResultsFromCell', [$cell])
        );
        if ($inputSet->isInputComplete()) {
            $this->workDispatcher->run(
                new ServiceCallTask('Orga_Service_ETLData', 'populateDWResultsFromCell', [$cell])
            );
        }
        // Regénère l'exports de la cellule.
        $this->workDispatcher->run(
            new ServiceCallTask('Orga_Service_Export', 'saveCellInput', [$cell])
        );
    }

    /**
     * Met à jour les résultats d'une saisie
     *
     * @param Orga_Model_Cell $cell
     * @param PrimaryInputSet $inputSet
     * @param AF|null         $af Permet d'uiliser un AF différent de celui de la saisie
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
