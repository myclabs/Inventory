<?php
/**
 * @author matthieu.napoli
 * @author valentin.claras
 */

namespace Core\Domain;

use Doctrine\ORM as Doctrine;

/**
 *  Entity repository interface
 */
class DoctrineEntityRepository extends Doctrine\EntityRepository implements EntityRepository
{
    /**
     * @param object $entity
     */
    public function add($entity)
    {
        $this->getEntityManager()->persist($entity);
    }

    /**
     * @param object $entity
     */
    public function remove($entity)
    {
        $this->getEntityManager()->remove($entity);
    }

    /**
     * Returns the number of elements in the repository.
     *
     * @return int
     */
    public function count()
    {
        return $this->createQueryBuilder('e')->select('count(e)')->getQuery()->getSingleScalarResult();
    }

    /**
     * @return object[]
     */
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * @param array|mixed $id
     * @throws \Core_Exception_NotFound
     * @return object
     */
    public function getOne($id)
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
     * @param array $criteria
     * @throws \Core_Exception_NotFound
     * @throws \Core_Exception_TooMany
     * @return object
     */
    public function getOneBy(array $criteria)
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
