<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class InvoiceController extends Controller
{
    /**
     * Stream the invoice PDF inline in browser (preview + download).
     */
    public function download(Invoice $invoice)
    {
        // If PDF file already generated and exists, stream it
        $path = $invoice->pdf_path;
        if ($path && file_exists($path)) {
            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $invoice->invoice_number . '.pdf"',
            ]);
        }

        // Otherwise, regenerate on the fly
        // (Relations are eagerly loaded inside pdf.blade.php)

        $pdf = PDF::loadView('invoices.pdf', ['invoice' => $invoice]);

        return $pdf->stream($invoice->invoice_number . '.pdf');
    }
}
