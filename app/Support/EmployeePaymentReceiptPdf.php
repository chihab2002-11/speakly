<?php

namespace App\Support;

use App\Models\EmployeePayment;
use App\Models\User;

class EmployeePaymentReceiptPdf
{
    private const PAGE_WIDTH = 283.46;

    private const PAGE_HEIGHT = 425.20;

    private const PAGE_MARGIN = 16;

    /**
     * @param  array<string, mixed>  $paymentData
     */
    public function render(User $employee, ?EmployeePayment $employeePayment, array $paymentData): string
    {
        $reference = 'EMP-'.str_pad((string) ($employeePayment?->id ?? 0), 6, '0', STR_PAD_LEFT);
        $expectedSalary = (int) ($paymentData['expected_salary'] ?? 0);
        $amountPaid = (int) ($paymentData['amount_paid'] ?? 0);
        $remaining = (int) ($paymentData['remaining'] ?? 0);
        $status = (string) ($paymentData['status'] ?? 'pending');
        $role = (string) ($paymentData['role'] ?? 'Employee');

        $lines = [
            ['text' => 'Lumina Academy', 'x' => self::PAGE_MARGIN, 'y' => 398, 'size' => 14, 'style' => 'bold'],
            ['text' => 'Employee Payment Receipt', 'x' => self::PAGE_MARGIN, 'y' => 380, 'size' => 10, 'style' => 'bold'],
            ['text' => 'Ref: '.$reference, 'x' => self::PAGE_MARGIN, 'y' => 352, 'size' => 9],
            ['text' => 'Employee: '.$employee->name, 'x' => self::PAGE_MARGIN, 'y' => 336, 'size' => 9],
            ['text' => 'Role: '.$role, 'x' => self::PAGE_MARGIN, 'y' => 320, 'size' => 9],
            ['text' => 'Email: '.$employee->email, 'x' => self::PAGE_MARGIN, 'y' => 304, 'size' => 9],
            ['text' => 'Salary Details', 'x' => self::PAGE_MARGIN, 'y' => 274, 'size' => 10, 'style' => 'bold'],
            ['text' => 'Expected: '.$this->formatMoney($expectedSalary), 'x' => self::PAGE_MARGIN, 'y' => 254, 'size' => 9],
            ['text' => 'Paid: '.$this->formatMoney($amountPaid), 'x' => self::PAGE_MARGIN, 'y' => 238, 'size' => 9],
            ['text' => 'Remaining: '.$this->formatMoney($remaining), 'x' => self::PAGE_MARGIN, 'y' => 222, 'size' => 9],
            ['text' => 'Status: '.ucfirst($status), 'x' => self::PAGE_MARGIN, 'y' => 206, 'size' => 9, 'style' => 'bold'],
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
}
