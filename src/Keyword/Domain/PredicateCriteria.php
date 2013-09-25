<?php

namespace Keyword\Domain;

use Core\Criteria\FieldFilter;
use Doctrine\Common\Collections\Criteria;

/**
 * Criteria for filtering keywords
 *
 * @author matthieu.napoli
 */
class PredicateCriteria extends Criteria
{
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_REVERSE_REF = 'reverseRef';
    const QUERY_REVERSE_LABEL = 'reverseLabel';

    /**
     * @var FieldFilter
     */
    public $ref;

    /**
     * @var FieldFilter
     */
    public $label;

    /**
     * @var FieldFilter
     */
    public $reverseRef;

    /**
     * @var FieldFilter
     */
    public $reverseLabel;

    public function __construct()
    {
        $this->ref = new FieldFilter($this, 'this.ref');
        $this->label = new FieldFilter($this, 'this.label');
        $this->reverseRef = new FieldFilter($this, 'this.reversRef');
        $this->reverseLabel = new FieldFilter($this, 'this.reverseLabel');
    }
}
