<?php
/**
 * This file is part of FPDI PDF-Parser
 *
 * @package   setasign\FpdiPdfParser
 * @copyright Copyright (c) 2017 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   FPDI PDF-Parser Commercial Developer License Agreement (see LICENSE file within this package)
 * @version   2.0.0
 */

namespace setasign\FpdiPdfParser\PdfParser\Filter;

use setasign\Fpdi\PdfParser\Filter\FilterException;

/**
 * Exception for predictor filter class
 *
 * @package setasign\Fpdi\FpdiPdfParser\Filter
 */
class PredictorException extends FilterException
{
    /**
     * @var int
     */
    const UNRECOGNIZED_PNG_PREDICTOR = 1;

    /**
     * @var int
     */
    const UNRECOGNIZED_PREDICTOR = 2;
}
