<?php
/**
 * This file is part of FPDI PDF-Parser
 *
 * @package   setasign\FpdiPdfParser
 * @copyright Copyright (c) 2017 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   FPDI PDF-Parser Commercial Developer License Agreement (see LICENSE.txt file within this package)
 * @version   2.0.0
 */

spl_autoload_register(function ($class) {
    if (strpos($class, 'setasign\\FpdiPdfParser\\') === 0) {
        $filename = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 23)) . '.php';
        $fullpath = __DIR__ . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($fullpath)) {
            /** @noinspection PhpIncludeInspection */
            require_once $fullpath;
        }
    }
});
