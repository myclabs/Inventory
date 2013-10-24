<?php

namespace Keyword\Domain;

use Core\Criteria\FieldFilter;
use Doctrine\Common\Collections\Criteria;

/**
 * Criteria for filtering keywords
 *
 * @author matthieu.napoli
 */
class KeywordCriteria extends Criteria
{
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';

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
        $this->ref = new FieldFilter($this, 'this.ref');
        $this->label = new FieldFilter($this, 'this.label');
    }
}
