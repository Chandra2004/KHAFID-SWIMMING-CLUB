<?php
namespace App\Services;

use App\Models\EventCategory;
use App\Models\Registration;
use App\Models\Schedule;
use Illuminate\Support\Str;

class SeedingService
{
    public static function seedEventCategory($category)
    {
        $registrations = Registration::where('event_category_uid', $category->uid)
            ->where('status', 'confirmed')
            ->with(['schedule', 'user'])
            ->get();
            
        if ($registrations->isEmpty()) return;

        $laneCount = $category->event->lane_count ?? 8;
        if ($laneCount < 1) $laneCount = 8;
        
        $regData = [];
        foreach ($registrations as $reg) {
            $prestasi = self::getPrestasi($reg);
            
            $ms = 99999999999;
            if ($prestasi !== 'NT') {
                // Parse MM:SS.ms or SS.ms
                $parts = explode(':', $prestasi);
                if (count($parts) === 2) {
                    $secParts = explode('.', $parts[1]);
                    $minutes = (int)$parts[0];
                    $seconds = (int)$secParts[0];
                    $milliseconds = isset($secParts[1]) ? (int)str_pad($secParts[1], 3, '0', STR_PAD_RIGHT) : 0;
                    $ms = ($minutes * 60000) + ($seconds * 1000) + $milliseconds;
                } elseif (count($parts) === 1) {
                    $secParts = explode('.', $parts[0]);
                    $seconds = (int)$secParts[0];
                    $milliseconds = isset($secParts[1]) ? (int)str_pad($secParts[1], 3, '0', STR_PAD_RIGHT) : 0;
                    $ms = ($seconds * 1000) + $milliseconds;
                }
            }
            
            $regData[] = [
                'reg' => $reg,
                'ms' => $ms,
            ];
        }
        
        // Urutkan berdasarkan waktu: Paling lambat (termasuk NT) ke Paling cepat
        // ms besar = lebih lambat, ms kecil = lebih cepat. Kita urutkan Descending agar lambat di awal (Seri awal).
        usort($regData, function($a, $b) {
            return $b['ms'] <=> $a['ms'];
        });
        
        $totalSwimmers = count($regData);
        $totalHeats = (int)ceil($totalSwimmers / $laneCount);
        
        $heats = []; 
        $currentIndex = $totalSwimmers - 1; 
        
        // Isi seri dari paling belakang (Seri terakhir berisi atlet tercepat)
        for ($h = $totalHeats; $h >= 1; $h--) {
            $swimmersInHeat = [];
            for ($l = 0; $l < $laneCount; $l++) {
                if ($currentIndex >= 0) {
                    $swimmersInHeat[] = $regData[$currentIndex];
                    $currentIndex--;
                }
            }
            $heats[$h] = $swimmersInHeat; 
        }
        
        // Generate susunan Spearhead untuk lane (e.g., 4, 5, 3, 6, 2, 7, 1, 8)
        $center = (int)ceil($laneCount / 2);
        $laneOrder = [];
        $laneOrder[] = $center;
        $offset = 1;
        while (count($laneOrder) < $laneCount) {
            $right = $center + $offset;
            $left = $center - $offset;
            
            if ($right <= $laneCount) $laneOrder[] = $right;
            if ($left >= 1) $laneOrder[] = $left;
            
            $offset++;
        }
        
        // Simpan pemetaan lintasan baru ke database
        foreach ($heats as $heatNumber => $swimmers) {
            foreach ($swimmers as $index => $data) {
                if (isset($laneOrder[$index])) {
                    $laneNumber = $laneOrder[$index];
                    $reg = $data['reg'];
                    
                    $schedule = $reg->schedule;
                    if (!$schedule) {
                        $schedule = new Schedule([
                            'uid' => (string) Str::uuid(),
                            'registration_uid' => $reg->uid
                        ]);
                    }
                    $schedule->heat_number = $heatNumber;
                    $schedule->lane_number = $laneNumber;
                    $schedule->save();
                }
            }
        }
    }

    public static function getPrestasi(Registration $reg)
    {
        $prestasi = $reg->seed_time;
        if (empty($prestasi) || strtoupper($prestasi) === 'NT') {
            $bestResult = \App\Models\Result::whereHas('registration', function ($query) use ($reg) {
                $query->where('user_uid', $reg->user_uid)
                      ->whereHas('eventCategory', function ($query2) use ($reg) {
                          $query2->where('acara_name', $reg->eventCategory->acara_name ?? '');
                      });
            })
            ->where('status', 'FINISH')
            ->orderBy('total_milliseconds', 'asc')
            ->first();
            
            $prestasi = $bestResult ? $bestResult->final_time : 'NT';
        }
        
        return $prestasi;
    }
}
