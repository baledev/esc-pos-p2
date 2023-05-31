<?php

$php = __DIR__ . '/printer-barcode.php';
$output = __DIR__ . '/foo.txt';

$file = basename($php);
$file_output = basename($output);

shell_exec("php {$file} > {$file_output}");
shell_exec("lpr -o raw -H localhost -P XP370B {$file_output}");