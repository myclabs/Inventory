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
class Orga_Service_ProjectService extends Core_Singleton
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
     * Constructeur
     */
    protected function __construct()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $this->entityManager = $entityManagers['default'];

        $this->aclManager = Orga_Service_ACLManager::getInstance();
    }

    /**
     * Crée un projet et assigne un utilisateur comme administrateur
     *
     * @param User_Model_User $administrator
     * @param string $label
     * @throws Exception
     * @return Orga_Model_Project
     */
    public function createProject(User_Model_User $administrator, $label)
    {
        $this->entityManager->beginTransaction();

        try {
            $project = new Orga_Model_Project();
            $project->setLabel($label);
            $defaultGranularity = new Orga_Model_Granularity($project);
            $project->save();
            $this->entityManager->flush();

            $this->aclManager->addProjectAdministrator($project, $administrator);
            $this->entityManager->flush();

            $this->entityManager->commit();

            return $project;
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();

            throw $e;
        }
    }

    /**
     * Supprime un projet
     *
     * @param Orga_Model_Project $project
     */
    public function deleteProject(Orga_Model_Project $project)
    {
        $project->setGranularityForInventoryStatus(null);

        $project->delete();
    }

}
