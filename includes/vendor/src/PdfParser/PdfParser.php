<?php
/**
 * This file is part of FPDI PDF-Parser
 *
 * @package   setasign\FpdiPdfParser
 * @copyright Copyright (c) 2017 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   FPDI PDF-Parser Commercial Developer License Agreement (see LICENSE.txt file within this package)
 * @version   2.0.0
 */

namespace setasign\FpdiPdfParser\PdfParser;

use setasign\FpdiPdfParser\PdfParser\CrossReference\CrossReference;

/**
 * A PDF parser class
 *
 * @package setasign\FpdiPdfParser\PdfParser
 */
class PdfParser extends \setasign\Fpdi\PdfParser\PdfParser
{
    /**
     * Get the cross reference instance.
     *
     * @return CrossReference
     */
    public function getCrossReference()
    {
        if (null === $this->xref) {
            $this->xref = new CrossReference($this, $this->resolveFileHeader());
        }

        return $this->xref;
    }
}
