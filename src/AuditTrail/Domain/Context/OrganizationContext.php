<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Domain\Context;

use Orga_Model_Project;

/**
 * Context global de l'application
 */
class OrganizationContext extends Context
{
    /**
     * @var Orga_Model_Project
     */
    private $organization;

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
}
