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
        $this->ref = new FieldFilter($this, 'ref');
        $this->label = new FieldFilter($this, 'label');
    }
}
