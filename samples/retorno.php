<?php
/**
 * Exemplo de uso
 *
 * Leitura do arquivo de lote
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 */
use MrPrompt\CaixaEconomicaFederal\Factory;
use MrPrompt\CaixaEconomicaFederal\Received\File;
use MrPrompt\ShipmentCommon\Base\Cart;
use MrPrompt\ShipmentCommon\Base\Sequence;

require __DIR__ . '/bootstrap.php';

try {
    /* @var $date \DateTime */
    $date       = new DateTime();

    /* @var $sequence \MrPrompt\ShipmentCommon\Base\Sequence */
    $sequence   = new Sequence(784);

    /* @var $customer \MrPrompt\ShipmentCommon\Base\Customer */
    $customer   = Factory::createCustomerFromArray(['cliente' => 759, 'identificador' => 39282]);

    /* @var $importer \MrPrompt\CaixaEconomicaFederal\Received\File */
    $importer   = new File($customer, $sequence, $date, __DIR__ . '/recebidos');
    $importer->setCart( new Cart() );

    // importing file data
    $result     = $importer->read('000759_27082015_00001.RET');

    /* @var \MrPrompt\ShipmentCommon\Base\Cart */
    $cart       = $importer->getCart();

    /* @var $lista array */
    $lista      = [];

    /* @var $item \MrPrompt\CaixaEconomicaFederal\Shipment\Partial\Detail */
    foreach ($cart as $item) {
        echo 'Tipo       : ', $item->getCharge()->getCharging(), PHP_EOL;
        echo 'Autorização: ', $item->getAuthorization()->getNumber(), PHP_EOL;

        /* @var $parcel \MrPrompt\ShipmentCommon\Base\Parcel */
        foreach ($item->getParcels() as $parcel) {
            echo 'Valor      : R$ ', number_format($parcel->getPrice(), 2, ',', '.'), PHP_EOL;
            echo 'Vencimento : ', $parcel->getMaturity() ? $parcel->getMaturity()->format('d/m/Y') : 'Não disponível', PHP_EOL;
            echo 'Situação   : ', $parcel->getStatus() ? 'PAGO' : 'ABERTO', PHP_EOL;
        }

        echo PHP_EOL;
    }
} catch (\InvalidArgumentException $ex) {
    echo sprintf('Erro: %s in file: %s - line: %s', $ex->getMessage(), $ex->getFile(), $ex->getLine()), PHP_EOL;
}
