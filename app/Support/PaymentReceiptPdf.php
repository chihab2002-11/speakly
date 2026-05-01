<?php

namespace App\Support;

use App\Models\TuitionPayment;
use App\Models\User;

class PaymentReceiptPdf
{
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
            ['text' => 'Lumina Academy', 'x' => 18, 'y' => 392, 'size' => 14, 'style' => 'bold'],
            ['text' => 'Payment Receipt', 'x' => 18, 'y' => 374, 'size' => 10, 'style' => 'bold'],
            ['text' => 'Ref: '.$reference, 'x' => 18, 'y' => 346, 'size' => 9],
            ['text' => 'Student: '.$student->name, 'x' => 18, 'y' => 330, 'size' => 9],
            ['text' => 'Date: '.($payment->paid_on?->format('Y-m-d') ?? '-'), 'x' => 18, 'y' => 314, 'size' => 9],
            ['text' => 'Method: '.$this->methodLabel((string) $payment->method), 'x' => 18, 'y' => 298, 'size' => 9],
            ['text' => 'Amount Paid', 'x' => 18, 'y' => 268, 'size' => 10, 'style' => 'bold'],
            ['text' => $this->formatMoney((int) $payment->amount), 'x' => 18, 'y' => 248, 'size' => 16, 'style' => 'bold'],
            ['text' => 'Tuition Summary', 'x' => 18, 'y' => 214, 'size' => 10, 'style' => 'bold'],
            ['text' => 'Course total: '.$this->formatMoney((int) ($financialSummary['totalCoursesPrice'] ?? 0)), 'x' => 18, 'y' => 196, 'size' => 9],
            ['text' => 'Applied discount: '.$discountLabel, 'x' => 18, 'y' => 180, 'size' => 9],
            ['text' => 'Due after discount: '.$this->formatMoney((int) ($financialSummary['totalDueAfterDiscount'] ?? $financialSummary['totalCoursesPrice'] ?? 0)), 'x' => 18, 'y' => 164, 'size' => 9],
            ['text' => 'Total paid: '.$this->formatMoney((int) ($financialSummary['totalPaid'] ?? 0)), 'x' => 18, 'y' => 148, 'size' => 9],
            ['text' => 'Remaining: '.$this->formatMoney((int) ($financialSummary['totalRemaining'] ?? 0)), 'x' => 18, 'y' => 132, 'size' => 9],
            ['text' => 'Progress: '.(int) ($financialSummary['paidPercentage'] ?? 0).'%', 'x' => 18, 'y' => 116, 'size' => 9],
            ['text' => 'Generated: '.now()->format('Y-m-d H:i'), 'x' => 18, 'y' => 72, 'size' => 8],
            ['text' => 'Thank you.', 'x' => 18, 'y' => 56, 'size' => 8, 'style' => 'bold'],
        ];

        return $this->buildPdf($lines);
    }

    private function buildPdf(array $lines): string
    {
        $content = "0 0 0 RG\n0.7 w\n18 360 m 208 360 l S\n";
        $content .= "18 232 m 208 232 l S\n";
        $content .= "18 92 m 208 92 l S\n";

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
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 226 420] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>',
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
            'bank_transfer' => 'Bank Transfer',
            'card' => 'Card',
            'online' => 'Online',
            default => 'Cash',
        };
    }
}
