<?php
namespace MrPrompt\CaixaEconomicaFederal\Tests\Shipment;

use MrPrompt\CaixaEconomicaFederal\Common\Base\Cart;
use MrPrompt\CaixaEconomicaFederal\Common\Util\ChangeProtectedAttribute;
use MrPrompt\CaixaEconomicaFederal\Shipment\Billet;
use MrPrompt\CaixaEconomicaFederal\Shipment\Partial\Detail;
use MrPrompt\CaixaEconomicaFederal\Tests\Mock;
use DateTime;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

/**
 * Billet test case.
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 */
class BilletTest extends TestCase
{
    use ChangeProtectedAttribute;
    use Mock;

    /**
     * @var Billet
     */
    private $file;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private static $root;

    /**
     * Boostrap
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$root = vfsStream::setup();
    }

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->file = new Billet(
            $this->customerMock(),
            $this->sequenceMock(),
            DateTime::createFromFormat('d-m-Y', '27-05-2015'),
            self::$root->url()
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
     * @covers \MrPrompt\CaixaEconomicaFederal\Shipment\Billet::__construct()
     * @covers \MrPrompt\CaixaEconomicaFederal\Shipment\Billet::getCart()
     */
    public function getCartMustBeReturnCartAttribute()
    {
        $cart = new Cart();

        $this->modifyAttribute($this->file, 'cart', $cart);

        $this->assertSame($cart, $this->file->getCart());
    }

    /**
     * @test
     * @covers \MrPrompt\CaixaEconomicaFederal\Shipment\Billet::__construct()
     * @covers \MrPrompt\CaixaEconomicaFederal\Shipment\Billet::setCart()
     */
    public function setCartMustBeReturnCartAttribute()
    {
        $cart   = new Cart;
        $result = $this->file->setCart($cart);

        $this->assertNull($result);
    }

    /**
     * @test
     * @covers \MrPrompt\CaixaEconomicaFederal\Shipment\Billet::__construct()
     * @covers \MrPrompt\CaixaEconomicaFederal\Shipment\Billet::save()
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
     * @depends save
     * @covers \MrPrompt\CaixaEconomicaFederal\Shipment\Billet::__construct()
     * @covers \MrPrompt\CaixaEconomicaFederal\Shipment\Billet::read()
     */
    public function read()
    {
        $this->modifyAttribute($this->file, 'cart', new Cart());

        $result = $this->file->read();

        $this->assertNotEmpty($result);
    }
}
