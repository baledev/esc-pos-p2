<?php
/* Change to the correct path if you copy this example! */
require __DIR__ . '/../../vendor/autoload.php';
use Baledev\Escposp2\Printer;
use Baledev\Escposp2\PrintConnectors\CupsPrintConnector;

try {
    $connector = new CupsPrintConnector("EPSON_TM-T20");
    
    /* Print a "Hello world" receipt" */
    $printer = new Printer($connector);
    $printer -> text("Hello World!\n");
    $printer -> cut();
    
    /* Close printer */
    $printer -> close();
} catch (Exception $e) {
    echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
}
