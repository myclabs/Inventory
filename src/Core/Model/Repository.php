<?php
/**
 * @author     valentin.claras
 * @package    Core
 * @subpackage Model
 */

/**
 * Repository class
 *
 * @package    Core
 * @subpackage Model
 */
class Core_Model_Repository extends Doctrine\ORM\EntityRepository
{

    /**
     * Charge une entité en fonction de son id.
     *
     * @param array|mixed $id
     *
     * @throws Core_Exception_NotFound
     *
     * @return Core_Model_Entity
     */
    public function load($id)
    {
        $entityName = $this->getEntityName();

        $entity = $this->find($id);
        if (empty($entity)) {
            // Nécessaire pour contourner un problème de récursivité lorsque la clé contient un objet.
            ob_start();
            var_dump($id);
            $exportedId = ob_get_clean();
            throw new Core_Exception_NotFound('No "' . $entityName . '" matching key ' . $exportedId);
        }

        return $entity;
    }

    /**
     * Charge une entité en fonction de ses attributs.
     *
     * @param array $criteria
     *
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     *
     * @return Core_Model_Entity
     */
    public function loadBy(array $criteria)
    {
        $entityName = $this->getEntityName();

        $entities = $this->findBy($criteria);
        if (empty($entities)) {
            throw new Core_Exception_NotFound("No '$entityName' matching " . $this->criteriaToString($criteria));
        } else {
            if (count($entities) > 1) {
                throw new Core_Exception_TooMany("Too many '$entityName' matching "
                                                     . $this->criteriaToString($criteria));
            }
        }

        return $entities[0];
    }

    /**
     * Charge la liste des objets de la classe associée.
     *
     * @param Core_Model_Query $queryParameters Paramètres de la requête
     *
     * @return Core_Model_Entity[]
     */
    public function loadList(Core_Model_Query $queryParameters)
    {
        $entityName = $this->getEntityName();
        $entityAlias = $entityName::getAlias();

        $queryBuilderLoadList = $this->createQueryBuilder($entityAlias);
        $queryParameters->rootAlias = $entityAlias;
        $queryParameters->entityName = $entityName;
        $this->addCustomParametersToQueryBuilder($queryBuilderLoadList, $queryParameters);

        return $queryParameters->getQuery($queryBuilderLoadList)->getResult();
    }

    /**
     * Renvoie le nombre d'éléments total que le loadList peut charger.
     *
     * @param Core_Model_Query $queryParameters Paramètres de la requête
     *
     * @return int
     */
    public function countTotal(Core_Model_Query $queryParameters = null)
    {
        $entityName = $this->getEntityName();
        $entityAlias = $entityName::getAlias();

        $queryBuilderCountTotal = $this->createQueryBuilder($entityAlias);
        $queryBuilderCountTotal->select($queryBuilderCountTotal->expr()->count($entityAlias));
        $queryParameters->rootAlias = $entityAlias;
        $queryParameters->entityName = $entityName;
        $this->addCustomParametersToQueryBuilder($queryBuilderCountTotal, $queryParameters);

        return $queryParameters->getQueryWithoutLimit($queryBuilderCountTotal)->getSingleScalarResult();
    }

    /**
     * Ajoute des paramètres personnalisés au QueryBuilder utilisé par le loadList et le countTotal.
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param Core_Model_Query $queryParameters
     */
    protected function addCustomParametersToQueryBuilder($queryBuilder, Core_Model_Query $queryParameters=null)
    {
        // Nothing added by default !
    }

    /**
     * Exporte un tableau de criteria en chaine de caractère
     * @param array $criteria
     * @return string
     */
    private function criteriaToString(array $criteria)
    {
        $tmp = [];
        foreach ($criteria as $key => $value) {
            $tmp[] = "$key == $value";
        }
        return '(' . implode(' && ', $tmp) . ')';
    }

}
