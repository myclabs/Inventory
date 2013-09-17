<?php

namespace Core\Criteria;

use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\Common\Collections\Expr\Value;

/**
 * Field filtering
 *
 * Usage inside a Criteria:
 *
 * <code>
 * // Filter on a field
 * $this->ref = new FieldFilter('ref');
 * // Filter on the field of an association
 * $this->subjectRef = new FieldFilter('subject.ref');
 * </code>
 *
 * @author matthieu.napoli
 */
class FieldFilter
{
    /**
     * @var string
     */
    private $field;

    /**
     * @var Expression|null
     */
    private $expression;

    /**
     * @param string $field
     */
    public function __construct($field)
    {
        $this->field = (string) $field;
    }

    /**
     * @return Expression|null
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param mixed $value
     */
    public function eq($value)
    {
        $this->expression = new Comparison($this->field, Comparison::EQ, new Value($value));
    }

    /**
     * @param mixed $value
     */
    public function gt($value)
    {
        $this->expression = new Comparison($this->field, Comparison::GT, new Value($value));
    }

    /**
     * @param mixed $value
     */
    public function lt($value)
    {
        $this->expression = new Comparison($this->field, Comparison::LT, new Value($value));
    }

    /**
     * @param mixed $value
     */
    public function gte($value)
    {
        $this->expression = new Comparison($this->field, Comparison::GTE, new Value($value));
    }

    /**
     * @param mixed $value
     */
    public function lte($value)
    {
        $this->expression = new Comparison($this->field, Comparison::LTE, new Value($value));
    }

    /**
     * @param mixed $value
     */
    public function neq($value)
    {
        $this->expression = new Comparison($this->field, Comparison::NEQ, new Value($value));
    }

    public function isNull()
    {
        $this->expression = new Comparison($this->field, Comparison::IS, new Value(null));
    }

    /**
     * @param mixed[] $values
     */
    public function in(array $values)
    {
        $this->expression = new Comparison($this->field, Comparison::IN, new Value($values));
    }

    /**
     * @param mixed[] $values
     */
    public function notIn(array $values)
    {
        $this->expression = new Comparison($this->field, Comparison::NIN, new Value($values));
    }

    /**
     * @param mixed $value
     */
    public function contains($value)
    {
        $this->expression = new Comparison($this->field, Comparison::CONTAINS, new Value($value));
    }
}
