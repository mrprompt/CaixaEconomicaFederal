<?php
namespace MrPrompt\CaixaEconomicaFederal\Gateway\Shipment;

use MrPrompt\CaixaEconomicaFederal\Common\Base\Cart;
use MrPrompt\CaixaEconomicaFederal\Common\Base\Customer;
use MrPrompt\CaixaEconomicaFederal\Common\Base\Sequence;
use MrPrompt\CaixaEconomicaFederal\Common\Converter\Pdf;
use MrPrompt\CaixaEconomicaFederal\Common\Util\Number;
use DateInterval;
use DateTime;
use OpenBoleto\Agente;
use OpenBoleto\Banco\Caixa;

/**
 * Payment Slip file class
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 */
final class PaymentSlip
{
    /**
     * File name template
     *
     * @var string
     */
    const TEMPLATE_GENERATED = '{CLIENT}_{DDMMYYYY}_{SEQUENCE}.HTML';

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
     * @var File
     */
    protected $shipmentFile;

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
        $this->shipmentFile = new File($customer, $sequence, $today, $storageDir);
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
     * Save the output result
     *
     * @return string
     */
    public function save()
    {
        /* @var $item \CaixaEconomicaFederal\Gateway\Shipment\Partial\Detail */
        $item       = $this->cart->offsetGet(0);

        /* @var filename string */
        $filename   = $this->createFilename();

        /* @var $outputFile string */
        $outputFile = $this->storage . DIRECTORY_SEPARATOR . $filename;

        /* @var $sacado \OpenBoleto\Agente */
        $sacado     = new Agente(
            $item->getPurchaser()->getName(),
            preg_replace('/[^[:digit:]]/', '', $item->getPurchaser()->getDocument()->getNumber()),
            sprintf(
                '%s %s',
                $item->getPurchaser()->getAddress()->getAddress(),
                $item->getPurchaser()->getAddress()->getComplement()
            ),
            $item->getPurchaser()->getAddress()->getPostalCode(),
            $item->getPurchaser()->getAddress()->getCity(),
            $item->getPurchaser()->getAddress()->getState()
        );

        /* @var $cedente \OpenBoleto\Agente */
        $cedente    = new Agente(
            $item->getSeller()->getName(),
            preg_replace('/[^[:digit:]]/', '', $item->getSeller()->getDocument()->getNumber()),
            sprintf(
                '%s %s',
                $item->getSeller()->getAddress()->getAddress(),
                $item->getSeller()->getAddress()->getComplement()
            ),
            $item->getSeller()->getAddress()->getPostalCode(),
            $item->getSeller()->getAddress()->getCity(),
            $item->getSeller()->getAddress()->getState()
        );

        /* @var $content string */
        $content = '';

        /* @var $parcel \CaixaEconomicaFederal\Common\Base\Parcel */
        foreach ($item->getParcels() as $parcel) {
            /* @var $boleto \OpenBoleto\Banco\Caixa */
            $boleto = new Caixa([
                'dataVencimento'            => $parcel->getMaturity(),
                'valor'                     => $parcel->getPrice(),
                'sequencial'                => $item->getAuthorization()->getNumber(),
                'sacado'                    => $sacado,
                'cedente'                   => $cedente,
                'agencia'                   => $item->getBillet()->getBankAccount()->getBank()->getAgency(),
                'agenciaDv'                 => $item->getBillet()->getBankAccount()->getBank()->getDigit(),
                'carteira'                  => 'RG',
                'conta'                     => $item->getSeller()->getCode(),
                'contaDv'                   => 2,
                'moeda'                     => Caixa::MOEDA_REAL,
                'dataDocumento'             => new DateTime(),
                'dataProcessamento'         => new DateTime(),
                'aceite'                    => 'N',
                'especieDoc'                => 'DM',
                'numeroDocumento'           => sprintf('%s/%s', $item->getBillet()->getNumber(), $this->sequence->getValue()),
                'contraApresentacao'        => false,
                'descricaoDemonstrativo'    => [''],
                'instrucoes'                => [''],
            ]);

            $boleto->setLayout('default-carne.phtml');

            $parcel->getMaturity()->add(new DateInterval('P30D'));

            $item->getAuthorization()->setNumber($item->getAuthorization()->getNumber() + 1);

            $content .= $boleto->getOutput();
        }

        $content = str_replace(
            [
                $item->getBillet()->getBankAccount()->getBank()->getAgency() . '-' . $item->getBillet()->getBankAccount()->getBank()->getDigit(),
                'REAL'
            ],
            [
                $item->getBillet()->getBankAccount()->getBank()->getAgency(),
                'R$'
            ],
            $content
        );

        file_put_contents($outputFile, $content);

        Pdf::convert($content, $outputFile);

        $this->shipmentFile->setCart($this->cart);
        $this->shipmentFile->save();

        return $outputFile;
    }

    /**
     * Read a return file
     *
     * @return array [Header, Detail, Footer]
     * @throws \Exception
     */
    public function read()
    {
        $filename       = $this->createFilename();
        $inputFile      = $this->storage . DIRECTORY_SEPARATOR . $filename;

        return file_get_contents($inputFile);
    }
}
