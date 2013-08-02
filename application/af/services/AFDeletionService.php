<?php

use DI\Annotation\Inject;

/**
 * Service de suppression d'un AF
 * @author  matthieu.napoli
 */
class AF_Service_AFDeletionService
{

    /**
     * @Inject
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Supprime un AF.
     *
     * Nécessaire car beaucoup trop de relations entre les objets d'un AF pour utiliser simplement les cascades.
     *
     * @param AF_Model_AF $af
     */
    public function deleteAF(AF_Model_AF $af)
    {
        $this->deleteGroup($af->getRootGroup());

        $af->delete();
    }

    private function deleteGroup(AF_Model_Component_Group $group)
    {
//        foreach ($group->getSubComponents() as $subComponent) {
//            if ($subComponent instanceof AF_Model_Component_Group) {
//                $this->deleteGroup($subComponent);
//            } else {
//                $subComponent->delete();
//                $group->removeSubComponent($subComponent);
//            }
//        }

        $group->delete();
    }

}
