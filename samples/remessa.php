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
use MrPrompt\CaixaEconomicaFederal\Gateway\Shipment\File;
use MrPrompt\CaixaEconomicaFederal\Gateway\Factory;

require __DIR__ . '/../bootstrap.php';

/* @var $today \DateTime */
$today      = new DateTime();

/* @var $cart \CaixaEconomicaFederal\Common\Base\Cart */
$cart       = new Cart();

/* @var $row array */
$row      = require __DIR__ . '/cart.php';

foreach ($row as $linha) {
    if ($linha['cobranca'] !== \CaixaEconomicaFederal\Common\Base\Charge::BILLET) {
        continue;
    }

    /* @var $item \CaixaEconomicaFederal\Gateway\Shipment\Partial\Detail */
    $item = Factory::createDetailFromArray($linha);

    echo 'Tipo     : ', $item->getCharge()->getCharging(), PHP_EOL;
    echo 'Comprador: ', $item->getPurchaser()->getName(), PHP_EOL;
    echo 'Parcelas : ', PHP_EOL;

    foreach ($item->getParcels() as $parcel) {
        echo '      # ', $parcel->getKey(), PHP_EOL;
        echo '     R$ ', number_format($parcel->getPrice(), 2, ',', '.'), PHP_EOL;
        echo '    Qtd ', $parcel->getQuantity(), PHP_EOL;
    }

    echo PHP_EOL, PHP_EOL;

    $cart->addItem($item);
}

try {
    /* @var $sequence \CaixaEconomicaFederal\Common\Base\Sequence */
    $sequence   = new Sequence(1);

    /* @var $customer \CaixaEconomicaFederal\Common\Base\Customer */
    $customer   = Factory::createCustomerFromArray(array_pop($row));

    /* @var $exporter \CaixaEconomicaFederal\Gateway\Shipment\File */
    $exporter   = new File($customer, $sequence, $today, __DIR__ . DIRECTORY_SEPARATOR . 'enviados');
    $exporter->setCart($cart);

    $file = $exporter->save();

    echo sprintf('Arquivo %s gerado com sucesso no diretório %s', basename($file), dirname($file)), PHP_EOL;
} catch (\InvalidArgumentException $ex) {
    echo sprintf('Erro: %s in file: %s - line: %s', $ex->getMessage(), $ex->getFile(), $ex->getLine()), PHP_EOL;
}