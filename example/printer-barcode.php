<?php

require __DIR__ . '/../vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;

function printTexts($texts, $maxHeight, $marginLeft, $marginTop)
{
    $printTexts = array();
    $y = ($maxHeight / 2) - ($texts->height / 2) + $marginTop;
    $x = $marginLeft;
    foreach ($texts->lines as $line) {
        $printTexts[] = "TEXT " . $x + $line->x . "," . $y + $line->y . ",\"1\",0,1,1,\"" . $line->word . "\"\n";
    }

    // dd(config("app.text.{$type}.x"));
    //dd($printTexts);
    return $printTexts;
}

function getTexts($name, $maxWidth, $lineSpace)
{
    $fontX = 8;
    $fontY = 12;
    $fontX += 2;
    $maxChar = $maxWidth / $fontX;
    $words = explode(" ", $name);
    $textLines = array();
    $buff = "";
    for ($i = 0; $i < count($words); $i++) {
        if (strlen($buff . " " . $words[$i]) > $maxChar) {
            $textLines[] = $buff;
            $buff = $words[$i];
        } else {
            if ($i == 0)
                $buff .= $words[$i];
            else
                $buff .= " " . $words[$i];
        }
        if ($i == count($words) - 1)
            $textLines[] = $buff;
    }
    //dd($textLines);
    $texts = (object)array();
    for ($i = 0; $i < count($textLines); $i++) {
        $text = (object)array();
        $text->word = $textLines[$i];
        $text->x = ($maxWidth / 2) - ((strlen($textLines[$i]) * $fontX) / 2);
        if ($i == 0)
            $text->y = ($i * $fontY);
        else
            $text->y = ($i * $fontY) + ($i * $lineSpace);
        $texts->lines[] = $text;
        if ($i == count($textLines) - 1)
            $texts->height = $text->y + $fontY;
    }
    //dd($texts);
    return $texts;
}


// try {

    $name = "REDMI NOTE 7 [I]";
    $barcode = "00267533";
    $copies = 2;

    $sizeX = 30; //mm
    $sizeY = 20; //mm
    $marginLeft = 10; //dot
    $marginRight = 10; //dot
    $marginTop = 10; //dot
    $lineSpace = 2; //dot
    $textMaxWidth = ($sizeX * 8) - $marginLeft - $marginRight; // 200DPI = *8
    $textMaxHeight = 75 - $marginTop - $lineSpace; // 75 start Y barcode
    //dd($textMaxWidth);
    $texts = getTexts($name, $textMaxWidth, $lineSpace);
    $printTexts = printTexts($texts, $textMaxHeight, $marginLeft, $marginTop);
    //dd($printTexts);
    // Enter the share name for your USB printer here
    //$connector = null;
    // $connector = new WindowsPrintConnector("Xprinter XP-370B");
    /* Print a "Hello world" receipt" */
    // $printer = new Printer($connector);
    $connector = new FilePrintConnector("php://stdout");
    $printer = new Printer($connector);

    $printer->text("\n");
    $printer->text("SIZE " . $sizeX . " mm, " . $sizeY . " mm\n");
    $printer->text("GAP 2 mm,0 mm\n");
    $printer->text("DENSITY 8\n");
    $printer->text("DIRECTION 1,0\n");
    $printer->text("REFERENCE 0,0\n");
    $printer->text("OFFSET 0 mm\n");
    $printer->text("SET PEEL OFF\n");
    $printer->text("SET TEAR ON\n");
    $printer->text("CLS\n");
    foreach ($printTexts as $printText) {
        $printer->text($printText);
    }
    $printer->text("BARCODE 17,75,\"EAN8\",55,1,0,3,6,\"" . $barcode . "\"\n");
    //$printer -> text("BACKUP 160\n");
    $printer->text("PRINT 1," . $copies . "\n");



    //$printer -> text("PRINT 1\n");

    /* Close printer */
    $printer->close();
// } catch (Exception $e) {
//     echo "Couldn't print to this printer: " . $e->getMessage() . "\n";
// }
