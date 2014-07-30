<?php

namespace Orga\Domain\Service\Cell\Input;

use Orga\Domain\Workspace;
use Orga\Domain\Cell;
use AF\Domain\InputSet\PrimaryInputSet;
use AF\Domain\AF;

/**
 * CellInputUpdaterInterface
 *
 * @author valentin.claras
 */
interface CellInputUpdaterInterface
{
    /**
     * @param Workspace $workspace
     */
    public function updateInconsistencyForWorkspace(Workspace $workspace);

    /**
     * @param Cell $cell
     */
    public function updateInconsistencyForCell(Cell $cell);

    /**
     * @param Cell $cell
     * @param PrimaryInputSet $newValues Nouvelles valeurs pour les saisies
     */
    public function editInput(Cell $cell, PrimaryInputSet $newValues);

    /**
     * @param Cell $cell
     * @param PrimaryInputSet $inputSet
     * @param AF|null $af Permet d'uiliser un AF différent de celui de la saisie
     */
    public function updateResults(Cell $cell, PrimaryInputSet $inputSet, AF $af = null);
}
