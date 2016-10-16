<?php
namespace MrPrompt\CaixaEconomicaFederal\Gateway\Shipment;

use MrPrompt\CaixaEconomicaFederal\Common\Base\Address;
use MrPrompt\CaixaEconomicaFederal\Common\Base\Bank;
use MrPrompt\CaixaEconomicaFederal\Common\Base\Cart;
use MrPrompt\CaixaEconomicaFederal\Common\Base\Charge;
use MrPrompt\CaixaEconomicaFederal\Common\Base\Customer;
use MrPrompt\CaixaEconomicaFederal\Common\Base\Document;
use MrPrompt\CaixaEconomicaFederal\Common\Base\Occurrence;
use MrPrompt\CaixaEconomicaFederal\Common\Base\Purchaser;
use MrPrompt\CaixaEconomicaFederal\Common\Base\Seller;
use MrPrompt\CaixaEconomicaFederal\Common\Base\Sequence;
use MrPrompt\CaixaEconomicaFederal\Common\Util\Number;
use MrPrompt\CaixaEconomicaFederal\Gateway\Factory;
use MrPrompt\CaixaEconomicaFederal\Gateway\Shipment\Partial\Footer;
use MrPrompt\CaixaEconomicaFederal\Gateway\Shipment\Partial\Header;
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
        /* @var $item \CaixaEconomicaFederal\Gateway\Shipment\Partial\Detail */
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

        /* @var $parcels \CaixaEconomicaFederal\Common\Base\Parcels */
        $parcels = $item->getParcels();

        /* @var $parcel \CaixaEconomicaFederal\Common\Base\Parcel */
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

    /**
     * Read a return file
     *
     * @param string $file
     * @return array [Header, Detail, Footer]
     * @throws \Exception
     */
    public function read($file = null)
    {
        $filename       = $file ? $file : $this->createFilename() . '.RET';
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
