<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['account_name' => 'Bank Mandiri - KSC Utama', 'account_number' => '1234567890', 'bank_name' => 'Mandiri', 'balance' => 50000000],
            ['account_name' => 'Bank BCA - Operasional', 'account_number' => '0987654321', 'bank_name' => 'BCA', 'balance' => 25000000],
            ['account_name' => 'Bank BNI - Dana Abadi', 'account_number' => '1122334455', 'bank_name' => 'BNI', 'balance' => 100000000],
            ['account_name' => 'Kas Kecil Kantor', 'account_number' => '-', 'bank_name' => 'CASH', 'balance' => 2000000],
            ['account_name' => 'Bank BRI - Tabungan Atlet', 'account_number' => '5544332211', 'bank_name' => 'BRI', 'balance' => 15000000],
            ['account_name' => 'Bank CIMB - Sponsorship', 'account_number' => '6677889900', 'bank_name' => 'CIMB NIAGA', 'balance' => 0],
            ['account_name' => 'Bank Syariah Indonesia', 'account_number' => '9988776655', 'bank_name' => 'BSI', 'balance' => 10000000],
            ['account_name' => 'GoPay Business', 'account_number' => '08123456789', 'bank_name' => 'GOPAY', 'balance' => 500000],
            ['account_name' => 'OVO Merchant', 'account_number' => '08123456789', 'bank_name' => 'OVO', 'balance' => 300000],
            ['account_name' => 'Dana Bisnis', 'account_number' => '08123456789', 'bank_name' => 'DANA', 'balance' => 750000],
        ];

        foreach ($accounts as $account) {
            DB::table('finance_accounts')->insert([
                'uid' => Str::uuid(),
                'account_name' => $account['account_name'],
                'account_number' => $account['account_number'],
                'bank_name' => $account['bank_name'],
                'balance' => $account['balance'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
