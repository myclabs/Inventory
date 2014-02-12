<?php

namespace Tests\Algo\Numeric;

use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Numeric\NumericConstantAlgo;
use Calc_UnitValue;
use Core\Test\TestCase;

/**
 * @covers \AF\Domain\Algorithm\Numeric\NumericConstantAlgo
 */
class ConstantTest extends TestCase
{
    /**
     * Teste que l'algo retourne la constante.
     */
    public function testExecute()
    {
        $constantValue = new Calc_UnitValue();

        $algo = new NumericConstantAlgo();
        $algo->setUnitValue($constantValue);

        $result = $algo->execute($this->getMockForAbstractClass(InputSet::class));

        $this->assertSame($constantValue, $result);
    }
}
