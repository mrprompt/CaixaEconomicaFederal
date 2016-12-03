<?php
namespace MrPrompt\CaixaEconomicaFederal\Tests\Gateway\Received\Partial;

use MrPrompt\CaixaEconomicaFederal\Common\Util\ChangeProtectedAttribute;
use MrPrompt\CaixaEconomicaFederal\Gateway\Received\Partial\Footer;
use MrPrompt\CaixaEconomicaFederal\Tests\Gateway\Mock as CaixaEconomicaFederalMock;
use Mockery as m;
use PHPUnit_Framework_TestCase;

/**
 * Footer test case.
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 */
class FooterTest extends PHPUnit_Framework_TestCase
{
    use ChangeProtectedAttribute;
    use CaixaEconomicaFederalMock;

    /**
     * Footer object
     *
     * @var Footer
     */
    private $footer;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->footer = new Footer(1, 1, $this->sequenceMock());
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->footer = null;

        parent::tearDown();
    }

    /**
     * @test
     */
    public function getSequence()
    {
        $this->assertEquals($this->sequenceMock(), $this->footer->getSequence());
    }

    /**
     * @test
     */
    public function setSequence()
    {
        $this->assertNull($this->footer->setSequence($this->sequenceMock()));
    }

    /**
     * @test
     */
    public function getTotal()
    {
        $this->assertEquals(1, $this->footer->getTotal());
    }

    /**
     * @test
     */
    public function setTotal()
    {
        $this->assertNull($this->footer->setTotal(0));
    }

    /**
     * @test
     */
    public function getSum()
    {
        $this->assertEquals(1, $this->footer->getSum());
    }

    /**
     * @test
     */
    public function setSum()
    {
        $this->assertNull($this->footer->setSum(0));
    }
}
