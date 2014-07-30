<?php

namespace Orga\Architecture\Repository;

use AF\Domain\InputSet\PrimaryInputSet;
use Core_Model_Query;
use Core_Model_Repository;
use Doctrine\ORM\QueryBuilder;
use Orga\Domain\Cell;
use Orga\Domain\Cell\CellInputComment;
use Orga\Domain\Granularity;
use Orga\Domain\Member;
use Orga\Domain\Workspace;
use DateTime;

/**
 * Gère les Cell.
 * @package    Orga
 * @subpackage Repository
 */
class CellRepository extends Core_Model_Repository
{
    /**
     * Ajoute des paramètres personnalisés au QueryBuilder utilisé par le loadList et le countTotal.
     *
     * @param QueryBuilder $queryBuilder
     * @param Core_Model_Query|null $queryParameters
     */
    protected function addCustomParametersToQueryBuilder(
        QueryBuilder $queryBuilder,
        Core_Model_Query $queryParameters = null
    ) {
        if ($queryParameters === null) {
            return;
        }

        $arrayAuthorisedAFQuery = array(
            PrimaryInputSet::getAlias() . PrimaryInputSet::QUERY_COMPLETION,
        );

        $needsJoinToAF = false;
        foreach ($queryParameters->filter->getConditions() as $filterConditionArray) {
            if (in_array($filterConditionArray['alias'] . $filterConditionArray['name'], $arrayAuthorisedAFQuery)) {
                $needsJoinToAF = true;
            }
            if ($needsJoinToAF) {
                break;
            }
        }
        foreach ($queryParameters->order->getOrders() as $orderArray) {
            if (in_array($orderArray['alias'] . $orderArray['name'], $arrayAuthorisedAFQuery)) {
                $needsJoinToAF = true;
            }
            if ($needsJoinToAF) {
                break;
            }
        }

        if ($needsJoinToAF) {
            $queryBuilder->leftJoin(
                Cell::getAlias() . '.aFInputSetPrimary',
                PrimaryInputSet::getAlias()
            );
        }
    }

    /**
     * Charge la liste des Cell possédant l'exact liste des membres données.
     *
     * @param array $arrayMembers Tableau des membres indxés par la granularité.
     * @param Core_Model_Query $queryParameters Paramètres de la requête.
     *
     * @return Cell[]
     */
    public function loadByMembers($arrayMembers, Core_Model_Query $queryParameters)
    {
        $entityName = $this->getEntityName();
        $cellAlias = $entityName::getAlias();

        $queryBuilder = $this->createQueryBuilder($cellAlias);
        $queryBuilder->distinct();
        $queryParameters->rootAlias = $cellAlias;
        $this->addCustomParametersToQueryBuilder($queryBuilder, $queryParameters);
        $queryParameters->parseToQueryBuilderWithLimit($queryBuilder);
        $this->addMembersFiltersToQueryBuilder($queryParameters, $queryBuilder);
        $this->parseArrayMembersToQueryBuilder($arrayMembers, $queryBuilder);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Compte le nombre de Cell possédant l'exact liste des membres données.
     *
     * @param array $arrayMembers Tableau des membres indxés par la granularité.
     * @param Core_Model_Query $queryParameters Paramètres de la requête.
     *
     * @return Cell[]
     */
    public function countTotalByMembers($arrayMembers, Core_Model_Query $queryParameters)
    {
        $entityName = $this->getEntityName();
        $cellAlias = $entityName::getAlias();

        $queryBuilder = $this->createQueryBuilder($cellAlias);
        $queryBuilder->select($queryBuilder->expr()->countDistinct($cellAlias));
        $queryParameters->rootAlias = $cellAlias;
        $this->addCustomParametersToQueryBuilder($queryBuilder, $queryParameters);
        $queryParameters->parseToQueryBuilderWithoutLimit($queryBuilder);
        $this->addMembersFiltersToQueryBuilder($queryParameters, $queryBuilder);
        $this->parseArrayMembersToQueryBuilder($arrayMembers, $queryBuilder);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Core_Model_Query $queryParameters
     * @param QueryBuilder $queryBuilder
     */
    protected function addMembersFiltersToQueryBuilder(Core_Model_Query $queryParameters, QueryBuilder $queryBuilder)
    {
        foreach ($queryParameters->getCustomParameters() as $memberFilter) {
            $queryBuilder->leftJoin(
                Cell::getAlias() . '.members',
                Member::getAlias() . $memberFilter['name']
            );
            if ((is_array($memberFilter['value'])) || (strpos($memberFilter['value'], ','))) {
                $orMembers = $queryBuilder->expr()->orX();
                if (strpos($memberFilter['value'], ',')) {
                    $memberFilter['value'] = explode(',', $memberFilter['value']);
                }
                foreach ($memberFilter['value'] as $indexValue => $memberFilterValue) {
                    $orMembers->add(
                        $queryBuilder->expr()->eq(
                            Member::getAlias() . $memberFilter['name'] . '.' . Member::QUERY_REF,
                            ':members_' . $memberFilter['name'] . '_' . $indexValue
                        )
                    );
                    $queryBuilder->setParameter(
                        'members_' . $memberFilter['name'] . '_' . $indexValue,
                        explode(Member::COMPLETEREF_JOIN, $memberFilterValue)[0]
                    );
                }
                $queryBuilder->andWhere($orMembers);
            } else {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->eq(
                        Member::getAlias() . $memberFilter['name'] . '.' . Member::QUERY_REF,
                        ':members_' . $memberFilter['name']
                    )
                );
                $queryBuilder->setParameter(
                    'members_' . $memberFilter['name'],
                    explode(Member::COMPLETEREF_JOIN, $memberFilter['value'])[0]
                );
            }
        }
    }

    /**
     * Ajoute les membres du tableau au queryBuilder.
     *
     * @param array $arrayMembers
     * @param QueryBuilder $queryBuilder
     */
    protected function parseArrayMembersToQueryBuilder($arrayMembers, QueryBuilder $queryBuilder)
    {
        $queryBuilder->leftJoin(
            Cell::getAlias() . '.members',
            Member::getAlias()
        );

        $orExpression = $queryBuilder->expr()->orx();
        foreach ($arrayMembers as $granularityIndex => $granularityMembers) {
            $granularityMembersExpression = $this->getGranularityMembersNeededExpression(
                $granularityIndex,
                $granularityMembers,
                $queryBuilder
            );
            $orExpression->add($granularityMembersExpression['expression']);
            foreach ($granularityMembersExpression['parameters'] as $indexParameter => $parameter) {
                $queryBuilder->setParameter($indexParameter, $parameter);
            }
        }

        if ($orExpression->count() > 0) {
            $queryBuilder->andWhere($orExpression);
        }
    }

    /**
     * Renvoie l'expression des cellules d'une granularité données possédant les membres souhaités.
     *  Ajoute les paramètres au queryBuilder principal.
     *
     * @param int $granularityIndex
     * @param array $granularityMembers
     * @param QueryBuilder $queryBuilder
     *
     * @return array('expression' => \Doctrine\ORM\Query\Expr\Andx, 'parameters' => array())
     */
    protected function getGranularityMembersNeededExpression(
        $granularityIndex,
        $granularityMembers,
        QueryBuilder $queryBuilder
    ) {
        $cellAlias = Cell::getAlias();

        $granularityMembersExpression = $queryBuilder->expr()->andX();
        $granularityMembersExpression->add(
            $queryBuilder->expr()->eq(
                $cellAlias . '.' . Cell::QUERY_GRANULARITY,
                ':granularity' . $granularityIndex
            )
        );

        $parameters = array();
        if (!empty($granularityMembers['members'])) {
            foreach ($granularityMembers['members'] as $axisRef => $axisMembers) {
                if (empty($axisMembers)) {
                    continue;
                }
                $subCellAlias = $cellAlias . '_' . $granularityIndex . '_' . $axisRef;
                $subMembersAlias = Member::getAlias() . '_' . $granularityIndex . '_' . $axisRef;
                $selectMembers = $this->_em->createQueryBuilder()->select($subCellAlias);
                $selectMembers->from(Cell::class, $subCellAlias);
                $selectMembers->distinct();
                $selectMembers->leftJoin($subCellAlias . '.members', $subMembersAlias);
                $selectMembers->where(
                    $selectMembers->expr()->in(
                        $subMembersAlias,
                        ':members_' . $subMembersAlias
                    )
                );
                $parameters['members_' . $subMembersAlias] = $axisMembers;
                $granularityMembersExpression->add(
                    $queryBuilder->expr()->in(
                        Cell::getAlias(),
                        $selectMembers->getDQL()
                    )
                );
            }
        }
        $parameters['granularity' . $granularityIndex] = $granularityMembers['granularity'];

        return array('expression' => $granularityMembersExpression, 'parameters' => $parameters);
    }

    /**
     * Ajoute au $queryBuilder les conditions pour récupérer les cellles enfantes de la cellule donnée.
     *
     * @param QueryBuilder $qb
     * @param Cell $cell
     * @param string $cellAlias
     */
    public function addCellAndChildrenConditionsToQueryBuilder(QueryBuilder $qb, Cell $cell, $cellAlias)
    {
        foreach (explode(Workspace::PATH_JOIN, $cell->getTag()) as $ci => $pathTag) {
            $qb->andWhere(
                $qb->expr()->like($cellAlias . '.tag', ':cPathTag_' . $ci)
            );
            $qb->setParameter('cPathTag_' . $ci, '%' . $pathTag . '%');
        }

        $qbGranularities = $this->getEntityManager()->createQueryBuilder();
        $qbGranularities->select('granularities.id');
        $qbGranularities->from(Granularity::class, 'granularities');

        $qbGranularities->where(
            $qbGranularities->expr()->eq('granularities.workspace', ':workspace')
        );
        $qb->setParameter('workspace', $cell->getWorkspace());

        foreach (explode(Workspace::PATH_JOIN, $cell->getGranularity()->getTag()) as $gi => $pathTag) {
            $qbGranularities->andWhere(
                $qbGranularities->expr()->like('granularities.tag', ':gPathTag_' . $gi)
            );
            $qb->setParameter('gPathTag_' . $gi, '%' . $pathTag . '%');
        }

        $qb->andWhere(
            $qb->expr()->in($cellAlias . '.granularity', $qbGranularities->getDQL())
        );
    }

    /**
     * Retourne les derniers commentaires pour la cellule et ses sous-cellules.
     *
     * @param Cell $cell
     * @param int $count
     *
     * @return CellInputComment[]
     */
    public function getLatestComments(Cell $cell, $count)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('comment')
            ->from(CellInputComment::class, 'comment')
            ->join('comment.cell', 'cell')
            ->orderBy('comment.creationDate', 'DESC');
        $qb->setMaxResults($count);

        $this->addCellAndChildrenConditionsToQueryBuilder($qb, $cell, 'cell');

        $comments = $qb->getQuery()->getResult();

        return $comments;
    }

    /**
     * Retourne les derniers commentaires pour la cellule et ses sous-cellules.
     *
     * @param Cell $cell
     * @param DateTime $upTo
     * @param DateTime $from
     *
     * @return CellInputComment[]
     */
    public function getUpToComments(Cell $cell, DateTime $upTo, DateTime $from = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('comment')
            ->from(CellInputComment::class, 'comment')
            ->join('comment.cell', 'cell')
            ->orderBy('comment.creationDate', 'DESC')
            ->where($qb->expr()->gte('comment.creationDate', ':upTo'));
        $qb->setParameter('upTo', $upTo);
        if (($from !== null) && ($upTo < $from)) {
            $qb->andWhere($qb->expr()->lte('comment.creationDate', ':from'));
            $qb->setParameter('from', $from);
        }

        $this->addCellAndChildrenConditionsToQueryBuilder($qb, $cell, 'cell');

        $comments = $qb->getQuery()->getResult();

        return $comments;
    }

    /**
     * Retourne les derniers commentaires pour la cellule et ses sous-cellules.
     *
     * @param Cell $cell
     * @param DateTime $from
     *
     * @return CellInputComment[]
     */
    public function hasFromComments(Cell $cell, DateTime $from = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select($qb->expr()->count('comment'))
            ->from(CellInputComment::class, 'comment')
            ->join('comment.cell', 'cell')
            ->orderBy('comment.creationDate', 'DESC')
            ->where($qb->expr()->lte('comment.creationDate', ':from'))
            ->setParameter('from', $from);

        $this->addCellAndChildrenConditionsToQueryBuilder($qb, $cell, 'cell');

        return ($qb->getQuery()->getSingleScalarResult() > 0);
    }
}
