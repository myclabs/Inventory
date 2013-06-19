<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Domain\Context;

use Orga_Model_Cell;
use Orga_Model_Project;

/**
 * Contexte d'une organisation
 */
class OrganizationContext extends Context
{
    /**
     * @var Orga_Model_Project
     */
    private $organization;

    /**
     * @var Orga_Model_Cell|null
     */
    private $cell;

    /**
     * @param Orga_Model_Project $organization
     */
    public function __construct(Orga_Model_Project $organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return Orga_Model_Project
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param Orga_Model_Cell $cell
     */
    public function setCell(Orga_Model_Cell $cell)
    {
        $this->cell = $cell;
    }

    /**
     * @return Orga_Model_Cell|null
     */
    public function getCell()
    {
        return $this->cell;
    }
}
