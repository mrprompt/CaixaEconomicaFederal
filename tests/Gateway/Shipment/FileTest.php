<?php
namespace MrPrompt\CaixaEconomicaFederal\Tests\Gateway\Shipment;

use MrPrompt\CaixaEconomicaFederal\Common\Base\Cart;
use MrPrompt\CaixaEconomicaFederal\Common\Util\ChangeProtectedAttribute;
use MrPrompt\CaixaEconomicaFederal\Gateway\Shipment\File;
use MrPrompt\CaixaEconomicaFederal\Gateway\Shipment\Partial\Detail;
use MrPrompt\CaixaEconomicaFederal\Gateway\Shipment\Partial\Footer;
use MrPrompt\CaixaEconomicaFederal\Gateway\Shipment\Partial\Header;
use MrPrompt\CaixaEconomicaFederal\Tests\Gateway\Mock;
use DateTime;
use Mockery as m;
use PHPUnit_Framework_TestCase;

/**
 * file test case.
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 */
class FileTest extends PHPUnit_Framework_TestCase
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
            __DIR__ . '/resources'
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
     * @covers \CaixaEconomicaFederal\Gateway\Shipment\File::__construct()
     * @covers \CaixaEconomicaFederal\Gateway\Shipment\File::getCart()
     */
    public function getCartMustBeReturnCartAttribute()
    {
        $cart = new Cart();

        $this->modifyAttribute($this->file, 'cart', $cart);

        $this->assertSame($cart, $this->file->getCart());
    }

    /**
     * @test
     * @covers \CaixaEconomicaFederal\Gateway\Shipment\File::__construct()
     * @covers \CaixaEconomicaFederal\Gateway\Shipment\File::setCart()
     */
    public function setCartMustBeReturnCartAttribute()
    {
        $cart   = new Cart;
        $result = $this->file->setCart($cart);

        $this->assertNull($result);
    }

    /**
     * @test
     * @covers \CaixaEconomicaFederal\Gateway\Shipment\File::__construct()
     * @covers \CaixaEconomicaFederal\Gateway\Shipment\File::save()
     */
    public function save()
    {
        $item = m::mock(Detail::class);
        $item->shouldReceive('getSeller')->andReturn($this->sellerMock());
        $item->shouldReceive('getBillet')->andReturn($this->billetMock());
        $item->shouldReceive('getAuthorization')->andReturn($this->authorizationMock());
        $item->shouldReceive('getParcels')->andReturn($this->parcelsMock());
        $item->shouldReceive('getPurchaser')->andReturn($this->purchaserMock());

        $this->modifyAttribute($this->file, 'cart', new Cart([$item]));

        $output = $this->file->save();

        $this->assertFileExists($output);
    }

    /**
     * @test
     * @covers \CaixaEconomicaFederal\Gateway\Shipment\File::__construct()
     * @covers \CaixaEconomicaFederal\Gateway\Shipment\File::read()
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
