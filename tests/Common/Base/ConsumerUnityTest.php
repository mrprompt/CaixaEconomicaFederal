<?php
namespace MrPrompt\CaixaEconomicaFederal\Tests\Common\Base;

use MrPrompt\CaixaEconomicaFederal\Common\Base\ConsumerUnity;
use MrPrompt\CaixaEconomicaFederal\Common\Util\ChangeProtectedAttribute;
use Mockery as m;
use PHPUnit_Framework_TestCase;

/**
 * Consumer Unity test case.
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 */
class ConsumerUnityTest extends PHPUnit_Framework_TestCase
{
    use ChangeProtectedAttribute;

    /**
     * @var ConsumerUnity
     */
    private $unity;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->unity = new ConsumerUnity();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->unity = null;

        parent::tearDown();
    }

    /**
     * @test
     * @covers \CaixaEconomicaFederal\Common\Base\ConsumerUnity::__construct()
     * @covers \CaixaEconomicaFederal\Common\Base\ConsumerUnity::getNumber()
     */
    public function getNumberReturnNumberAttribute()
    {
        $number = rand();

        $this->modifyAttribute($this->unity, 'number', $number);

        $this->assertEquals($number, $this->unity->getNumber());
    }

    /**
     * @test
     * @covers \CaixaEconomicaFederal\Common\Base\ConsumerUnity::__construct()
     * @covers \CaixaEconomicaFederal\Common\Base\ConsumerUnity::setNumber()
     */
    public function setNumberReturnNull()
    {
        $this->assertNull($this->unity->setNumber(rand()));
    }

    /**
     * @test
     * @covers \CaixaEconomicaFederal\Common\Base\ConsumerUnity::__construct()
     * @covers \CaixaEconomicaFederal\Common\Base\ConsumerUnity::setNumber()
     * @expectedException \InvalidArgumentException
     */
    public function setNumberThrowsExceptionWhenEmpty()
    {
        $this->assertNull($this->unity->setNumber(''));
    }

    /**
     * @test
     * @covers \CaixaEconomicaFederal\Common\Base\ConsumerUnity::__construct()
     * @covers \CaixaEconomicaFederal\Common\Base\ConsumerUnity::getRead()
     */
    public function getReadReturnReadAttribute()
    {
        $read = new \DateTime();

        $this->modifyAttribute($this->unity, 'read', $read);

        $this->assertSame($read, $this->unity->getRead());
    }

    /**
     * @test
     * @covers \CaixaEconomicaFederal\Common\Base\ConsumerUnity::__construct()
     * @covers \CaixaEconomicaFederal\Common\Base\ConsumerUnity::setRead()
     */
    public function setReadReturnNull()
    {
        $this->assertNull($this->unity->setRead(new \DateTime()));
    }

    /**
     * @test
     * @covers \CaixaEconomicaFederal\Common\Base\ConsumerUnity::__construct()
     * @covers \CaixaEconomicaFederal\Common\Base\ConsumerUnity::getMaturity()
     */
    public function getMaturityReturnMaturityAttribute()
    {
        $read = new \DateTime();

        $this->modifyAttribute($this->unity, 'maturity', $read);

        $this->assertSame($read, $this->unity->getMaturity());
    }

    /**
     * @test
     * @covers \CaixaEconomicaFederal\Common\Base\ConsumerUnity::__construct()
     * @covers \CaixaEconomicaFederal\Common\Base\ConsumerUnity::setMaturity()
     */
    public function setMaturityReturnNull()
    {
        $this->assertNull($this->unity->setMaturity(new \DateTime()));
    }

    /**
     * @test
     * @covers \CaixaEconomicaFederal\Common\Base\Consumerunity::__construct()
     * @covers \CaixaEconomicaFederal\Common\Base\Consumerunity::getCode()
     */
    public function getCodeReturnCodeAttribute()
    {
        $this->modifyAttribute($this->unity, 'code', 1);

        $this->assertEquals($this->unity->getCode(), 1);
    }

    /**
     * @test
     * @covers \CaixaEconomicaFederal\Common\Base\Consumerunity::__construct()
     * @covers \CaixaEconomicaFederal\Common\Base\Consumerunity::setCode()
     * @covers \CaixaEconomicaFederal\Common\Base\Consumerunity::getCode()
     */
    public function setCodeChangeCodeAttribute()
    {
        $this->unity->setCode(1);

        $this->assertEquals($this->unity->getCode(), 1);
    }

    /**
     * @test
     * @covers \CaixaEconomicaFederal\Common\Base\Consumerunity::__construct()
     * @covers \CaixaEconomicaFederal\Common\Base\Consumerunity::setCode()
     * @expectedException InvalidArgumentException
     */
    public function setCodeOnlyAcceptIntegerValue()
    {
        $this->unity->setCode('A');
    }
}