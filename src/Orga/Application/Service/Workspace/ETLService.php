<?php

namespace Orga\Application\Service\Workspace;

use Core\Work\ServiceCall\ServiceCallTask;
use Mnapoli\Translated\Translator;
use MyCLabs\Work\Dispatcher\SynchronousWorkDispatcher;
use Orga\Domain\Service\ETL\ETLDataInterface;
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
        $this->workDispatcher->run(
            new ServiceCallTask(
                ETLService::class,
                'resetGranularitiesDWCubes',
                [$workspace],
                __(
                    'Orga', 'backgroundTasks', 'resetGranularitiesDWCubes',
                    [
                        'WORKSPACE' => $this->translator->get($workspace->getLabel())
                    ]
                )
            )
        );
        $granularity = $workspace->getGranularityByRef('global');
        $cell = $granularity->getCellByMembers([]);
        $this->resetCellAndChildrenDWCubes($cell);
    }

    /**
     * @param Workspace $workspace
     */
    public function resetGranularitiesDWCubes(Workspace $workspace)
    {
        foreach ($workspace->getDWGranularities() as $granularity) {
            $this->etlStructureService->resetGranularityDWCube(Granularity::load($granularity->getId()));
        }
    }

    /**
     * @param Cell $cell
     */
    public function resetCellAndChildrenDWCubes(Cell $cell)
    {
        // Lance une tâche pour la cellule courante.
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

        $cell = Cell::load($cell->getId());
        $narrowerGranularities = $cell->getGranularity()->getNarrowerGranularities();
        // Lance une tâche par granularité plus fine.
        foreach ($narrowerGranularities as $narrowerGranularity) {
            if ($narrowerGranularity->getCellsGenerateDWCubes()) {
                $cell = Cell::load($cell->getId());
                $narrowerGranularity = Granularity::load($narrowerGranularity->getId());
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
            $this->etlStructureService->resetCellDWCube(Cell::load($childCell->getId()));
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
                ETLDataInterface::class,
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
