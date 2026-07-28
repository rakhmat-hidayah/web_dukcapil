<?php

namespace App\Contracts;

interface OcrEngineInterface
{
    /**
     * Extract tabular statistical data from an uploaded image or document file.
     *
     * @param string $filePath
     * @return array Contains 'status', 'confidence', 'detected_type', 'rows' => [['label', 'value', 'confidence']]
     */
    public function extractTableData(string $filePath): array;
}
