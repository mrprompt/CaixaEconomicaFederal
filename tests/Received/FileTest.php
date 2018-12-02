<?php
namespace MrPrompt\CaixaEconomicaFederal\Tests\Received;

use MrPrompt\ShipmentCommon\Base\Cart;
use MrPrompt\CaixaEconomicaFederal\Tests\ChangeProtectedAttribute;
use MrPrompt\CaixaEconomicaFederal\Received\File;
use MrPrompt\CaixaEconomicaFederal\Received\Partial\Footer;
use MrPrompt\CaixaEconomicaFederal\Received\Partial\Header;
use MrPrompt\CaixaEconomicaFederal\Tests\Mock;
use DateTime;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * file test case.
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 */
class FileTest extends TestCase
{
    use ChangeProtectedAttribute;
    use Mock;

    /**
     * @var File
     */
    private $file;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->file = new File(
            $this->customerMock(),
            $this->sequenceMock(),
            DateTime::createFromFormat('d-m-Y', '27-05-2015'),
            __DIR__ . DIRECTORY_SEPARATOR . 'resources'
        );
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->file = null;

        parent::tearDown();
    }

    /**
     * @test
     * @covers \MrPrompt\CaixaEconomicaFederal\Received\File::__construct()
     * @covers \MrPrompt\CaixaEconomicaFederal\Received\File::getCart()
     */
    public function getCartMustBeReturnCartAttribute()
    {
        $cart = new Cart();

        $this->modifyAttribute($this->file, 'cart', $cart);

        $this->assertSame($cart, $this->file->getCart());
    }

    /**
     * @test
     * @covers \MrPrompt\CaixaEconomicaFederal\Received\File::__construct()
     * @covers \MrPrompt\CaixaEconomicaFederal\Received\File::setCart()
     */
    public function setCartMustBeReturnCartAttribute()
    {
        $cart   = new Cart;
        $result = $this->file->setCart($cart);

        $this->assertNull($result);
    }

    /**
     * @test
     * @covers \MrPrompt\CaixaEconomicaFederal\Received\File::__construct()
     * @covers \MrPrompt\CaixaEconomicaFederal\Received\File::read()
     */
    public function read()
    {
        $this->modifyAttribute($this->file, 'cart', new Cart());

        $result = $this->file->read();

        $this->assertTrue(is_array($result));
        $this->assertInstanceOf(Header::class, $result[0]);
        $this->assertInstanceOf(Cart::class, $result[1]);
        $this->assertInstanceOf(Footer::class, $result[2]);
    }
}
