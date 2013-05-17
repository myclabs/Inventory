<?php
/**
 * @author  matthieu.napoli
 * @package Inventory
 */

use Doctrine\ORM\EntityManager;

/**
 * @package Inventory
 */
class Inventory_Service_ProjectService extends Core_Singleton
{

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Inventory_Service_ACLManager
     */
    private $aclManager;

    /**
     * Constructeur
     */
    protected function __construct()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $this->entityManager = $entityManagers['default'];

        $this->aclManager = Inventory_Service_ACLManager::getInstance();
    }

    /**
     * Crée un projet et assigne un utilisateur comme administrateur
     *
     * @param User_Model_User $administrator
     * @param string          $label
     * @return Inventory_Model_Project
     */
    public function createProject(User_Model_User $administrator, $label)
    {
        $this->entityManager->beginTransaction();

        try {
            $project = new Inventory_Model_Project();
            $project->setLabel($label);
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
     * @param Inventory_Model_Project $project
     */
    public function deleteProject(Inventory_Model_Project $project)
    {
        foreach ($project->getAFGranularities() as $aFGranularities) {
            $project->deleteAFGranularities($aFGranularities);
        }
        $project->setOrgaGranularityForInventoryStatus(null);

        $project->delete();
    }

}
