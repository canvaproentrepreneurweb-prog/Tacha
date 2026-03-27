<?php
// Minimal PDF builder for one image page (A4 portrait) compatible with this MVP.
class FPDF
{
    private string $imagePath = '';

    public function AddPage(): void
    {
        // no-op for this minimal implementation
    }

    public function Image(string $path, float $x = 0, float $y = 0, float $w = 190, float $h = 0): void
    {
        $this->imagePath = $path;
    }

    public function Output(string $dest = 'I', string $name = 'ticket.pdf'): void
    {
        if (!is_file($this->imagePath)) {
            throw new RuntimeException('Image source missing for PDF generation.');
        }

        $jpg = file_get_contents($this->imagePath);
        $info = getimagesize($this->imagePath);
        if (!$jpg || !$info) {
            throw new RuntimeException('Invalid image for PDF generation.');
        }

        $imgW = (int) $info[0];
        $imgH = (int) $info[1];
        $pageW = 595;
        $pageH = 842;

        $ratio = min($pageW / $imgW, $pageH / $imgH);
        $drawW = $imgW * $ratio;
        $drawH = $imgH * $ratio;
        $x = ($pageW - $drawW) / 2;
        $y = ($pageH - $drawH) / 2;

        $content = sprintf("q %.2f 0 0 %.2f %.2f %.2f cm /Im0 Do Q", $drawW, $drawH, $x, $y);

        $objects = [];
        $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj";
        $objects[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj";
        $objects[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 $pageW $pageH] /Resources << /XObject << /Im0 4 0 R >> >> /Contents 5 0 R >> endobj";
        $objects[] = "4 0 obj << /Type /XObject /Subtype /Image /Width $imgW /Height $imgH /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($jpg) . " >> stream\n" . $jpg . "\nendstream endobj";
        $objects[] = "5 0 obj << /Length " . strlen($content) . " >> stream\n" . $content . "\nendstream endobj";

        $pdf = "%PDF-1.3\n";
        $offsets = [0];
        foreach ($objects as $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= $obj . "\n";
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i]) . "\n";
        }
        $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n$xrefPos\n%%EOF";

        if ($dest === 'F') {
            file_put_contents($name, $pdf);
            return;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($name) . '"');
        echo $pdf;
    }
}
?>

