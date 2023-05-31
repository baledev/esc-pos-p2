<?php
require __DIR__ . '/../../vendor/autoload.php';
use Baledev\Escposp2\Printer;
use Baledev\Escposp2\PrintConnectors\FilePrintConnector;
use Baledev\Escposp2\CapabilityProfile;

$connector = new FilePrintConnector("php://stdout");
$profile = CapabilityProfile::load("default");
$printer = new Printer($connector, $profile);

$printer -> text("Μιχάλης Νίκος\n");
$printer -> cut();
$printer -> close();

?>
