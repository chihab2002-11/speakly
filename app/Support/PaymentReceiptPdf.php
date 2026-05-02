<?php

namespace App\Support;

use App\Models\TuitionPayment;
use App\Models\User;

class PaymentReceiptPdf
{
    private const PAGE_WIDTH = 283.46;

    private const PAGE_HEIGHT = 425.20;

    private const PAGE_MARGIN = 16;

    /**
     * @param  array<string, mixed>  $financialSummary
     */
    public function render(User $student, TuitionPayment $payment, array $financialSummary): string
    {
        $reference = $payment->reference ?: 'PAY-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT);
        $discountPercent = (int) ($financialSummary['scholarshipDiscount'] ?? 0);
        $discountAmount = (int) ($financialSummary['discountAmount'] ?? 0);
        $discountLabel = $discountPercent > 0
            ? $discountPercent.'% ('.$this->formatMoney($discountAmount).')'
            : 'None';

        $lines = [
            ['text' => 'Lumina Academy', 'x' => self::PAGE_MARGIN, 'y' => 398, 'size' => 14, 'style' => 'bold'],
            ['text' => 'Payment Receipt', 'x' => self::PAGE_MARGIN, 'y' => 380, 'size' => 10, 'style' => 'bold'],
            ['text' => 'Ref: '.$reference, 'x' => self::PAGE_MARGIN, 'y' => 352, 'size' => 9],
            ['text' => 'Student: '.$student->name, 'x' => self::PAGE_MARGIN, 'y' => 336, 'size' => 9],
            ['text' => 'Date: '.($payment->paid_on?->format('Y-m-d') ?? '-'), 'x' => self::PAGE_MARGIN, 'y' => 320, 'size' => 9],
            ['text' => 'Method: '.$this->methodLabel((string) $payment->method), 'x' => self::PAGE_MARGIN, 'y' => 304, 'size' => 9],
            ['text' => 'Amount Paid', 'x' => self::PAGE_MARGIN, 'y' => 274, 'size' => 10, 'style' => 'bold'],
            ['text' => $this->formatMoney((int) $payment->amount), 'x' => self::PAGE_MARGIN, 'y' => 254, 'size' => 16, 'style' => 'bold'],
            ['text' => 'Tuition Summary', 'x' => self::PAGE_MARGIN, 'y' => 220, 'size' => 10, 'style' => 'bold'],
            ['text' => 'Course total: '.$this->formatMoney((int) ($financialSummary['totalCoursesPrice'] ?? 0)), 'x' => self::PAGE_MARGIN, 'y' => 202, 'size' => 9],
            ['text' => 'Applied discount: '.$discountLabel, 'x' => self::PAGE_MARGIN, 'y' => 186, 'size' => 9],
            ['text' => 'Due after discount: '.$this->formatMoney((int) ($financialSummary['totalDueAfterDiscount'] ?? $financialSummary['totalCoursesPrice'] ?? 0)), 'x' => self::PAGE_MARGIN, 'y' => 170, 'size' => 9],
            ['text' => 'Total paid: '.$this->formatMoney((int) ($financialSummary['totalPaid'] ?? 0)), 'x' => self::PAGE_MARGIN, 'y' => 154, 'size' => 9],
            ['text' => 'Remaining: '.$this->formatMoney((int) ($financialSummary['totalRemaining'] ?? 0)), 'x' => self::PAGE_MARGIN, 'y' => 138, 'size' => 9],
            ['text' => 'Progress: '.(int) ($financialSummary['paidPercentage'] ?? 0).'%', 'x' => self::PAGE_MARGIN, 'y' => 122, 'size' => 9],
            ['text' => 'Generated: '.now()->format('Y-m-d H:i'), 'x' => self::PAGE_MARGIN, 'y' => 78, 'size' => 8],
            ['text' => 'Thank you.', 'x' => self::PAGE_MARGIN, 'y' => 62, 'size' => 8, 'style' => 'bold'],
        ];

        return $this->buildPdf($lines);
    }

    private function buildPdf(array $lines): string
    {
        $rightEdge = $this->pdfNumber(self::PAGE_WIDTH - self::PAGE_MARGIN);

        $content = "0 0 0 RG\n0.7 w\n";
        $content .= self::PAGE_MARGIN." 366 m {$rightEdge} 366 l S\n";
        $content .= self::PAGE_MARGIN." 238 m {$rightEdge} 238 l S\n";
        $content .= self::PAGE_MARGIN." 98 m {$rightEdge} 98 l S\n";

        foreach ($lines as $line) {
            $content .= $this->text(
                text: (string) $line['text'],
                x: (int) $line['x'],
                y: (int) $line['y'],
                size: (int) $line['size'],
                bold: ($line['style'] ?? null) === 'bold',
            );
        }

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 '.$this->pdfNumber(self::PAGE_WIDTH).' '.$this->pdfNumber(self::PAGE_HEIGHT).'] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
            '<< /Length '.strlen($content)." >>\nstream\n".$content."\nendstream",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $objectNumber = $index + 1;
            $pdf .= $objectNumber." 0 obj\n".$object."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($index = 1; $index <= count($objects); $index++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$index])."\n";
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n".$xrefOffset."\n%%EOF";

        return $pdf;
    }

    private function text(string $text, int $x, int $y, int $size, bool $bold = false): string
    {
        $font = $bold ? 'F2' : 'F1';

        return "0 0 0 rg\nBT /".$font.' '.$size.' Tf '.$x.' '.$y.' Td ('.$this->escapeText($text).") Tj ET\n";
    }

    private function pdfNumber(float $number): string
    {
        return rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.');
    }

    private function escapeText(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function formatMoney(int $amount): string
    {
        return number_format($amount, 0, ',', ' ').' DZD';
    }

    private function methodLabel(string $method): string
    {
        return match ($method) {
            'bank_transfer' => 'Baridi Mob',
            'card' => 'Card',
            'online' => 'Online',
            default => 'Cash',
        };
    }
}
