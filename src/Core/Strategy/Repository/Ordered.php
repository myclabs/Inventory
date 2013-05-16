<?php
/**
 * @author     valentin.claras
 * @package    Core
 * @subpackage Strategy
 */

/**
 * Repository class
 *
 * @package    Core
 * @subpackage Strategy
 */
trait Core_Strategy_Repository_Ordered
{

    /**
     * Renvoi la dernière position utilisée.
     *
     * @param array $context
     *
     * @return int
     */
    public function getLastPositionByContext($context=array())
    {
        $entityName = $this->getEntityName();
        $alias = $entityName::getAlias();
        $positionAttribute = $alias.'.position';

        $queryBuilderLastPosition = $this->createQueryBuilder($alias);
        $queryBuilderLastPosition->select($positionAttribute);
        $queryBuilderLastPosition->setMaxResults(1);
        $queryBuilderLastPosition->addOrderBy($positionAttribute, Core_Model_Order::ORDER_DESC);
        $this->addCustomParametersToQueryBuilder($queryBuilderLastPosition);

        // Permet de gérer le cas ou un context n'as pas encore été flushé.
        try {
            foreach ($context as $attribute => $value) {
                if ($value instanceof Core_Model_Entity) {
                    $entityKey = $value->getKey();
                    if (empty($entityKey)) {
                        throw new Core_Exception_Database('Context not flushed yet.');
                    }
                }

                if ($value === null) {
                    $condition = $queryBuilderLastPosition->expr()->isNull($alias.'.'.$attribute);
                } else {
                    $condition = $queryBuilderLastPosition->expr()->eq($alias.'.'.$attribute, ':context'.$attribute);
                    $queryBuilderLastPosition->setParameter('context'.$attribute, $value);
                }
                $queryBuilderLastPosition->andWhere($condition);
            }

            $entities = $queryBuilderLastPosition->getQuery()->getResult();
        } catch (Exception $e) {
            $entities = null;
        }
        if (empty($entities)) {
            return 0;
        } else {
            return $entities[0]['position'];
        }
    }

}
