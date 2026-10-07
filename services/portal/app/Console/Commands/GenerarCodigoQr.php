<?php

declare(strict_types=1);

namespace App\Console\Commands;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Console\Command;

class GenerarCodigoQr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portal:qr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera los archivos estáticos qr.svg y qr.pdf (A6) para cartelería y pegatinas';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $domain = (string) config('proyecto.dominio');
        $nombre = (string) config('proyecto.nombre');
        $url = 'https://' . $domain;

        $this->info("Generando códigos QR para: {$url}");

        // 1. Opciones comunes del código QR
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel' => QRCode::ECC_H,
            'imageBase64' => false,
            'addQuietzone' => true,
        ]);

        $qrcode = new QRCode($options);

        // 2. Generar qr.svg
        $svgContent = $qrcode->render($url);
        $svgPath = public_path('qr.svg');
        file_put_contents($svgPath, $svgContent);
        $this->info("Archivo SVG generado en: {$svgPath} (" . strlen($svgContent) . " bytes)");

        // 3. Generar qr.pdf (Formato A6: 105 mm x 148 mm -> 297.64 pt x 419.53 pt)
        $matrix = $qrcode->getQRMatrix($url);
        $pdfContent = $this->generarPdfA6($matrix, $nombre, $url);
        $pdfPath = public_path('qr.pdf');
        file_put_contents($pdfPath, $pdfContent);
        $this->info("Archivo PDF generado en: {$pdfPath} (" . strlen($pdfContent) . " bytes)");

        return Command::SUCCESS;
    }

    /**
     * Genera un PDF 1.4 vectorial A6 puro en memoria sin dependencias externas pesadas.
     */
    protected function generarPdfA6(QRMatrix $matrix, string $nombre, string $url): string
    {
        $pageW = 297.64; // A6 ancho en pt
        $pageH = 419.53; // A6 alto en pt

        $stream = "0 0 0 rg\n";

        // Título del proyecto
        $stream .= "BT\n/F1 18 Tf\n";
        $titleX = max(20.0, ($pageW - (strlen($nombre) * 9.5)) / 2);
        $stream .= sprintf("1 0 0 1 %.2f 365 Tm\n(%s) Tj\nET\n", $titleX, addcslashes($nombre, "()\\"));

        // Subtítulo
        $sub = 'Red regional comunitaria LoRa Meshtastic';
        $stream .= "BT\n/F2 9 Tf\n";
        $subX = max(15.0, ($pageW - (strlen($sub) * 4.8)) / 2);
        $stream .= sprintf("1 0 0 1 %.2f 345 Tm\n(%s) Tj\nET\n", $subX, addcslashes($sub, "()\\"));

        // Matriz del QR centrada
        $matrixSize = $matrix->getSize();
        $qrPixelSize = 200.0;
        $moduleSize = $qrPixelSize / $matrixSize;
        $originX = ($pageW - $qrPixelSize) / 2;
        $originY = 120.0;

        for ($y = 0; $y < $matrixSize; $y++) {
            for ($x = 0; $x < $matrixSize; $x++) {
                if ($matrix->check($x, $y)) {
                    $posX = $originX + ($x * $moduleSize);
                    $posY = $originY + (($matrixSize - 1 - $y) * $moduleSize);
                    $stream .= sprintf("%.2f %.2f %.2f %.2f re f\n", $posX, $posY, $moduleSize + 0.1, $moduleSize + 0.1);
                }
            }
        }

        // URL legible debajo del QR
        $stream .= "BT\n/F1 11 Tf\n";
        $urlX = max(15.0, ($pageW - (strlen($url) * 6.2)) / 2);
        $stream .= sprintf("1 0 0 1 %.2f 90 Tm\n(%s) Tj\nET\n", $urlX, addcslashes($url, "()\\"));

        // Pie
        $pie = 'Accede para ver el mapa en vivo y configurar tu nodo';
        $stream .= "BT\n/F2 8 Tf\n";
        $pieX = max(10.0, ($pageW - (strlen($pie) * 4.2)) / 2);
        $stream .= sprintf("1 0 0 1 %.2f 70 Tm\n(%s) Tj\nET\n", $pieX, addcslashes($pie, "()\\"));

        $len = strlen($stream);
        $objs = [];
        $objs[1] = '<</Type/Catalog/Pages 2 0 R>>';
        $objs[2] = '<</Type/Pages/Kids[3 0 R]/Count 1>>';
        $objs[3] = sprintf(
            '<</Type/Page/Parent 2 0 R/MediaBox[0 0 %.2f %.2f]/Resources<</Font<</F1 4 0 R/F2 5 0 R>>>>/Contents 6 0 R>>',
            $pageW,
            $pageH
        );
        $objs[4] = '<</Type/Font/Subtype/Type1/BaseFont/Helvetica-Bold>>';
        $objs[5] = '<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>';
        $objs[6] = "<</Length {$len}>>\nstream\n" . $stream . "endstream";

        $out = "%PDF-1.4\n";
        $offsets = [0];
        for ($i = 1; $i <= 6; $i++) {
            $offsets[$i] = strlen($out);
            $out .= "{$i} 0 obj\n" . $objs[$i] . "\nendobj\n";
        }
        $xrefOffset = strlen($out);
        $out .= "xref\n0 7\n0000000000 65535 f \n";
        for ($i = 1; $i <= 6; $i++) {
            $out .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $out .= "trailer\n<</Size 7/Root 1 0 R>>\nstartxref\n{$xrefOffset}\n%%EOF\n";

        return $out;
    }
}
