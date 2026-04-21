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

        $lines = [
            ['text' => 'Lumina Academy', 'x' => 56, 'y' => 780, 'size' => 20, 'style' => 'bold'],
            ['text' => 'Payment Receipt', 'x' => 56, 'y' => 752, 'size' => 14, 'style' => 'bold'],
            ['text' => 'Reference: '.$reference, 'x' => 56, 'y' => 714, 'size' => 11],
            ['text' => 'Student: '.$student->name, 'x' => 56, 'y' => 694, 'size' => 11],
            ['text' => 'Email: '.$student->email, 'x' => 56, 'y' => 674, 'size' => 11],
            ['text' => 'Paid on: '.($payment->paid_on?->format('F j, Y') ?? '-'), 'x' => 56, 'y' => 654, 'size' => 11],
            ['text' => 'Method: '.$this->methodLabel((string) $payment->method), 'x' => 56, 'y' => 634, 'size' => 11],
            ['text' => 'Amount Paid', 'x' => 56, 'y' => 586, 'size' => 12, 'style' => 'bold'],
            ['text' => $this->formatMoney((int) $payment->amount), 'x' => 56, 'y' => 558, 'size' => 22, 'style' => 'bold'],
            ['text' => 'Tuition Summary', 'x' => 56, 'y' => 506, 'size' => 13, 'style' => 'bold'],
            ['text' => 'Total course price: '.$this->formatMoney((int) ($financialSummary['totalCoursesPrice'] ?? 0)), 'x' => 56, 'y' => 482, 'size' => 11],
            ['text' => 'Total paid: '.$this->formatMoney((int) ($financialSummary['totalPaid'] ?? 0)), 'x' => 56, 'y' => 462, 'size' => 11],
            ['text' => 'Remaining unpaid: '.$this->formatMoney((int) ($financialSummary['totalRemaining'] ?? 0)), 'x' => 56, 'y' => 442, 'size' => 11],
            ['text' => 'Paid progress: '.(int) ($financialSummary['paidPercentage'] ?? 0).'%', 'x' => 56, 'y' => 422, 'size' => 11],
            ['text' => 'Generated on '.now()->format('F j, Y g:i A'), 'x' => 56, 'y' => 92, 'size' => 9],
            ['text' => 'This receipt was generated from the Lumina Academy student billing portal.', 'x' => 56, 'y' => 76, 'size' => 9],
        ];

        return $this->buildPdf($lines);
    }

    private function buildPdf(array $lines): string
    {
        $content = "0.08 0.37 0.27 rg\n0 730 612 112 re f\n";
        $content .= "1 1 1 rg\n";

        foreach ($lines as $line) {
            $content .= $this->text(
                text: (string) $line['text'],
                x: (int) $line['x'],
                y: (int) $line['y'],
                size: (int) $line['size'],
                bold: ($line['style'] ?? null) === 'bold',
                white: (int) $line['y'] >= 730,
            );
        }

        $content .= "0.88 0.91 0.89 RG\n56 610 m 556 610 l S\n";
        $content .= "0.88 0.91 0.89 RG\n56 402 m 556 402 l S\n";

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 842] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>',
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

    private function text(string $text, int $x, int $y, int $size, bool $bold = false, bool $white = false): string
    {
        $font = $bold ? 'F2' : 'F1';
        $color = $white ? '1 1 1 rg' : '0.10 0.11 0.13 rg';

        return $color."\nBT /".$font.' '.$size.' Tf '.$x.' '.$y.' Td ('.$this->escapeText($text).") Tj ET\n";
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
