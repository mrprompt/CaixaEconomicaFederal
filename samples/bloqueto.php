<?php
/**
 * Exemplo de uso
 *
 * Criação do arquivo de cadastro
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 */
use MrPrompt\CaixaEconomicaFederal\Common\Base\Cart;
use MrPrompt\CaixaEconomicaFederal\Common\Base\Sequence;
use MrPrompt\CaixaEconomicaFederal\Common\Base\Charge;
use MrPrompt\CaixaEconomicaFederal\Shipment\PaymentSlip;
use MrPrompt\CaixaEconomicaFederal\Factory;

require __DIR__ . '/bootstrap.php';

/* @var $today \DateTime */
$today      = new DateTime();

/* @var $cart \MrPrompt\CaixaEconomicaFederal\Common\Base\Cart */
$cart       = new Cart();

/* @var $row array */
$row      = require __DIR__ . '/cart.php';

foreach ($row as $linha) {
    if ($linha['cobranca'] !== Charge::PAYMENT_SLIP) {
        continue;
    }

    /* @var $item \MrPrompt\CaixaEconomicaFederal\Shipment\Partial\Detail */
    $item = Factory::createDetailFromArray($linha);

    echo 'Tipo     : ', $item->getCharge()->getCharging(), PHP_EOL;
    echo 'Comprador: ', $item->getPurchaser()->getName(), PHP_EOL;
    echo 'Parcelas : ', PHP_EOL;

    /* @var $parcel \MrPrompt\CaixaEconomicaFederal\Common\Base\Parcel */
    foreach ($item->getParcels() as $parcel) {
        echo '      # ', $parcel->getKey(), PHP_EOL;
        echo '     R$ ', number_format($parcel->getPrice(), 2, ',', '.'), PHP_EOL;
        echo '    Qtd ', $parcel->getQuantity(), PHP_EOL;
    }

    echo PHP_EOL, PHP_EOL;

    $cart->addItem($item);
}

try {
    /* @var $sequence \MrPrompt\CaixaEconomicaFederal\Common\Base\Sequence */
    $sequence   = new Sequence(1);

    /* @var $customer \MrPrompt\CaixaEconomicaFederal\Common\Base\Customer */
    $customer   = Factory::createCustomerFromArray(array_shift($row));

    /* @var $exporter \MrPrompt\CaixaEconomicaFederal\Shipment\File */
    $exporter   = new PaymentSlip($customer, $sequence, $today, __DIR__ . DIRECTORY_SEPARATOR . 'bloquetos');
    $exporter->setCart($cart);

    $file = $exporter->save();

    echo sprintf('Arquivo %s gerado com sucesso no diretório %s', basename($file), dirname($file)), PHP_EOL;
} catch (\InvalidArgumentException $ex) {
    echo sprintf('Erro: %s in file: %s - line: %s', $ex->getMessage(), $ex->getFile(), $ex->getLine()), PHP_EOL;
}