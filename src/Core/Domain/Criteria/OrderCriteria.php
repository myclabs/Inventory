<?php

namespace Core\Domain\Criteria;

/**
 * Criteria on the order
 *
 * @author matthieu.napoli
 */
trait OrderCriteria
{
    /**
     * @var array
     */
    private $orderings = [];

    /**
     * Gets the current orderings of this Criteria.
     *
     * @return array
     */
    public function getOrderings()
    {
        return $this->orderings;
    }

    /**
     * Sets the ordering of the result of this Criteria.
     *
     * Keys are field and values are the order, being either ASC or DESC.
     *
     * @see Criteria::ASC
     * @see Criteria::DESC
     *
     * @param array $orderings
     *
     * @return $this
     */
    public function orderBy(array $orderings)
    {
        $this->orderings = $orderings;

        return $this;
    }
}
