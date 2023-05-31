<?php
require __DIR__ . '/../../vendor/autoload.php';
use Baledev\Escposp2\CapabilityProfile;
use Baledev\Escposp2\Printer;
use Baledev\Escposp2\PrintConnectors\FilePrintConnector;
use Baledev\Escposp2\PrintBuffers\ImagePrintBuffer;

/* This example shows the printing of Latvian text on the Star TUP 592 printer */
$profile = CapabilityProfile::load("SP2000");

/* Option 1: Native character encoding */
$connector = new FilePrintConnector("php://stdout");
$printer = new Printer($connector, $profile);
$printer -> text("Glāžšķūņa rūķīši dzērumā čiepj Baha koncertflīģeļu vākus\n");
$printer -> cut();
$printer -> close();

/* Option 2: Image-based output (formatting not available using this output) */
$buffer = new ImagePrintBuffer();
$connector = new FilePrintConnector("php://stdout");
$printer = new Printer($connector, $profile);
$printer -> setPrintBuffer($buffer);
$printer -> text("Glāžšķūņa rūķīši dzērumā čiepj Baha koncertflīģeļu vākus\n");
$printer -> cut();
$printer -> close();
