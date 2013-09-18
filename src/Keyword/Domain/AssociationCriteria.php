<?php

namespace Keyword\Domain;

use Core\Criteria\FieldFilter;
use Doctrine\Common\Collections\Criteria;

/**
 * Criteria for filtering keywords associations
 *
 * @author matthieu.napoli
 */
class AssociationCriteria extends Criteria
{
    const QUERY_KEYWORD_SUBJECT_REF = 'subjectRef';
    const QUERY_KEYWORD_SUBJECT_LABEL = 'subjectLabel';
    const QUERY_PREDICATE_REF = 'predicateRef';
    const QUERY_PREDICATE_LABEL = 'predicateLabel';
    const QUERY_KEYWORD_OBJECT_REF = 'objectRef';
    const QUERY_KEYWORD_OBJECT_LABEL = 'objectLabel';

    /**
     * @var FieldFilter
     */
    public $subjectRef;

    /**
     * @var FieldFilter
     */
    public $subjectLabel;

    /**
     * @var FieldFilter
     */
    public $predicateRef;

    /**
     * @var FieldFilter
     */
    public $predicateLabel;

    /**
     * @var FieldFilter
     */
    public $objectRef;

    /**
     * @var FieldFilter
     */
    public $objectLabel;

    public function __construct()
    {
        $this->subjectRef = new FieldFilter($this, 'subject.ref');
        $this->subjectLabel = new FieldFilter($this, 'subject.label');
        $this->predicateRef = new FieldFilter($this, 'predicate.ref');
        $this->predicateLabel = new FieldFilter($this, 'predicate.label');
        $this->objectRef = new FieldFilter($this, 'object.ref');
        $this->objectLabel = new FieldFilter($this, 'object.label');
    }
}
