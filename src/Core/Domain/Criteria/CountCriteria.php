<?php

namespace Core\Domain\Criteria;

/**
 * Criteria on the number of results
 *
 * @author matthieu.napoli
 */
trait CountCriteria
{
    /**
     * @var int|null
     */
    private $firstResult;

    /**
     * @var int|null
     */
    private $maxResults;

    /**
     * Gets the current first result option of this Criteria.
     *
     * @return int|null
     */
    public function getFirstResult()
    {
        return $this->firstResult;
    }

    /**
     * Set the number of first result that this Criteria should return.
     *
     * @param int|null $firstResult The value to set.
     *
     * @return $this
     */
    public function setFirstResult($firstResult)
    {
        $this->firstResult = $firstResult;

        return $this;
    }

    /**
     * Gets maxResults.
     *
     * @return int|null
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * Sets maxResults.
     *
     * @param int|null $maxResults The value to set.
     *
     * @return $this
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;

        return $this;
    }
}
