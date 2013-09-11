<?php

namespace Keyword\Domain;

use Core\Domain\Criteria\CountCriteria;
use Core\Domain\Criteria\OrderCriteria;

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
     * @var string
     */
    public $ref;

    /**
     * @var
     */
    public $refOperator;

    /**
     * @var string
     */
    public $label;

    /**
     * @var
     */
    public $labelOperator;
}
