<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterController extends Controller
{
    public function style()
    {
        return view('dashboard.master.style');
    }

    public function finance()
    {
        return view('dashboard.master.finance');
    }

    public function gallery()
    {
        return view('dashboard.master.gallery');
    }

    public function parameter()
    {
        return view('dashboard.master.parameter');
    }

    public function event()
    {
        return view('dashboard.master.event');
    }

    public function lombaIndex()
    {
        return view('dashboard.master.lomba_all');
    }

    public function lomba($uid)
    {
        $event = \App\Models\Event::where('uid', $uid)->firstOrFail();
        return view('dashboard.master.lomba', compact('event'));
    }

    public function pendaftaran()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->can('master-pendaftaran.view') && !$user->can('master-pendaftaran.view.self')) {
            abort(403);
        }
        return view('dashboard.master.pendaftaran');
    }

    public function resultEvent()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->can('master-result.view') && !$user->can('master-result.detail.self') && !$user->can('master-result.detail.team')) {
            abort(403);
        }
        return view('dashboard.master.result_event');
    }

    public function resultEventDetail($event_uid)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->can('master-result.detail') && !$user->can('master-result.detail.self') && !$user->can('master-result.detail.team')) {
            abort(403);
        }
        $event = \App\Models\Event::where('uid', $event_uid)->firstOrFail();
        return view('dashboard.master.result_event_detail', compact('event', 'event_uid'));
    }

    public function historyPendaftaran()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->can('master-history-pendaftaran.view') && 
            !$user->can('master-history-pendaftaran.view.self') && 
            !$user->can('master-history-pendaftaran.view.all')) {
            abort(403);
        }
        
        return view('dashboard.master.history_pendaftaran');
    }
}
