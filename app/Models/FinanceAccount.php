<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceAccount extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uid';
    public $incrementing = false;

    protected $fillable = [
        'uid',
        'bank_name',
        'account_number',
        'account_name',
        'image',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
