<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RequirementParameter;
use Illuminate\Support\Str;

class RequirementParameterSeeder extends Seeder
{
    public function run(): void
    {
        $params = [
            [
                'parameter_key' => 'birth_year',
                'display_name' => 'Tahun Lahir',
                'input_type' => 'number',
                'allowed_operators' => ['=', '>', '<', '>=', '<=', 'IN'],
                'input_options' => null,
            ],
            [
                'parameter_key' => 'gender',
                'display_name' => 'Jenis Kelamin',
                'input_type' => 'select',
                'allowed_operators' => ['='],
                'input_options' => ['male', 'female'],
            ],
            [
                'parameter_key' => 'kta_number',
                'display_name' => 'Nomor KTA PRSI',
                'input_type' => 'text',
                'allowed_operators' => ['='],
                'input_options' => null,
            ],
            [
                'parameter_key' => 'club_uid',
                'display_name' => 'ID Klub',
                'input_type' => 'text',
                'allowed_operators' => ['=', 'IN'],
                'input_options' => null,
            ],
            [
                'parameter_key' => 'best_time_limit',
                'display_name' => 'Limit Waktu Terbaik',
                'input_type' => 'text',
                'allowed_operators' => ['<', '<='],
                'input_options' => null,
            ],
            [
                'parameter_key' => 'membership_status',
                'display_name' => 'Status Keanggotaan',
                'input_type' => 'select',
                'allowed_operators' => ['='],
                'input_options' => ['active', 'inactive'],
            ],
            [
                'parameter_key' => 'age',
                'display_name' => 'Usia Atlet',
                'input_type' => 'number',
                'allowed_operators' => ['=', '>=', '<='],
                'input_options' => null,
            ],
            [
                'parameter_key' => 'region_code',
                'display_name' => 'Kode Wilayah',
                'input_type' => 'text',
                'allowed_operators' => ['='],
                'input_options' => null,
            ],
            [
                'parameter_key' => 'verified_status',
                'display_name' => 'Status Verifikasi Akun',
                'input_type' => 'select',
                'allowed_operators' => ['='],
                'input_options' => ['verified', 'unverified'],
            ],
            [
                'parameter_key' => 'medical_checkup',
                'display_name' => 'Surat Kesehatan',
                'input_type' => 'select',
                'allowed_operators' => ['='],
                'input_options' => ['valid', 'expired'],
            ],
        ];

        foreach ($params as $param) {
            RequirementParameter::updateOrCreate(['parameter_key' => $param['parameter_key']], [
                'uid' => Str::uuid(),
                'display_name' => $param['display_name'],
                'input_type' => $param['input_type'],
                'allowed_operators' => $param['allowed_operators'],
                'input_options' => $param['input_options'],
                'is_active' => true,
            ]);
        }
    }
}
