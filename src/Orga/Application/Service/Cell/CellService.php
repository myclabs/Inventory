<?php

namespace Orga\Application\Service\Cell;

use Doctrine\ORM\EntityManager;
use Exception;
use Orga\Domain\Cell;

/**
 * @author valentin.claras
 */
class CellService
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
     * @param Cell $cell
     * @param bool $relevance
     * @throws Exception
     */
    public function setCellRelevance(Cell $cell, $relevance)
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
