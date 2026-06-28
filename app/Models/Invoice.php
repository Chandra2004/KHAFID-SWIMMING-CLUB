<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'registration_uids',
        'payment_id',
        'amount',
        'tax',
        'status',
        'issued_at',
        'due_date',
        'pdf_path',
    ];

    protected $casts = [
        'registration_uids' => 'array',
        'issued_at' => 'datetime',
        'due_date' => 'date',
    ];

    public function registrations()
    {
        return Registration::whereIn('uid', $this->registration_uids ?? [])->get();
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'uid');
    }

    protected static function booted()
    {
        static::creating(function ($invoice) {
            $invoice->invoice_number = 'KSC_' . strtoupper(Str::random(8));
        });
    }
}
?>
