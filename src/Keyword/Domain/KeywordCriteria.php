<?php

namespace Keyword\Domain;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;

/**
 * Criteria for filtering keywords
 *
 * @author matthieu.napoli
 */
class KeywordCriteria extends Criteria
{
    /**
     * @var Expression|null
     */
    public $ref;

    /**
     * @var Expression|null
     */
    public $label;

    /**
     * @return Expression|null
     */
    public function getWhereExpression()
    {
        if ($this->ref) {
            $this->andWhere($this->ref);
        }
        if ($this->label) {
            $this->andWhere($this->label);
        }

        return parent::getWhereExpression();
    }
}
