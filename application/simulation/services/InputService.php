<?php
/**
 * @author  matthieu.napoli
 */

use AF\Domain\InputSet\PrimaryInputSet;
use Core\Work\ServiceCall\ServiceCallTask;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Service responsable de la gestion des saisies
 */
class Simulation_Service_InputService
{
    /**
     * @var AF_Service_InputService
     */
    private $afInputService;

    /**
     * @var Simulation_Service_ETLData
     */
    private $etlDataService;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var WorkDispatcher
     */
    private $workDispatcher;

    /**
     * @param AF_Service_InputService    $afInputService
     * @param Simulation_Service_ETLData $etlDataService
     * @param EventDispatcher            $eventDispatcher
     * @param WorkDispatcher             $workDispatcher
     */
    public function __construct(
        AF_Service_InputService $afInputService,
        Simulation_Service_ETLData $etlDataService,
        EventDispatcher $eventDispatcher,
        WorkDispatcher $workDispatcher
    ) {
        $this->afInputService = $afInputService;
        $this->etlDataService = $etlDataService;
        $this->eventDispatcher = $eventDispatcher;
        $this->workDispatcher = $workDispatcher;
    }

    /**
     * Modifie la saisie d'une cellule et recalcule les résultats si la saisie est complète
     *
     * @param Simulation_Model_Scenario $scenario
     * @param \AF\Domain\InputSet\PrimaryInputSet $newValues Nouvelles valeurs pour les saisies
     * @throws InvalidArgumentException
     */
    public function editInput(Simulation_Model_Scenario $scenario, PrimaryInputSet $newValues)
    {
        try {
            $inputSet = $scenario->getAFInputSetPrimary();
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
        }

        // Création de la saisie
        if (!$inputSet) {
            $inputSet = $newValues;

            // Sauvegarde et attache au scenario
            $inputSet->save();
            $scenario->setAFInputSetPrimary($inputSet);
            $this->afInputService->updateResults($inputSet);
        }


        $this->workDispatcher->runBackground(
            new ServiceCallTask('Simulation_Service_ETLData', 'clearDWResultsFromScenario', [$scenario])
        );
        if ($inputSet->isInputComplete()) {
            $this->workDispatcher->runBackground(
                new ServiceCallTask('Simulation_Service_ETLData', 'populateDWResultsFromScenario', [$scenario])
            );
        }
    }
}
