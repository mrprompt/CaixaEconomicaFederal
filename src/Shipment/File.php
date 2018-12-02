<?php
namespace MrPrompt\CaixaEconomicaFederal\Shipment;

use MrPrompt\ShipmentCommon\Base\Address;
use MrPrompt\ShipmentCommon\Base\Bank;
use MrPrompt\ShipmentCommon\Base\Cart;
use MrPrompt\ShipmentCommon\Base\Charge;
use MrPrompt\ShipmentCommon\Base\Customer;
use MrPrompt\ShipmentCommon\Base\Document;
use MrPrompt\ShipmentCommon\Base\Occurrence;
use MrPrompt\ShipmentCommon\Base\Purchaser;
use MrPrompt\ShipmentCommon\Base\Seller;
use MrPrompt\ShipmentCommon\Base\Sequence;
use MrPrompt\ShipmentCommon\Util\Number;
use MrPrompt\CaixaEconomicaFederal\Factory;
use MrPrompt\CaixaEconomicaFederal\Shipment\Partial\Footer;
use MrPrompt\CaixaEconomicaFederal\Shipment\Partial\Header;
use Cnab\Banco;
use Cnab\Especie;
use Cnab\Remessa\Cnab240\Arquivo;
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
    const TEMPLATE_GENERATED = '{CLIENT}_{DDMMYYYY}_{SEQUENCE}.TXT';

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
     * Save the output result
     *
     * @return string
     */
    public function save()
    {
        /* @var $item \MrPrompt\CaixaEconomicaFederal\Shipment\Partial\Detail */
        $item       = $this->cart->offsetGet(0);

        /* @var filename string */
        $filename   = $this->createFilename();

        /* @var $outputFile string */
        $outputFile = $this->storage . DIRECTORY_SEPARATOR . $filename;

        /* @var $seller Seller */
        $seller = $item->getSeller();
        
        /* @var $address Address */
        $address = $seller->getAddress();

        /* @var $file \Cnab\Remessa\Cnab240\Arquivo */
        $file = new Arquivo(Banco::CEF, 'sigcb');
        $file->configure([
            'data_geracao'              => new DateTime(),
            'data_gravacao'             => new DateTime(),
            'nome_fantasia'             => $seller->getName(),
            'razao_social'              => $seller->getName(),
            'codigo_inscricao'          => '01',
            'cnpj'                      => $seller->getDocument()->getNumber(),
            'banco'                     => Banco::CEF,
            'logradouro'                => sprintf('%s %s', $address->getAddress(), $address->getComplement()),
            'numero'                    => $address->getNumber(),
            'bairro'                    => $address->getDistrict(),
            'cidade'                    => $address->getCity(),
            'uf'                        => $address->getState(),
            'cep'                       => $address->getPostalCode(),
            'agencia'                   => $item->getBillet()->getBankAccount()->getBank()->getAgency(),
            'agencia_dv'                => $item->getBillet()->getBankAccount()->getBank()->getDigit(),
            'conta'                     => $item->getBillet()->getBankAccount()->getNumber(),
            'operacao'                  => $item->getBillet()->getBankAccount()->getOperation(),
            'codigo_cedente'            => $seller->getCode(),
            'codigo_cedente_dac'        => '2',
            'numero_sequencial_arquivo' => $this->sequence->getValue(),
        ]);

        /* @var $parcels \MrPrompt\ShipmentCommon\Base\Parcels */
        $parcels = $item->getParcels();

        /* @var $parcel \MrPrompt\ShipmentCommon\Base\Parcel */
        foreach ($parcels as $parcel) {
            /* @var $purchaser Purchaser */
            $purchaser  = $item->getPurchaser();
            
            /* @var $address Address */
            $address    = $purchaser->getAddress();

            /* @var $document Document */
            $document   = $purchaser->getDocument();
            $docType    = $document->getType() === Document::CPF ? 'sacado_cpf' : 'sacado_cnpj';

            $rowFile    = [
                'codigo_ocorrencia'             => 1,
                'nosso_numero'                  => $item->getAuthorization()->getNumber(),
                'numero_documento'              => sprintf('%s/%s', $item->getBillet()->getNumber(), $this->sequence->getValue()),
                'modalidade_carteira'           => 14,
                'especie'                       => Especie::CEF_OUTROS,
                'aceite'                        => 'N',
                'registrado'                    => true,
                'valor'                         => $parcel->getPrice(),
                'instrucao1'                    => 1,
                'instrucao2'                    => 0,
                'sacado_nome'                   => $purchaser->getName(),
                'sacado_tipo'                   => $document->getType() === Document::CPF ? 'cpf' : 'cnpj',
                'sacado_logradouro'             => $address->getAddress(),
                'sacado_bairro'                 => $address->getDistrict(),
                'sacado_cep'                    => $address->getPostalCode(),
                'sacado_cidade'                 => $address->getCity(),
                'sacado_uf'                     => $address->getState(),
                'data_vencimento'               => $parcel->getMaturity(),
                'data_cadastro'                 => new DateTime(),
                'juros_de_um_dia'               => 0.02,
                'data_desconto'                 => $parcel->getMaturity(),
                'valor_desconto'                => $parcel->getPrice(),
                'prazo'                         => 0,
                'taxa_de_permanencia'           => 0,
                'mensagem'                      => '',
                'data_multa'                    => $parcel->getMaturity(),
                'valor_multa'                   => 0.0,
                'identificacao_distribuicao'    => 0,
                "{$docType}"                    => $document->getNumber(),
            ];

            // Fix
            $file->headerLote->uso_exclusivo_banco = '00000000000000';

            $file->insertDetalhe($rowFile);
        }

        $file->save($outputFile);

        return $outputFile;
    }
}
