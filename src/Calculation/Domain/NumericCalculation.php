<?php

namespace Calculation\Domain;

/**
 * NumericCalculation
 *
 * @author matthieu.napoli
 */
class NumericCalculation implements Calculation
{
    /**
     * @var Constant[]
     */
    private $constants;

    /**
     * @var ParameterReference[]
     */
    private $parameters;

    /**
     * @var Expression[]
     */
    private $expressions;

    /**
     * {@inheritdoc}
     */
    public function calculate(ValueSet $inputs)
    {
        $inputs = new ArrayValueSet();
        $inputs->add($inputs);
        $inputs->add($this->getConstantsAsValueSet());
        $inputs->add($this->getParametersAsValueSet());

        $results = new ArrayValueSet();

        foreach ($this->expressions as $expression) {
            $result = $expression->execute($inputs);
            $results->set($expression->getRef(), $result);
        }

        return $results;
    }

    /**
     * @return ValueSet
     */
    private function getConstantsAsValueSet()
    {
    }

    /**
     * @return ValueSet
     */
    private function getParametersAsValueSet()
    {
    }
}
