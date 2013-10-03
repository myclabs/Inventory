<?php

use Doctrine\ORM\EntityManager;

/**
 * Service de suppression d'un AF
 *
 * Ce service devrait à terme passer dans la couche architecture, dans le repository des AF (à créer)
 *
 * @author matthieu.napoli
 */
class AF_Service_AFDeletionService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Supprime un AF.
     *
     * Nécessaire car beaucoup trop de relations entre les objets d'un AF pour utiliser simplement les cascades.
     *
     * @param AF_Model_AF $af
     */
    public function deleteAF(AF_Model_AF $af)
    {
        $this->entityManager->beginTransaction();

        $this->deleteActionsAndConditions($af);
        $this->entityManager->flush();
        $this->entityManager->clear();
        $af = AF_Model_AF::load($af->getId());

        $this->deleteGroupContent($af->getRootGroup());
        $this->entityManager->flush();

        $this->deleteAlgos($af);
        $this->entityManager->flush();

        $this->entityManager->clear();
        $af = AF_Model_AF::load($af->getId());
        $af->delete();

        $this->entityManager->flush();
        $this->entityManager->flush();

        $this->entityManager->commit();
    }

    private function deleteActionsAndConditions(AF_Model_AF $af)
    {
        foreach ($af->getConditions() as $condition) {
            $condition->delete();
            $af->removeCondition($condition);
        }
        foreach ($af->getRootGroup()->getSubComponentsRecursive() as $subComponent) {
            $actions = $subComponent->getActions();
            foreach ($actions as $action) {
                $action->delete();
                $subComponent->removeAction($action);
            }
        }
    }

    private function deleteAlgos(AF_Model_AF $af)
    {
        $algoSet = $af->getMainAlgo()->getSet();

        foreach ($algoSet->getAlgos() as $algo) {
            if ($af->getMainAlgo() !== $algo) {
                $algo->delete();
            }
        }
    }

    private function deleteGroupContent(AF_Model_Component_Group $group)
    {
        foreach ($group->getSubComponents() as $subComponent) {
            if ($subComponent instanceof AF_Model_Component_Group) {
                $this->deleteGroupContent($subComponent);
                $subComponent->delete();
            } else {
                $subComponent->delete();
                $group->removeSubComponent($subComponent);
            }
        }
    }
}