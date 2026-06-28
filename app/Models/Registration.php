<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uid';
    public $incrementing = false;

    protected static function booted()
    {
        static::saved(function ($registration) {
            // Otomatis buat jadwal jika status adalah 'confirmed'
            if ($registration->status === 'confirmed') {
                // Reload schedule to be sure it's up to date
                if (!$registration->relationLoaded('schedule')) {
                    $registration->load('schedule');
                }

                if (!$registration->schedule) {
                    $eventCategory = $registration->eventCategory;
                    $event = $eventCategory?->event;
                    
                    if ($event) {
                        $laneCount = $event->lane_count ?: 8;
                        
                        // Hitung jumlah pendaftar yang sudah dikonfirmasi sebelumnya di kategori ini
                        $count = Registration::where('event_category_uid', $registration->event_category_uid)
                            ->where('status', 'confirmed')
                            ->where('uid', '!=', $registration->uid)
                            ->count();

                        $heat = floor($count / $laneCount) + 1;
                        $lane = ($count % $laneCount) + 1;

                        \App\Models\Schedule::create([
                            'uid' => (string) \Illuminate\Support\Str::uuid(),
                            'registration_uid' => $registration->uid,
                            'heat_number' => $heat,
                            'lane_number' => $lane,
                        ]);
                    }
                }
            }
        });
    }

    protected $fillable = [
        'user_uid',
        'event_category_uid',
        'seed_time',
        'entry_time',
        'status',
        'registration_number',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_uid', 'uid');
    }

    public function eventCategory()
    {
        return $this->belongsTo(EventCategory::class, 'event_category_uid', 'uid');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'registration_uid', 'uid');
    }

    public function result()
    {
        return $this->hasOne(Result::class, 'registration_uid', 'uid');
    }

    public function schedule()
    {
        return $this->hasOne(Schedule::class, 'registration_uid', 'uid');
    }
}
