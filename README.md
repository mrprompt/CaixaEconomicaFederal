Caixa Econômica Federal
=======================

[![Build Status](https://travis-ci.org/mrprompt/CaixaEconomicaFederal.svg?branch=master)](https://travis-ci.org/mrprompt/CaixaEconomicaFederal)
[![Code Climate](https://codeclimate.com/github/mrprompt/CaixaEconomicaFederal/badges/gpa.svg)](https://codeclimate.com/github/mrprompt/CaixaEconomicaFederal)
[![Test Coverage](https://codeclimate.com/github/mrprompt/CaixaEconomicaFederal/badges/coverage.svg)](https://codeclimate.com/github/mrprompt/CaixaEconomicaFederal/coverage)
[![Issue Count](https://codeclimate.com/github/mrprompt/CaixaEconomicaFederal/badges/issue_count.svg)](https://codeclimate.com/github/mrprompt/CaixaEconomicaFederal)

Geração de Boletos, Bloquetos e arquivos de Remessa CNAB240 - SIGCB para a Caixa Econômica Federal.

## Dependências

- PHP 5.6+
- [WkHtmlToPDF](http://wkhtmltopdf.org/)

## Instalação

```
composer.phar require mrprompt/CaixaEconomicaFederal
```
    
## Exemplos
Os exemplos estão na pasta *samples*.

Descrição dos exemplos

    - samples/cart.php          - Array com os parâmetros necessários para cada tipo de transação
    - samples/bloqueto.php      - Geração de bloqueto de cobrança (já gera o arquivo de remessa)
    - samples/boleto.php        - Geração de boleto de cobrança (já gera o arquivo de remessa)
    - samples/remessa.php       - Geração de arquivo de remessa CNAB240 - SIGCB
    - samples/retorno.php       - Leitura do arquivo de retorno CNAB240 - SIGCB

## Geração de PDF
A biblioteca requer a instalação da ferramenta [WkHtmlToPDF](http://wkhtmltopdf.org/) e que o path da mesma esteja 
em */usr/local/bin/wkhtmltopdf*, caso contrário, o PDf não poderá ser gerado.

## Contribuindo

### Instalação
Após baixar o composer, basta rodar o *install*

```
composer.phar install --prefer-dist -o
```

**Importante**

Caso o executável para geração do PDF não seja encontrado, nenhum erro é emitido.
