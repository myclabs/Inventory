<?php

namespace Keyword\Domain;

use Core\Domain\Criteria\CountCriteria;
use Core\Domain\Criteria\OrderCriteria;
use Doctrine\Common\Collections\Expr\Expression;

/**
 * Criteria for filtering keywords
 *
 * @author matthieu.napoli
 */
class KeywordCriteria
{
    use OrderCriteria;
    use CountCriteria;

    /**
     * @var Expression
     */
    public $ref;

    /**
     * @var Expression
     */
    public $label;
}
