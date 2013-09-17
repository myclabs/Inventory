<?php

namespace Keyword\Domain;

use Core\Criteria\FieldFilter;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;
use User_Model_Action;
use User_Model_User;

/**
 * Criteria for filtering keywords
 *
 * @author matthieu.napoli
 */
class KeywordCriteria extends Criteria
{
    /**
     * @var FieldFilter
     */
    public $ref;

    /**
     * @var FieldFilter
     */
    public $label;

    public function __construct()
    {
        $this->ref = new FieldFilter('ref');
        $this->label = new FieldFilter('label');
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
