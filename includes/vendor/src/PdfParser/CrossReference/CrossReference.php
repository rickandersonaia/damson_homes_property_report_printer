<?php
/**
 * This file is part of FPDI PDF-Parser
 *
 * @package   setasign\FpdiPdfParser
 * @copyright Copyright (c) 2017 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   FPDI PDF-Parser Commercial Developer License Agreement (see LICENSE.txt file within this package)
 * @version   2.0.0
 */

namespace setasign\FpdiPdfParser\PdfParser\CrossReference;

use setasign\Fpdi\PdfParser\CrossReference\CrossReference as FpdiCrossReference;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\CrossReference\ReaderInterface;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfStream;
use setasign\Fpdi\PdfParser\Type\PdfToken;
use setasign\Fpdi\PdfParser\Type\PdfType;
use setasign\FpdiPdfParser\PdfParser\PdfParser;

/**
 * Class CrossReference
 *
 * This class also supports reading of compressed cross-references and object streams.
 *
 * @package setasign\FpdiPdfParser\PdfParser\CrossReference
 */
class CrossReference extends FpdiCrossReference
{
    /**
     * @var PdfParser
     */
    protected $parser;

    /**
     * Data of object streams.
     *
     * @var array
     */
    protected $objectStreams = [];

    /**
     * @var CompressedReader[]
     */
    protected $compressedXrefs = [];

    /**
     * Get the offset by an object number.
     *
     * @param int $objectNumber
     * @return integer|array|bool
     */
    public function getOffsetFor($objectNumber)
    {
        foreach ($this->getReaders() as $key => $reader) {
            $offset = $reader->getOffsetFor($objectNumber);
            if (false !== $offset) {
                return $offset;
            }

            // handle hybrid files
            if (!isset($this->compressedXrefs[$key])) {
                $trailer = $reader->getTrailer();
                if (!($reader instanceof CompressedReader) && isset($trailer->value['XRefStm'])) {
                    $this->parser->getStreamReader()->reset(PdfNumeric::ensure($trailer->value['XRefStm'])->value);
                    $this->parser->getTokenizer()->clearStack();

                    $this->compressedXrefs[$key] = new CompressedReader(
                        $this->parser,
                        PdfStream::ensure(PdfType::resolve($this->parser->readValue(), $this->parser))
                    );
                } else {
                    $this->compressedXrefs[$key] = false;
                }
            }

            if ($this->compressedXrefs[$key] instanceof CompressedReader) {
                /** @var CompressedReader $compressedXref */
                $compressedXref = $this->compressedXrefs[$key];
                $offset = $compressedXref->getOffsetFor($objectNumber);
                if (false !== $offset) {
                    return $offset;
                }
            }
        }

        return false;
    }

    /**
     * Get a cross-reference reader instance.
     *
     * @param PdfToken|PdfIndirectObject $initValue
     * @return ReaderInterface|bool
     * @throws CrossReferenceException
     */
    protected function initReaderInstance($initValue)
    {
        try {
            return parent::initReaderInstance($initValue);

        } catch (CrossReferenceException $e) {
            if ($e->getCode() === CrossReferenceException::ENCRYPTED) {
                throw $e;
            }

            if ($e->getCode() !== CrossReferenceException::COMPRESSED_XREF) {
                return new CorruptedReader($this->parser);
            }

            $stream = PdfStream::ensure(PdfType::resolve($initValue, $this->parser));
            return new CompressedReader($this->parser, $stream);
        }
    }

    /**
     * Get an indirect object by its object number.
     *
     * @param int $objectNumber
     * @return PdfIndirectObject
     * @throws CrossReferenceException
     */
    public function getIndirectObject($objectNumber)
    {
        $offset = $this->getOffsetFor($objectNumber);
        if (false === $offset) {
            throw new CrossReferenceException(
                sprintf('Object (id:%s) not found.', $objectNumber),
                CrossReferenceException::OBJECT_NOT_FOUND
            );
        }

        $parser = $this->parser;
        $parser->getTokenizer()->clearStack();

        // handle standard cross-references
        if (is_int($offset)) {
            $parser->getStreamReader()->reset($offset + $this->fileHeaderOffset);

            $object = $parser->readValue();
            if (false === $object || !($object instanceof PdfIndirectObject)) {
                throw new CrossReferenceException(
                    sprintf('Object (id:%s) not found at location (%s).', $objectNumber, $offset),
                    CrossReferenceException::OBJECT_NOT_FOUND
                );
            }

        // handle compressed object streams
        } else {
            list($targetObjectNumber) = $offset;
            if (isset($this->objectStreams[$targetObjectNumber])) {
                /**
                 * @var array $streamOffsets
                 * @var PdfParser $objectStreamParser
                 */
                extract($this->objectStreams[$targetObjectNumber]);

            } else {
                $objectStream = $this->getIndirectObject($targetObjectNumber);
                /** @var PdfStream $stream */
                $stream = PdfType::resolve($objectStream, $this->parser);
                $objectStreamParser = new PdfParser(StreamReader::createByString($stream->getUnfilteredStream()));
                $dict = $stream->value;
                $firstPos = $dict->value['First']->value;
                $count = $dict->value['N']->value;

                $streamOffsets = [];
                for ($i = 0; $i < $count; $i++) {
                    $_objectNumber = PdfNumeric::ensure($objectStreamParser->readValue())->value;
                    $streamOffsets[$_objectNumber] = (
                        PdfNumeric::ensure($objectStreamParser->readValue())->value + $firstPos
                    );
                }

                $this->objectStreams[$targetObjectNumber] = [
                    'streamOffsets' => $streamOffsets,
                    'objectStreamParser' => $objectStreamParser
                ];
            }

            $objectStreamParser->getStreamReader()->reset($streamOffsets[$objectNumber]);
            $objectStreamParser->getTokenizer()->clearStack();
            $value = $objectStreamParser->readValue();
            if (false === $value) {
                throw new CrossReferenceException(
                    sprintf('Object (id:%s) not found in object stream (id:%s).', $objectNumber, $offset[0]),
                    CrossReferenceException::OBJECT_NOT_FOUND
                );
            }

            $object = PdfIndirectObject::create($objectNumber, 0, $value);
        }

        if ($object->objectNumber !== $objectNumber) {
            throw new CrossReferenceException(
                sprintf('Wrong object found, got %s while %s was expected.', $object->objectNumber, $objectNumber),
                CrossReferenceException::OBJECT_NOT_FOUND
            );
        }

        return $object;
    }
}
