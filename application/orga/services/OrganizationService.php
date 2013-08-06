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
class Orga_Service_OrganizationService
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
            // Création de l'organization.
            $organization = new Orga_Model_Organization();
            $organization->setLabel($label);
            // Création d'une granularité globale par défaut.
            $defaultGranularity = new Orga_Model_Granularity($organization);
            $defaultGranularity->setNavigability(true);
            $defaultGranularity->setCellsWithOrgaTab(true);
            $defaultGranularity->setCellsWithACL(true);
            $defaultGranularity->setCellsWithAFConfigTab(true);
            // Sauvegarde.
            $organization->save();
            $this->entityManager->flush();
            // Définition de la création des DW après pour éviter un bug d'insertion.
            $defaultGranularity->setCellsGenerateDWCubes(true);
            $organization->save();

            // Ajout de l'utilisateur courant en tant qu'administrateur.
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
