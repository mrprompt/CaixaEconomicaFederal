<?php
namespace MrPrompt\CaixaEconomicaFederal\Tests\Common\Util;

use MrPrompt\CaixaEconomicaFederal\Common\Util\Number;
use PHPUnit_Framework_TestCase;

/**
 * Number test case.
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 */
class NumberTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \CaixaEconomicaFederal\Common\Util\Number::zeroFill
     */
    public function zeroFillMustBeReturnExactLengthWithoutFillSideParam()
    {
        $foo = Number::zeroFill(1, 10);

        $this->assertEquals(10, strlen($foo));
    }

    /**
     * @test
     * @covers \CaixaEconomicaFederal\Common\Util\Number::zeroFill
     */
    public function zeroFillMustBeReturnExactStringWhenLengthIsEqualsWithoutFillSideParam()
    {
        $foo = Number::zeroFill(1, 3);

        $this->assertEquals(3, strlen($foo));
    }
    /**
     * @test
     * @covers \CaixaEconomicaFederal\Common\Util\Number::zeroFill
     */
    public function zeroFillMustBeReturnExactLengthWithFillSideParam()
    {
        $foo = Number::zeroFill(1, 10, Number::FILL_LEFT);

        $this->assertEquals(10, strlen($foo));
    }

    /**
     * @test
     * @covers \CaixaEconomicaFederal\Common\Util\Number::zeroFill
     */
    public function zeroFillMustBeReturnExactStringWhenLengthIsEqualsWithFillSideParam()
    {
        $foo = Number::zeroFill(1, 3, Number::FILL_LEFT);

        $this->assertEquals(3, strlen($foo));
    }

    /**
     * @test
     * @covers \CaixaEconomicaFederal\Common\Util\Number::zeroFill
     * @expectedException \InvalidArgumentException
     */
    public function zeroFillThrowsExceptionWhenValueLengthIsGreaterThanLength()
    {
        Number::zeroFill(11111, 2);
    }

    /**
     * @test
     * @covers \CaixaEconomicaFederal\Common\Util\Number::zeroFill
     */
    public function zeroFillThrowsExceptionWithoutParams()
    {
        $return = Number::zeroFill('', '');

        $this->assertEmpty($return);
    }
}
