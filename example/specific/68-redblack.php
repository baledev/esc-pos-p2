<?php
/*
 * Example of two-color printing, tested on an epson TM-U220 with two-color ribbon installed.
 */
require __DIR__ . '/../../vendor/autoload.php';
use Baledev\Escposp2\Printer;
use Baledev\Escposp2\PrintConnectors\FilePrintConnector;

$connector = new FilePrintConnector("/dev/usb/lp0");
$printer = new Printer($connector);
try {
    $printer -> text("Hello World!\n");
    $printer -> setColor(Printer::COLOR_2);
    $printer -> text("Red?!\n");
    $printer -> setColor(Printer::COLOR_1);
    $printer -> text("Default color again?!\n");
    $printer -> cut();
} finally {
    /* Always close the printer! */
    $printer -> close();
}
