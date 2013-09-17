<?php

namespace Keyword\Domain;

use Core\Criteria\FieldFilter;
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
     * @var FieldFilter
     */
    public $subjectRef;

    /**
     * @var FieldFilter
     */
    public $predicate;

    /**
     * @var FieldFilter
     */
    public $objectRef;

    public function __construct()
    {
        $this->subjectRef = new FieldFilter($this, 'subject.ref');
        $this->predicate = new FieldFilter($this, 'predicate');
        $this->objectRef = new FieldFilter($this, 'object.ref');
    }
}
