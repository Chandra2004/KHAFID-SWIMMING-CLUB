<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Registration;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class InvoiceService
{
    /**
     * Create a draft invoice linked to a registration.
     */
    public function createDraft(array $registrationUids, $payment = null): Invoice
    {
        $invoice = Invoice::create([
            'registration_uids' => $registrationUids,
            'payment_id' => $payment?->uid,
            'amount' => $payment?->amount ?? 0,
            'status' => 'draft',
        ]);

        return $invoice;
    }

    /**
     * Generate PDF for the invoice, mark as issued, and store file path.
     */
    public function issue(Invoice $invoice): Invoice
    {
        $invoice->update([
            'status' => 'issued',
            'issued_at' => now(),
            // 'pdf_path' => $filePath, // Kita tidak simpan path karena di-generate on-the-fly
        ]);

        return $invoice;
    }
}
