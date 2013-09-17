<?php

namespace Core\Criteria;

use Doctrine\Common\Collections\Criteria;

/**
 * Field filtering
 *
 * Usage inside a Criteria:
 *
 * <code>
 * // Filter on a field
 * $this->ref = new FieldFilter($this, 'ref');
 * // Filter on the field of an association
 * $this->subjectRef = new FieldFilter($this, 'subject.ref');
 * </code>
 *
 * @author matthieu.napoli
 */
class FieldFilter
{
    /**
     * @var Criteria
     */
    private $criteria;

    /**
     * @var string
     */
    private $field;

    /**
     * @param Criteria $criteria
     * @param string   $field
     */
    public function __construct(Criteria $criteria, $field)
    {
        $this->criteria = $criteria;
        $this->field = (string) $field;
    }

    /**
     * @param mixed $value
     */
    public function eq($value)
    {
        $this->criteria->andWhere($this->criteria->expr()->eq($this->field, $value));
    }

    /**
     * @param mixed $value
     */
    public function gt($value)
    {
        $this->criteria->andWhere($this->criteria->expr()->gt($this->field, $value));
    }

    /**
     * @param mixed $value
     */
    public function lt($value)
    {
        $this->criteria->andWhere($this->criteria->expr()->lt($this->field, $value));
    }

    /**
     * @param mixed $value
     */
    public function gte($value)
    {
        $this->criteria->andWhere($this->criteria->expr()->gte($this->field, $value));
    }

    /**
     * @param mixed $value
     */
    public function lte($value)
    {
        $this->criteria->andWhere($this->criteria->expr()->lte($this->field, $value));
    }

    /**
     * @param mixed $value
     */
    public function neq($value)
    {
        $this->criteria->andWhere($this->criteria->expr()->neq($this->field, $value));
    }

    public function isNull()
    {
        $this->criteria->andWhere($this->criteria->expr()->isNull($this->field));
    }

    /**
     * @param mixed[] $values
     */
    public function in(array $values)
    {
        $this->criteria->andWhere($this->criteria->expr()->in($this->field, $values));
    }

    /**
     * @param mixed[] $values
     */
    public function notIn(array $values)
    {
        $this->criteria->andWhere($this->criteria->expr()->notIn($this->field, $values));
    }

    /**
     * @param mixed $value
     */
    public function contains($value)
    {
        $this->criteria->andWhere($this->criteria->expr()->contains($this->field, $value));
    }
}
