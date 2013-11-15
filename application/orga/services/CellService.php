<?php
/**
 * @author  valentin.claras
 * @package Orga
 * @subpackage Service
 */

use Doctrine\ORM\EntityManager;

/**
 * @package Orga
 * @subpackage Service
 */
class Orga_Service_CellService
{

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Modifie la pertinence d'une cellule
     *
     * @param Orga_Model_Cell $cell
     * @param bool $relevance
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
