<?php
/**
 * @author  matthieu.napoli
 */

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Service responsable de la gestion des saisies
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

        // Modification de la saisie
        if ($inputSet) {
            $this->afInputService->editInputSet($inputSet, $newValues);

            // Lance l'évènement
            $event = new Orga_Service_InputEditedEvent($cell);
            $this->eventDispatcher->dispatch($event::NAME, $event);
        }

        // Création de la saisie
        if (!$inputSet) {
            $inputSet = $newValues;

            // Sauvegarde et attache à la cellule
            $inputSet->save();
            $cell->setAFInputSetPrimary($inputSet);
            $this->afInputService->updateResults($inputSet);

            // Lance l'évènement
            $event = new Orga_Service_InputCreatedEvent($cell);
            $this->eventDispatcher->dispatch($event::NAME, $event);
        }


        $this->workDispatcher->runBackground(
            new Core_Work_ServiceCall_Task('Orga_Service_ETLData', 'clearDWResultsFromCell', [$cell])
        );
        $this->etlDataService->clearDWResultsFromCell($cell);
        if ($inputSet->isInputComplete()) {
            $this->workDispatcher->runBackground(
                new Core_Work_ServiceCall_Task('Orga_Service_ETLData', 'populateDWResultsFromCell', [$cell])
            );
        }
    }
}
