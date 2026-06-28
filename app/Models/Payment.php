<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uid';
    public $incrementing = false;

    protected $fillable = [
        'registration_uid',
        'invoice_number',
        'amount',
        'status',
        'method',
        'paid_at',
        'payment_proof',
        'admin_notes',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class, 'registration_uid', 'uid');
    }
}
