<?php

use Doctrine\ORM\EntityManager;

/**
 * @author valentin.claras
 */
class Orga_Service_CellService
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
     * Modifie la pertinence d'une cellule
     *
     * @param Orga_Model_Cell $cell
     * @param bool            $relevance
     * @throws Exception
     */
    public function setCellRelevance(Orga_Model_Cell $cell, $relevance)
    {
        $this->entityManager->beginTransaction();

        try {
            $cell->setRelevant($relevance);

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();

            throw $e;
        }
    }
}
