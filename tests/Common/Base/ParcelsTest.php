<?php
namespace MrPrompt\CaixaEconomicaFederal\Tests\Common\Base;

use MrPrompt\CaixaEconomicaFederal\Common\Base\Parcels;
use MrPrompt\CaixaEconomicaFederal\Common\Util\ChangeProtectedAttribute;
use MrPrompt\CaixaEconomicaFederal\Tests\Gateway\Mock;
use Mockery as m;
use PHPUnit_Framework_TestCase;

/**
 * Parcels test case.
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 */
class ParcelsTest extends PHPUnit_Framework_TestCase
{
    use ChangeProtectedAttribute;
    use Mock;

    /**
     * @var Parcels
     */
    private $parcels;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->parcels = new Parcels(4);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->parcels = null;

        parent::tearDown();
    }

    /**
     * @test
     * @covers \CaixaEconomicaFederal\Common\Base\Parcels::__construct
     * @covers \CaixaEconomicaFederal\Common\Base\Parcels::addParcel
     */
    public function addParcelMustReturnNullWhenReceiveParcelObject()
    {
        $this->assertNull($this->parcels->addParcel($this->parcelMock()));
    }
}
