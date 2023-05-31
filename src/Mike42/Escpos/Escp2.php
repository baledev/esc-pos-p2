<?php declare(strict_types=1);

namespace Mike42\Escpos;

use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;

class Escp2 extends Printer
{
    Public $unit = 10 / 3600;

    public $topMargin = 0;

    public $bottomMargin = 0;

    public $leftMargin = 0;

    public $rightMargin = 0;

    /* Setting the page format */
    /**
     * Set page length in inch, must define setUnit() first
     * @param $pageLength in inch (float)
     */
    public function setPageLengthInInch($pageLength)
    {
        $mh = (int)(($pageLength / $this->unit) / 256);
        $ml = ($pageLength / $this->unit) % 256;
        $cmd = self::ESC . "(C" . chr(2) . chr(0) . chr($ml) . chr($mh);
        $this->connector->write($cmd);
    }

    /**
     * Sets the top and bottom margins in inch measured from the top edge of the paper
     * @param $topMargin, $bottomMargin in inch (float)
     */
    public function setPageFormat($topMargin, $bottomMargin)
    {
        $this->topMargin = $topMargin;
        $this->bottomMargin = $bottomMargin;
        $nl = 4;
        $nh = 0;
        $th = (int)(($topMargin / $this->unit) / 256);
        $tl = ($topMargin / $this->unit) % 256;
        $bh = (int)(($bottomMargin / $this->unit) / 256);
        $bl = ($bottomMargin / $this->unit) % 256;
        $cmd = self::ESC . "(c" . chr($nl) . chr($nh) . chr($tl) . chr($th) . chr($bl) . chr($bh);
        $this->connector->write($cmd);
    }

    /**
     * Sets the left and right margins in n column of character in the current character cpi measured from the left of the printable column
     * @param $leftMargin, $rigtMargin in amount of character (1-255)
     */
    public function setLeftRightMargin($leftMargin, $rightMargin)
    {
        $this->leftMargin = $leftMargin;
        $this->rightMargin = $rightMargin;
        $cmd = self::ESC . "l" . chr($leftMargin);
        $this->connector->write($cmd);
        $cmd = self::ESC . "Q" . chr($rightMargin);
        $this->connector->write($cmd);
    }

    /**
     * Sets the page length to n lines in the current line spacing
     * @param $lines | 1 <= $lines <= 127 | 0 < $lines x (current line spacing) <= 22 inch
     */
    public function setPageLengthInLines($lines)
    {
        $cmd = self::ESC . "C" . chr($lines);
        $this->connector->write($cmd);
    }


    /* Moving the print position */
    /**
     * CR Carriage return
     * Moves the print position to the left margin position
     * Print all data in the line buffer
     */
    public function carriageReturn()
    {
        $cmd = "\x0D";
        $this->connector->write($cmd);
    }

    /**
     * LF Line feed
     * Advances the vertical print position one line (in the currently set line spacing)
     * Moves the horizontal print position to the left-margin position
     */
    public function lineFeed()
    {
        $this->carriageReturn();
        $cmd = "\x0A";
        $this->connector->write($cmd);
    }

    /**
     * FF Form feed
     * Advances the vertical print position on continuous paper to the top-margin position of the next page
     * Ejects single-sheet paper
     * Moves the horizontal print position to the left-margin position
     * Prints all data in the buffer
     */
    public function formFeed()
    {
        $this->carriageReturn();
        $cmd = "\x0C";
        $this->connector->write($cmd);
    }

    /**
     * Moves the horizontal print position to the absolute hotizontal position, measured from left margin
     * @param $position in inch (float)
     */
    public function moveAbsoluteHorizontal($position)
    {
        $nh = (int)((($position - $this->leftMargin) / $this->unit) / 256);
        $nl = (($position - $this->leftMargin) / $this->unit) % 256;
        $cmd = self::ESC . "$" . chr($nl) . chr($nh);
        $this->connector->write($cmd);
    }

    /**
     * Moves the vertical print position to the absolute vertical position, measured from top margin
     * @param $position in inch (float)
     */
    public function moveAbsoluteVertical($position)
    {
        $mh = (int)((($position - $this->topMargin) / $this->unit) / 256);
        $ml = (($position - $this->leftMargin) / $this->unit) % 256;
        $cmd = self::ESC . "(V" . chr(2) . chr(0) . chr($ml) . chr($mh);
        $this->connector->write($cmd);
    }

    /**
     * Moves the horizontal print position to the next tab to the right of the current print position
     */
    public function horizontalTab()
    {
        $cmd = "\x09";
        $this->connector->write($cmd);
    }

    /**
     * Moves the vertical print position to the next vertical tab below the current print position
     * Moves the horizontal print position to the left margin position
     */
    public function verticalTab()
    {
        $cmd = "\x0B";
        $this->connector->write($cmd);
    }



    /* Setting the units */
    /**
     * Set Unit in inch, 1 unit = $unit / 3600
     * @param $unit 5, 10, 20, 30, 40, 50, 60
     */
    public function setUnit($unit)
    {
        $this->unit = $unit / 3600;
        $cmd = self::ESC . "(U" . chr(1) . chr(0) . chr($unit);
        $this->connector->write($cmd);
    }

    /**
     * Sets the line spacing 1/8 inch or 1/6 inch
     *
     * @param $spacing 6, 8
     */
    public function setLineSpacing($spacing = 6)
    {
        $cmdx = "2";
        if ($spacing == 8) {
            $cmdx = "0";
        }
        $cmd = self::ESC . $cmdx;
        $this->connector->write($cmd);

        self::validateInteger($spacing, 6, 8, __FUNCTION__);
        $this->connector->write(self::ESC . chr($spacing));
    }

    /**
     * Sets the line spacing to $spacing / 180 inch
     *
     * @param $spacing 1-255
     */
    public function setLineSpacing180($spacing)
    {
        self::validateInteger($spacing, 1, 255, __FUNCTION__);
        $this->connector->write(self::ESC . "3" . chr($spacing));
    }

    /**
     * Sets the line spacing to $spacing / 360 inch
     *
     * @param $spacing 1-255
     */
    public function setLineSpacing360($spacing)
    {
        self::validateInteger($spacing, 1, 255, __FUNCTION__);
        $this->connector->write(self::ESC . "+" . chr($spacing));
    }

    /**
     * Sets the horizontal tab position (in the current character cpi) at the column of the char measured from left margin position
     * Max 32 tabs
     *
     * @param $tabs (array), tabs must be ascending in the array, each tab value 1-255
     */
    public function setHorizontalTab($tabs)
    {
        $tb = "";

        foreach ($tabs as $tab) {
            $tb.= chr($tab);
        }

        $this->connector->write(self::ESC . "D" . $tb . chr(0));
    }

    /**
     * Sets the vertical tab position (in the current line spacing) at the lines measured from top margin position
     * Max 16 tabs
     *
     * @param $tabs (array), tabs must be ascending in the array, each tab value 1-255
     */
    public function setVerticalTab($tabs)
    {
        $tb = "";

        foreach ($tabs as $tab) {
            $tb.= chr($tab);
        }

        $this->connector->write(self::ESC . "B" . $tb . chr(0));
    }

    /**
     * Select either LQ (letter-quality) or draft printing
     *
     * @param $state (boolean)
     */
    public function setLQ(bool $on = true)
    {
        self::validateBoolean($on, __FUNCTION__);
        $this->connector->write(self::ESC . "x" . ($on ? chr(1) : chr(0)));
    }

    /**
     * Select the typeface for LQ printing
     * 0 Roman          7 Orator
     * 1 Sans Serif     8 Orator-S
     * 2 Courier        9 Script C
     * 3 Prestige       10 Roman T
     * 4 Script         11 Sans serif H
     * 5 OCR-B          30 SV Busaba
     * 6 OCR-A          31 SV Jitta
     *
     * @param $type int
     */
    public function setFontTypeface($type)
    {
        self::validateInteger($type, 0, 31, __FUNCTION__);
        $this->connector->write(self::ESC . "k" . chr($type));
    }

    /**
     * Select 3 mode 10.5-point character per inch (cpi)
     *
     * @param $cpi 10, 12, 15
     */
    public function setCharacterCpi($cpi = 10)
    {
        self::validateInteger($cpi, 10, 15, __FUNCTION__);

        if ($cpi == 10)
            $cmd = "P";
        else if ($cpi == 12)
            $cmd = "M";
        else if ($cpi == 15)
            $cmd = "g";
        else
            $cmd = "P";

        $this->connector->write(self::ESC . $cmd);
    }

    /**
     * Turn proportional character spacing on or off
     * Select either proportional or fixed character spacing
     *
     * @param $state (boolean)
     */
    public function setCharacterProportionalMode(bool $on = false)
    {
        self::validateBoolean($on, __FUNCTION__);
        $this->connector->write(self::ESC . 'p' . ($on ? chr(1) : chr(0)));
    }

    /**
     * Turn emphasized mode on/off.
     *
     *  @param boolean $on true for emphasis, false for no emphasis
     */
    public function setEmphasis(bool $on = true)
    {
        self::validateBoolean($on, __FUNCTION__);
        $this->connector->write(self::ESC . ($on ? 'E' : 'F'));
    }

    /**
     * Turn italic mode on/off.
     *
     *  @param boolean $on true for italic, false for no italic
     */
    public function setItalic(bool $on = true)
    {
        self::validateBoolean($on, __FUNCTION__);
        $this->connector->write(self::ESC . ($on ? '4' : '5'));
    }

    /**
     * Set double strike mode on/off
     *
     * @param boolean $on
     */
    public function setDoubleStrike(bool $on = true)
    {
        self::validateBoolean($on, __FUNCTION__);
        $this->connector->write(self::ESC . ($on ? 'G' : 'H'));
    }

    /**
     * Select Underline mode on/off
     *
     * @param int $underline Either true/false, or one of Printer::UNDERLINE_NONE, Printer::UNDERLINE_SINGLE or Printer::UNDERLINE_DOUBLE. Defaults to Printer::UNDERLINE_SINGLE.
     */
    public function setUnderline(int $underline = Printer::UNDERLINE_SINGLE)
    {
        self::validateInteger($underline, 0, 2, __FUNCTION__);
        $this->connector->write(self::ESC . "-" . chr($underline));
    }

    /**
     * Set Condensed mode on/off
     *
     * @param boolean $on
     */
    public function setCondensed(bool $on = true)
    {
        self::validateBoolean($on, __FUNCTION__);
        $this->connector->write(self::ESC . ($on ? '\x0F' : '\x12'));
    }

    /**
     * Set double width mode on/off
     *
     * @param boolean $on
     */
    public function setDoubleWidth(bool $on = true)
    {
        self::validateBoolean($on, __FUNCTION__);
        $this->connector->write(self::ESC . 'W' . ($on ? chr(1) : chr(0)));
    }

    /**
     * Select double height mode on/off
     *
     * @param boolean $on
     */
    public function setDoubleHeight(bool $on = true)
    {
        self::validateBoolean($on, __FUNCTION__);
        $this->connector->write(self::ESC . 'w' . ($on ? chr(1) : chr(0)));
    }

    /* Others */
    /**
     * Print an image, using the older "bit image" command in column format.
     *
     * Should only be used if your printer does not support the graphics() or
     * bitImage() commands.
     *
     * @param EscposImage $img The image to print
     * @param int $size Size modifier for the image. Must be either `Printer::IMG_DEFAULT`
     *  (default), or any combination of the `Printer::IMG_DOUBLE_HEIGHT` and
     *  `Printer::IMG_DOUBLE_WIDTH` flags.
     */
    public function bitImageColumnFormat(EscposImage $img, $size = Printer::IMG_DEFAULT)
    {
        $highDensityVertical = ! (($size & self::IMG_DOUBLE_HEIGHT) == Printer::IMG_DOUBLE_HEIGHT);
        $highDensityHorizontal = ! (($size & self::IMG_DOUBLE_WIDTH) == Printer::IMG_DOUBLE_WIDTH);
        // Experimental column format printing
        // This feature is not yet complete and may produce unpredictable results.
        $this->setLineSpacing360(48); // 16-dot line spacing. This is the correct value on both TM-T20 and TM-U220
        // Header and density code (0, 1, 32, 33) re-used for every line
        $densityCode = ($highDensityHorizontal ? 1 : 0) + ($highDensityVertical ? 32 : 0);
        $colFormatData = $img -> toColumnFormat($highDensityVertical);
        $header = self::dataHeader([$img -> getWidth()], true);
        $densityCode = 39;
        foreach ($colFormatData as $line) {
            // Print each line, double density etc for printing are set here also
            $this->connector->write(self::ESC . "*" . chr($densityCode) . $header . $line);
            $this -> lineFeed();
            // sleep(0.1); // Reduces the amount of trouble that a TM-U220 has keeping up with large images
        }
        $this->setLineSpacing(); // Revert to default line spacing
    }


    /**
     * Convert widths and heights to characters. Used before sending graphics to set the size.
     *
     * @param array $inputs
     * @param boolean $long True to use 4 bytes, false to use 2
     * @return string
     */
    protected static function dataHeader(array $inputs, $long = true)
    {
        $outp = [];
        foreach ($inputs as $input) {
            if ($long) {
                $outp[] = self::intLowHigh($input, 2);
            } else {
                self::validateInteger($input, 0, 255, __FUNCTION__);
                $outp[] = chr($input);
            }
        }

        return implode("", $outp);
    }

    /**
     * Generate two characters for a number: In lower and higher parts, or more parts as needed.
     *
     * @param int $input Input number
     * @param int $length The number of bytes to output (1 - 4).
     */
    protected static function intLowHigh($input, $length)
    {
        $maxInput = (256 << ($length * 8) - 1);
        self::validateInteger($length, 1, 4, __FUNCTION__);
        self::validateInteger($input, 0, $maxInput, __FUNCTION__);
        $outp = "";
        for ($i = 0; $i < $length; $i++) {
            $outp .= chr($input % 256);
            $input = (int)($input / 256);
        }

        return $outp;
    }
}
