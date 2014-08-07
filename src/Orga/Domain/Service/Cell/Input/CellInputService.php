<?php

namespace Orga\Domain\Service\Cell\Input;

use AF\Domain\AF;
use AF\Domain\InputService;
use AF\Domain\InputService\InputSetInconsistencyFinder;
use AF\Domain\InputSet\PrimaryInputSet;
use Core\Work\ServiceCall\ServiceCallTask;
use InvalidArgumentException;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
use Orga\Domain\Cell;
use Orga\Domain\Service\Cell\Input\CellInputUpdaterInterface;
use Orga\Domain\Service\ETL\ETLDataService;
use Orga\Domain\Workspace;
use Orga\Domain\Service\Cell\Input\CellInputCreatedEvent;
use Orga\Domain\Service\Cell\Input\CellInputEditedEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Service responsable de la gestion des saisies
 *
 * @author matthieu.napoli
 */
class CellInputService implements CellInputUpdaterInterface
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
     * @param Workspace $workspace
     */
    public function updateInconsistencyForWorkspace(Workspace $workspace)
    {
        foreach ($workspace->getInputGranularities() as $inputGranularity) {
            foreach ($inputGranularity->getCells() as $inputCell) {
                $this->updateInconsistencyForCell($inputCell);
            }
        }
    }

    /**
     * @param Cell $cell
     */
    public function updateInconsistencyForCell(Cell $cell)
    {
        $inputSet = $cell->getAFInputSetPrimary();
        if ($inputSet !== null) {
            // Saisie de l'année précédente
            $previousInput = $cell->getPreviousAFInputSetPrimary();
            if ($previousInput !== null) {
                $inconsistencyFinder = new InputSetInconsistencyFinder($inputSet, $previousInput);
                $cell->setNumberOfInconsistenciesInInputSet($inconsistencyFinder->run());
                return;
            }
        }

        $cell->setNumberOfInconsistenciesInInputSet(0);
    }

    /**
     * Modifie la saisie d'une cellule et recalcule les résultats si la saisie est complète
     *
     * @param Cell $cell
     * @param PrimaryInputSet $newValues Nouvelles valeurs pour les saisies
     * @throws InvalidArgumentException
     */
    public function editInput(Cell $cell, PrimaryInputSet $newValues)
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

            $event = new CellInputEditedEvent($cell);
        } else {
            // Création de la saisie
            $inputSet = $newValues;

            // Sauvegarde et attache à la cellule
            $inputSet->save();
            $cell->setAFInputSetPrimary($inputSet);
            $this->afInputService->updateResults($inputSet);
            $cell->updateInputStatus();

            $event = new CellInputCreatedEvent($cell);
        }

        // Lance l'évènement
        $this->eventDispatcher->dispatch($event::NAME, $event);

        // Vérification des valeurs précédente.
        $this->updateInconsistencyForCell($cell);
        // Mise à jour des valeurs suivantes.
        $timeAxis = $cell->getGranularity()->getWorkspace()->getTimeAxis();
        if ($timeAxis && $cell->getGranularity()->hasAxis($timeAxis)) {
            $nextCell = $cell->getNextCellForAxis($timeAxis);
            if ($nextCell) {
                $this->updateInconsistencyForCell($nextCell);
            }
        }

        // Regénère DW
        $this->workDispatcher->run(
            new ServiceCallTask(ETLDataService::class, 'clearDWCubesFromCellDWResults', [$cell])
        );
        if ($inputSet->isInputComplete()) {
            $this->workDispatcher->run(
                new ServiceCallTask(ETLDataService::class, 'populateDWCubesWithCellInputResults', [$cell])
            );
        }
        // Regénère l'exports de la cellule.
        $this->workDispatcher->run(
            new ServiceCallTask('Export', 'saveCellInput', [$cell])
        );
    }

    /**
     * Met à jour les résultats d'une saisie
     *
     * @param Cell $cell
     * @param PrimaryInputSet $inputSet
     * @param AF|null $af Permet d'uiliser un AF différent de celui de la saisie
     */
    public function updateResults(Cell $cell, PrimaryInputSet $inputSet, AF $af = null)
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
