<?php

namespace Keyword\Domain;

use Core\Criteria\FieldCriteria;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;
use User_Model_Action;
use User_Model_User;

/**
 * Criteria for filtering keywords associations
 *
 * @author matthieu.napoli
 */
class AssociationCriteria extends Criteria
{
    /**
     * @var FieldCriteria
     */
    public $subjectRef;

    /**
     * @var FieldCriteria
     */
    public $predicate;

    /**
     * @var FieldCriteria
     */
    public $objectRef;

    public function __construct()
    {
        $this->subjectRef = new FieldCriteria('subject.ref');
        $this->predicate = new FieldCriteria('predicate');
        $this->objectRef = new FieldCriteria('object.ref');
    }

    /**
     * @return Expression|null
     */
    public function getWhereExpression()
    {
        if ($this->subjectRef->getExpression()) {
            $this->andWhere($this->subjectRef->getExpression());
        }
        if ($this->predicate->getExpression()) {
            $this->andWhere($this->predicate->getExpression());
        }
        if ($this->objectRef->getExpression()) {
            $this->andWhere($this->objectRef->getExpression());
        }

        return parent::getWhereExpression();
    }
}
