<?php

require __DIR__ . '/../vendor/autoload.php';

use Baledev\Escposp2\PrintConnectors\FilePrintConnector;
use Baledev\Escposp2\Printer;
use Baledev\Escposp2\Experimental\Unifont\UnifontPrintBuffer;

$connector = new FilePrintConnector("php://stdout");
$printer = new Printer($connector);

// Use Unifont to render text
$unifontBuffer = new UnifontPrintBuffer("/usr/share/unifont/unifont.hex");
$printer -> setPrintBuffer($unifontBuffer);

// Most simple example
$printer->text("Hello\n");
$printer->setUpsideDown(true);
$printer->text("World\n");
$printer->cut();
$printer->close();

