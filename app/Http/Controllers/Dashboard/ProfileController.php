<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function myProfile() {
        $data = [
            'title' => 'Dashboard Profil Saya | Khafid Swimming Club (KSC) - Official Website',
        ];

        return view('dashboard.account.profile', $data);

    }
}
