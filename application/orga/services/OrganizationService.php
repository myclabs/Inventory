<?php
/**
 * @author  matthieu.napoli
 * @package Orga
 * @subpackage Service
 */

use Doctrine\ORM\EntityManager;

/**
 * @package Orga
 * @subpackage Service
 */
class Orga_Service_OrganizationService extends Core_Singleton
{

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Orga_Service_ACLManager
     */
    private $aclManager;

    /**
     * @param EntityManager           $entityManager
     * @param Orga_Service_ACLManager $aclManager
     */
    public function __construct(EntityManager $entityManager, Orga_Service_ACLManager $aclManager)
    {
        $this->entityManager = $entityManager;
        $this->aclManager = $aclManager;
    }

    /**
     * Crée un projet et assigne un utilisateur comme administrateur
     *
     * @param User_Model_User $administrator
     * @param string $label
     * @throws Exception
     * @return Orga_Model_Organization
     */
    public function createOrganization(User_Model_User $administrator, $label)
    {
        $this->entityManager->beginTransaction();

        try {
            $organization = new Orga_Model_Organization();
            $organization->setLabel($label);
            $defaultGranularity = new Orga_Model_Granularity($organization);
            $organization->save();
            $this->entityManager->flush();

            $this->aclManager->addOrganizationAdministrator($organization, $administrator);
            $this->entityManager->flush();

            $this->entityManager->commit();

            return $organization;
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();

            throw $e;
        }
    }

    /**
     * Supprime un projet
     *
     * @param Orga_Model_Organization $organization
     */
    public function deleteOrganization(Orga_Model_Organization $organization)
    {
        $organization->setGranularityForInventoryStatus(null);

        $organization->delete();
    }

}
