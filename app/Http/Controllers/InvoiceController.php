<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class InvoiceController extends Controller
{
    /**
     * Stream or download the invoice PDF.
     */
    public function download(\Illuminate\Http\Request $request, Invoice $invoice)
    {
        $shouldDownload = $request->query('download') === '1';
        $disposition = $shouldDownload ? 'attachment' : 'inline';

        // If PDF file already generated and exists, stream it
        $path = $invoice->pdf_path;
        if ($path && file_exists($path)) {
            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => $disposition . '; filename="' . $invoice->invoice_number . '.pdf"',
            ]);
        }

        // Otherwise, regenerate on the fly
        // (Relations are eagerly loaded inside pdf.blade.php)

        $pdf = PDF::loadView('invoices.pdf', ['invoice' => $invoice]);

        if ($shouldDownload) {
            return $pdf->download($invoice->invoice_number . '.pdf');
        }

        return $pdf->stream($invoice->invoice_number . '.pdf');
    }
}
