<?php

namespace Keyword\Domain;

use Core\Criteria\FieldCriteria;
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
     * @var FieldCriteria
     */
    public $ref;

    /**
     * @var FieldCriteria
     */
    public $label;

    public function __construct()
    {
        $this->ref = new FieldCriteria('ref');
        $this->label = new FieldCriteria('label');
    }

    /**
     * @return Expression|null
     */
    public function getWhereExpression()
    {
        if ($this->ref->getExpression()) {
            $this->andWhere($this->ref->getExpression());
        }
        if ($this->label->getExpression()) {
            $this->andWhere($this->label->getExpression());
        }

        return parent::getWhereExpression();
    }
}
