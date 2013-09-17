<?php

namespace Core\Domain;

use Doctrine\ORM as Doctrine;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Implementation of a repository using Doctrine
 *
 * @author matthieu.napoli
 * @author valentin.claras
 */
class DoctrineEntityRepository extends Doctrine\EntityRepository implements EntityRepository
{
    /**
     * {@inheritdoc}
     */
    public function add($entity)
    {
        $this->getEntityManager()->persist($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($entity)
    {
        $this->getEntityManager()->remove($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->createQueryBuilder('e')
            ->select('count(e)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $entity = $this->find($id);

        if (empty($entity)) {
            // Nécessaire pour contourner un problème de récursivité lorsque la clé contient un objet.
            ob_start();
            var_dump($id);
            $exportedId = ob_get_clean();
            throw new \Core_Exception_NotFound('No "' . $this->getEntityName() . '" matching key ' . $exportedId);
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getBy(array $criteria)
    {
        $entities = $this->findBy($criteria);

        if (empty($entities)) {
            $criteriaAsString = $this->criteriaToString($criteria);
            throw new \Core_Exception_NotFound('No "' . $this->getEntityName() . '" matching ' . $criteriaAsString);
        } elseif (count($entities) > 1) {
            $criteriaAsString = $this->criteriaToString($criteria);
            throw new \Core_Exception_TooMany('Too many "' . $this->getEntityName() . '" matching ' . $criteriaAsString);
        }

        return $entities[0];
    }


    /**
    /**
     * Selects all elements from a selectable that match the expression and
     * returns a new collection containing these elements.
     *
     * @param Criteria $criteria
     *
     * @return Paginator
     */
    public function matching(Criteria $criteria)
    {
        $queryBuilder = $this->createQueryBuilder('this');
        $queryBuilder->addCriteria($criteria);

        return new Paginator($queryBuilder, false);
    }

    /**
     * Exporte un tableau de criteria en chaine de caractère
     * @param array $criteria
     * @return string
     */
    protected function criteriaToString(array $criteria)
    {
        $tmp = [];
        foreach ($criteria as $key => $value) {
            $tmp[] = "$key == $value";
        }
        return '(' . implode(' && ', $tmp) . ')';
    }
}
