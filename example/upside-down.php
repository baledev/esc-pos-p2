<?php
/* Demonstration of upside-down printing */
require __DIR__ . '/../vendor/autoload.php';
use Baledev\Escposp2\Printer;
use Baledev\Escposp2\PrintConnectors\FilePrintConnector;

$connector = new FilePrintConnector("php://stdout");
$printer = new Printer($connector);

// Most simple example
$printer -> text("Hello\n");
$printer -> setUpsideDown(true);
$printer -> text("World\n");
$printer -> cut();
$printer -> close();

