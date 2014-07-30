<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Domain\Context;

use Orga\Domain\Cell;
use Orga\Domain\Workspace;

/**
 * Contexte d'une organisation
 */
class WorkspaceContext extends Context
{
    /**
     * @var \Orga\Domain\Workspace
     */
    private $workspace;

    /**
     * @var \Orga\Domain\Cell|null
     */
    private $cell;

    /**
     * @param \Orga\Domain\Workspace $workspace
     */
    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * @return \Orga\Domain\Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * @param \Orga\Domain\Cell $cell
     */
    public function setCell(Cell $cell)
    {
        $this->cell = $cell;
    }

    /**
     * @return \Orga\Domain\Cell|null
     */
    public function getCell()
    {
        return $this->cell;
    }
}
