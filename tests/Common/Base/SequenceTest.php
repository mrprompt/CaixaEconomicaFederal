<?php
namespace MrPrompt\CaixaEconomicaFederal\Tests\Common\Base;

use MrPrompt\CaixaEconomicaFederal\Common\Base\Sequence;
use MrPrompt\CaixaEconomicaFederal\Common\Util\ChangeProtectedAttribute;
use Mockery as m;
use PHPUnit_Framework_TestCase;

/**
 * Sequence test case.
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 */
class SequenceTest extends PHPUnit_Framework_TestCase
{
    use ChangeProtectedAttribute;

    /**
     * @var Sequence
     */
    private $sequence;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->sequence = new Sequence();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->sequence = null;

        parent::tearDown();
    }

    /**
     * @test
     * @covers \MrPrompt\CaixaEconomicaFederal\Common\Base\Sequence::__construct()
     * @covers \MrPrompt\CaixaEconomicaFederal\Common\Base\Sequence::getValue()
     */
    public function getValueReturnValueAttribute()
    {
        $this->modifyAttribute($this->sequence, 'value', 1);

        $this->assertEquals(1, $this->sequence->getValue());
    }

    /**
     * @test
     * @covers \MrPrompt\CaixaEconomicaFederal\Common\Base\Sequence::__construct()
     * @covers \MrPrompt\CaixaEconomicaFederal\Common\Base\Sequence::setValue()
     * @covers \MrPrompt\CaixaEconomicaFederal\Common\Base\Sequence::getValue()
     */
    public function setValueModifyValueAttribute()
    {
        $this->sequence->setValue(1);

        $this->assertEquals(1, $this->sequence->getValue());
    }

    /**
     * @test
     * @covers \MrPrompt\CaixaEconomicaFederal\Common\Base\Sequence::__construct()
     * @covers \MrPrompt\CaixaEconomicaFederal\Common\Base\Sequence::setValue()
     * @expectedException InvalidArgumentException
     */
    public function setValueOnlyAcceptIntegerValue()
    {
        $this->sequence->setValue('foo');
    }
}
