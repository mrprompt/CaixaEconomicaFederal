<?php
namespace MrPrompt\CaixaEconomicaFederal\Received;

use MrPrompt\ShipmentCommon\Base\Bank;
use MrPrompt\ShipmentCommon\Base\Cart;
use MrPrompt\ShipmentCommon\Base\Charge;
use MrPrompt\ShipmentCommon\Base\Customer;
use MrPrompt\ShipmentCommon\Base\Occurrence;
use MrPrompt\ShipmentCommon\Base\Sequence;
use MrPrompt\ShipmentCommon\Util\Number;
use MrPrompt\CaixaEconomicaFederal\Factory;
use MrPrompt\CaixaEconomicaFederal\Received\Partial\Footer;
use MrPrompt\CaixaEconomicaFederal\Received\Partial\Header;
use DateTime;

/**
 * Billet file class
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 */
final class File
{
    /**
     * File name template
     *
     * @var string
     */
    const TEMPLATE_GENERATED = '{CLIENT}_{DDMMYYYY}_{SEQUENCE}.TXT.RET';

    /**
     * @var DateTime
     */
    protected $now;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var Sequence
     */
    protected $sequence;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var string
     */
    protected $storage;

    /**
     * @var string
     */
    protected $header;

    /**
     * @var string
     */
    protected $footer;

    /**
     * @param Customer $customer
     * @param Sequence $sequence
     * @param DateTime $today
     * @param string   $storageDir
     */
    public function __construct(Customer $customer, Sequence $sequence, DateTime $today, $storageDir = null)
    {
        $this->customer     = $customer;
        $this->sequence     = $sequence;
        $this->now          = $today;
        $this->storage      = $storageDir;
    }

    /**
     * Return the cart
     *
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * Set the for with the payments
     *
     * @param Cart $cart
     */
    public function setCart(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Create the file name
     *
     * @return string
     */
    protected function createFilename()
    {
        $search = [
            '{CLIENT}',
            '{DDMMYYYY}',
            '{SEQUENCE}'
        ];

        $replace = [
            Number::zeroFill($this->customer->getCode(), 6, Number::FILL_LEFT),
            $this->now->format('dmY'),
            Number::zeroFill($this->sequence->getValue(), 5, Number::FILL_LEFT),
        ];

        return str_replace($search, $replace, self::TEMPLATE_GENERATED);
    }

    /**
     * Read a return file
     *
     * @param string $file
     * @return array [Header, Detail, Footer]
     * @throws \Exception
     */
    public function read($file = null)
    {
        $filename       = $file ? $file : $this->createFilename();
        $inputFile      = $this->storage . DIRECTORY_SEPARATOR . $filename;

        /* @var $cnab \Cnab\Factory */
        $cnab           = new \Cnab\Factory();
        $file           = $cnab->createRetorno($inputFile);
        $details        = $file->listDetalhes();

        /* @var $detail \Cnab\Retorno\Cnab240\Detalhe */
        foreach($details as $detail) {
            $result = [
                'cliente'           => $this->customer->getCode(),
                'cobranca'          => Charge::BILLET,
                'ocorrencia'        => Occurrence::INSERT,
                'identificador'     => $detail->getNumeroDocumento(),
                'autorizacao'       => $detail->getNossoNumero(),
                'parcelas'          => [
                    [
                        'vencimento' => ($detail->getDataVencimento() ? $detail->getDataVencimento()->format('dmY') : null),
                        'valor'      => $detail->getValorRecebido(),
                        'quantidade' => 1,  // quantidade de parcelas do bloqueto
                        'situacao'   => $detail->isBaixa()
                    ],
                ],
                'boleto'            => [
                    'documento'         => $detail->getNumeroDocumento(),
                    'banco'             => [
                        'codigo'        => Bank::CAIXA_ECONOMICA_FEDERAL,
                        'agencia'       => $detail->getAgencia(),
                        'digito'        => $detail->getAgenciaDv(),
                        'conta'         => [
                            'numero'    => $detail->getAgenciaCobradora(),
                            'digito'    => $detail->getAgenciaCobradoraDac(),
                            'operacao'  => 003,
                        ]
                    ],
                ],
            ];

            $this->cart->addItem( Factory::createDetailFromArray($result) );
        }

        $this->header = $this->getHeaderFromFile();
        $this->footer = $this->getFooterFromFile();

        return [
            $this->header,
            $this->cart,
            $this->footer
        ];
    }

    /**
     * @return Header
     */
    private function getHeaderFromFile()
    {
        $header = new Header(
            $this->cart->offsetGet(0)->getCustomer(),
            $this->cart->offsetGet(0)->getSequence(),
            $this->now
        );

        return $header;
    }

    /**
     * @return Footer
     */
    private function getFooterFromFile()
    {
        $sum = 0;

        foreach ($this->cart as $item) {
            foreach ($item->getParcels() as $item) {
                $sum += $item->getPrice();
            }
        }

        $footer = new Footer(
            $sum,
            $this->cart->count(),
            $this->cart->offsetGet(0)->getSequence()
        );

        return $footer;
    }
}
