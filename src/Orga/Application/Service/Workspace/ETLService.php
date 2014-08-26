<?php

namespace Orga\Application\Service\Workspace;

use Core\Work\ServiceCall\ServiceCallTask;
use Mnapoli\Translated\Translator;
use MyCLabs\Work\Dispatcher\SynchronousWorkDispatcher;
use Orga\Domain\Service\ETL\ETLDataService;
use Orga\Domain\Service\ETL\ETLStructureInterface;
use Orga\Domain\Workspace;
use Orga\Domain\Granularity;
use Orga\Domain\Cell;

/**
 * ETLService
 *
 * @author valentin.claras
 */
class ETLService
{

    /**
     * @var ETLStructureInterface
     */
    private $etlStructureService;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var SynchronousWorkDispatcher
     */
    private $workDispatcher;


    /**
     * @param ETLStructureInterface $etlStructureService
     * @param Translator $translator
     * @param SynchronousWorkDispatcher $workDispatcher
     */
    public function __construct(
        ETLStructureInterface $etlStructureService,
        Translator $translator,
        SynchronousWorkDispatcher $workDispatcher
    ) {
        $this->etlStructureService = $etlStructureService;
        $this->translator = $translator;
        $this->workDispatcher = $workDispatcher;
    }

    /**
     * @param Workspace $workspace
     */
    public function resetWorkspaceDWCubes(Workspace $workspace)
    {
        foreach ($workspace->getOrderedGranularities() as $granularity) {
            if ($granularity->getCellsGenerateDWCubes()) {
                // Lance la tache en arrière plan
                $this->workDispatcher->run(
                    new ServiceCallTask(
                        ETLService::class,
                        'resetGranularityAndCellsDWCubes',
                        [$granularity],
                        __(
                            'Orga', 'backgroundTasks', 'resetGranularityAndCellsDWCubes',
                            [
                                'GRANULARITY' => $this->translator->get($granularity->getLabel())
                            ]
                        )
                    )
                );
            }
        }
    }

    /**
     * @param Granularity $granularity
     */
    public function resetGranularityAndCellsDWCubes(Granularity $granularity)
    {
        foreach ($granularity->getCells() as $cell) {
            $this->etlStructureService->resetCellDWCube($cell);
        }

        $this->etlStructureService->resetGranularityDWCube($granularity);
    }

    /**
     * @param Cell $cell
     */
    public function resetCellAndChildrenDWCubes(Cell $cell)
    {
        $this->workDispatcher->run(
            new ServiceCallTask(
                ETLStructureInterface::class,
                'resetCellDWCube',
                [$cell],
                __(
                    'Orga', 'backgroundTasks', 'resetCellDWCube',
                    [
                        'CELL' => $this->translator->get($cell->getExtendedLabel())
                    ]
                )
            )
        );

        // Lance une tâche par granularité plus fine.
        foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
            if ($narrowerGranularity->getCellsGenerateDWCubes()) {
                $this->workDispatcher->run(
                    new ServiceCallTask(
                        ETLService::class,
                        'resetCellChidrenDWCubesForGranularity',
                        [$cell, $narrowerGranularity],
                        __(
                            'Orga', 'backgroundTasks', 'resetCellChidrenDWCubesForGranularity',
                            [
                                'CELL' => $this->translator->get($cell->getExtendedLabel()),
                                'GRANULARITY' => $this->translator->get($narrowerGranularity->getLabel())
                            ]
                        )
                    )
                );
            }
        }
    }

    /**
     * @param Cell $cell
     * @param Granularity $granularity
     */
    public function resetCellChidrenDWCubesForGranularity(Cell $cell, Granularity $granularity)
    {
        foreach ($cell->getChildCellsForGranularity($granularity) as $childCell) {
            $this->etlStructureService->resetCellDWCube($childCell);
        }
    }

    /**
     * @param Cell $cell
     */
    public function resetCellAndChildrenCalculationsAndDWCubes(Cell $cell)
    {
        // Tâche du recalculs.resetDWCellAndResults
        $this->workDispatcher->run(
            new ServiceCallTask(
                ETLDataService::class,
                'calculateResultsForCellAndChildren',
                [$cell],
                __(
                    'Orga', 'backgroundTasks', 'calculateResultsForCellAndChildren',
                    [
                        'CELL' => $this->translator->get($cell->getExtendedLabel())
                    ]
                )
            )
        );

        // Puis lance un rebuild des DW.
        $this->resetCellAndChildrenDWCubes($cell);
    }
}
