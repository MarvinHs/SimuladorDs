<?php
namespace App;

use Smalot\PdfParser\Parser;
use Exception;

class PdfExtractor {
    private string $filePath;

    public function __construct(string $filePath) {
        $this->filePath = $filePath;
    }

    public function extractText(): string {
        if (!file_exists($this->filePath)) {
            throw new Exception("El archivo PDF no existe.");
        }

        $parser = new Parser();
        $pdf = $parser->parseFile($this->filePath);
        $text = $pdf->getText();

        // Normalizar codificación UTF-8
        return mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    }
}