<?php
/**
 * @author     matthieu.napoli
 * @package    Social
 * @subpackage Model
 */

/**
 * @package    Social
 * @subpackage Repository
 */
class Social_Model_Repository_GenericAction extends Core_Model_Repository
{

    /**
     * Ajoute des paramètres personnalisés au QueryBuilder utilisé par le loadList et le countTotal.
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param Core_Model_Query           $queryParameters
     */
    protected function addCustomParametersToQueryBuilder($queryBuilder, Core_Model_Query $queryParameters = null)
    {
        if ($queryParameters === null) {
            return;
        }
        $queryBuilder->leftJoin(
            Social_Model_GenericAction::getAlias() . '.' . Social_Model_GenericAction::QUERY_THEME,
            Social_Model_Theme::getAlias()
        );
    }

}
