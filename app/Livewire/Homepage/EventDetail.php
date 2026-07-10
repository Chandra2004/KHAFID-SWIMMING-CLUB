<?php

namespace App\Livewire\Homepage;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Registration;
use App\Models\Payment;
use App\Models\RequirementParameter;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventDetail extends Component
{
    use WithFileUploads;

    public $event_uid;
    public $event;
    public $user;
    public $profileCompletion;
    public $registeredCategoryUids = [];
    
    // Properties to MATCH DASHBOARD (management-pendaftaran.blade.php)
    public $showCreateModal = false;
    public $create_user_uids = [];
    public $create_event_categories = [];
    public $create_payment_method = 'transfer';
    public $create_payment_proof;
    public $create_status = 'pending';
    public $create_errors = [];
    public $lombaSearch = '';
    public $userSearch = '';
    public $activeSlide = 0;
    public $requirements = [];

    public function updatedCreateUserUids()
    {
        $this->activeSlide = 0;
    }

    public function prevSlide()
    {
        if ($this->activeSlide > 0) {
            $this->activeSlide--;
        }
    }

    public function nextSlide()
    {
        if ($this->activeSlide < count($this->create_user_uids) - 1) {
            $this->activeSlide++;
        }
    }

    public function mount($slug, $uid)
    {
        $this->event_uid = $uid;
        $this->loadData();
    }

    public function loadData()
    {
        $eventModel = Event::with(['categories.category', 'financeAccount', 'author.profile'])
            ->where('uid', $this->event_uid)
            ->firstOrFail();

        $user = Auth::user();
        if ($user) {
            $this->registeredCategoryUids = Registration::where('user_uid', $user->uid)
                ->whereIn('event_category_uid', $eventModel->categories->pluck('uid'))
                ->where('status', '!=', 'rejected')
                ->pluck('event_category_uid')
                ->toArray();
            
            $this->create_user_uids = [$user->uid];
            $this->user = \App\Models\User::with('profile')->where('uid', $user->uid)->first();
        }

        $this->event = [
            'uid' => $eventModel->uid,
            'nama_event' => $eventModel->name,
            'slug' => $eventModel->slug,
            'banner_event' => $eventModel->banner,
            'deskripsi' => $eventModel->description,
            'lokasi_event' => $eventModel->location,
            'tanggal_event' => $eventModel->start_date,
            'waktu_event' => $eventModel->start_time,
            'status_event' => $this->mapStatus($eventModel->status),
            'biaya_event' => $eventModel->categories->min('registration_fee') ?? 0,
            'author' => $eventModel->author->profile->full_name ?? 'Admin KSC',
            'bank' => $eventModel->financeAccount->bank_name ?? null,
            'rekening' => $eventModel->financeAccount->account_number ?? null,
            'atas_nama' => $eventModel->financeAccount->account_name ?? null,
            'qr_code' => $eventModel->financeAccount->image ?? null,
            'total_quota' => $eventModel->categories->sum(fn($cat) => (int)$eventModel->lane_count * (int)$cat->total_series),
            'eventCategories' => $eventModel->categories->map(function($cat) {
                return [
                    'uid' => $cat->uid,
                    'acara_name' => $cat->acara_name,
                    'registration_fee' => $cat->registration_fee,
                    'start_time' => $cat->start_time,
                    'location' => $cat->location,
                    'type' => $cat->type,
                    'event_uid' => $cat->event_uid,
                ];
            })->toArray()
        ];
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = !$this->showCreateModal;
        if($this->showCreateModal) {
            $this->create_errors = [];
            $this->create_payment_proof = null;
        }
    }

    // --- LOGIKA VALIDASI DASHBOARD (COPIED FROM MANAGEMENT-PENDAFTARAN) ---
    private function validateCategoryRequirements($user, $category)
    {
        $profile = $user->profile;
        $missingData = [];

        if (!$profile) {
            $missingData[] = 'Data Profil Dasar';
        }

        // 1. Check Main Requirement
        if ($category->parameter_uid) {
            $mainParam = RequirementParameter::where('uid', $category->parameter_uid)->first();
            if ($mainParam) {
                $userValue = $this->getUserValueForParameter($profile, $mainParam->parameter_key);
                if (is_null($userValue)) {
                    $missingData[] = "{$mainParam->display_name}";
                } else {
                    $passed = $this->evaluateCondition($userValue, $category->operator, $category->parameter_value);
                    if (!$passed) {
                        return [
                            'status' => false,
                            'message' => "Syarat utama tidak terpenuhi ({$mainParam->display_name}: Harus {$category->operator} {$category->parameter_value})"
                        ];
                    }
                }
            }
        }

        // 2. Check Supporting Requirements
        foreach ($category->requirements as $req) {
            $param = $req->parameter;
            if (!$param) continue;

            $userValue = $this->getUserValueForParameter($profile, $param->parameter_key);
            if (is_null($userValue) && $req->is_required) {
                $missingData[] = "{$param->display_name}";
                continue;
            }

            $targetValue = is_array($req->parameter_value) ? ($req->parameter_value[0] ?? null) : $req->parameter_value;
            $passed = $this->evaluateCondition($userValue, $req->operator, $targetValue);
            if (!$passed && $req->is_required) {
                return [
                    'status' => false,
                    'message' => "Syarat pendukung tidak terpenuhi ({$param->display_name})"
                ];
            }
        }

        if (!empty($missingData)) {
            $fields = implode(', ', array_unique($missingData));
            return [
                'status' => false,
                'message' => "Data profil berikut belum lengkap: [{$fields}]. Silakan lengkapi di Dashboard."
            ];
        }

        return ['status' => true];
    }

    private function getUserValueForParameter($profile, $key)
    {
        if (!$profile) return null;
        switch ($key) {
            case 'gender': return $profile->gender;
            case 'birth_year': return $profile->birth_date ? \Carbon\Carbon::parse($profile->birth_date)->year : null;
            case 'age': return $profile->birth_date ? \Carbon\Carbon::parse($profile->birth_date)->age : null;
            case 'club_uid': return $profile->club_uid;
            case 'kta_number': return $profile->identity_number;
            case 'membership_status': return $profile->is_active ? 'active' : 'inactive';
            case 'verified_status': return ($profile->identity_photo && $profile->birth_certificate_photo) ? 'verified' : 'unverified';
            default: return $profile->{$key} ?? null;
        }
    }

    private function evaluateCondition($userValue, $operator, $targetValue)
    {
        if (is_null($userValue)) return false;
        if (is_numeric($userValue) && is_numeric($targetValue)) {
            $userValue = (float)$userValue; $targetValue = (float)$targetValue;
        }
        switch ($operator) {
            case '=': return $userValue == $targetValue;
            case '!=': return $userValue != $targetValue;
            case '>': return $userValue > $targetValue;
            case '<': return $userValue < $targetValue;
            case '>=': return $userValue >= $targetValue;
            case '<=': return $userValue <= $targetValue;
            case 'in':
            case 'IN': 
                $targets = is_array($targetValue) ? $targetValue : explode(',', $targetValue);
                return in_array($userValue, array_map('trim', $targets));
            default: return false;
        }
    }

    public function saveManualRegistration()
    {
        if (!Auth::check()) return redirect()->route('login');

        // Reset errors
        $this->create_errors = [];

        // Hitung apakah ada lomba berbayar
        $hasPaidCategory = false;
        foreach ($this->create_event_categories as $catUid) {
            $cat = EventCategory::where('uid', $catUid)->first();
            if ($cat && $cat->type === 'paid') { $hasPaidCategory = true; break; }
        }

        // VALIDASI LIVEWIRE (SAMA DENGAN DASHBOARD)
        try {
            $this->validate([
                'create_user_uids' => 'required|array|min:1',
                'create_event_categories' => 'required|array|min:1',
                'create_status' => 'required|in:pending,confirmed',
                'create_payment_method' => 'required|in:cash,transfer',
                'create_payment_proof' => ($hasPaidCategory && $this->create_payment_method === 'transfer') ? 'required|string' : 'nullable|string',
            ], [
                'create_payment_proof.required' => 'Wajib melampirkan bukti pembayaran untuk metode transfer.',
                'create_user_uids.required' => 'Peserta tidak terdeteksi.',
                'create_event_categories.required' => 'Pilih minimal satu kategori lomba.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('notification', [
                'status' => 'error',
                'message' => 'Gagal Simpan! Data yang Anda masukkan belum lengkap.',
            ]);
            throw $e;
        }

        $validRegistrations = [];
        $failedMessages = [];

        foreach ($this->create_user_uids as $usrUid) {
            $userModel = \App\Models\User::with('profile')->where('uid', $usrUid)->first();
            if (!$userModel) continue;

            foreach ($this->create_event_categories as $catUid) {
                // Check if already registered
                $exists = Registration::where('user_uid', $usrUid)->where('event_category_uid', $catUid)->where('status', '!=', 'rejected')->exists();
                if ($exists) continue;

                $cat = EventCategory::with(['requirements.parameter', 'event'])->where('uid', $catUid)->first();
                if (!$cat) continue;

                // VALIDASI PERSYARATAN (SAMA DENGAN DASHBOARD)
                $check = $this->validateCategoryRequirements($userModel, $cat);
                if (!$check['status']) {
                    $failedMessages[] = $check['message'];
                    continue;
                }

                // VALIDASI KUOTA
                $eventModel = $cat->event;
                $maxQuota = (int)$eventModel->lane_count * (int)$cat->total_series;
                $currentRegCount = Registration::where('event_category_uid', $catUid)->count();
                if ($currentRegCount >= $maxQuota) {
                    $failedMessages[] = "Kuota pendaftaran untuk " . $cat->acara_name . " sudah penuh.";
                    continue;
                }

                $validRegistrations[] = ['user_uid' => $usrUid, 'category' => $cat];
            }
        }

        if (empty($validRegistrations)) {
            $this->create_errors = !empty($failedMessages) ? $failedMessages : ['Semua kategori yang dipilih sudah Anda daftarkan.'];
            $this->dispatch('notification', [
                'status' => 'error',
                'message' => 'Pendaftaran gagal diproses. Silakan cek detail kendala.',
            ]);
            return;
        }

        try {
            DB::beginTransaction();
            $proofPath = null;
            if ($this->create_payment_proof) {
                $proofPath = \App\Helpers\ImageHelper::uploadToWebp($this->create_payment_proof, 'payments');
            }

            $createdRegistrations = [];

            foreach ($validRegistrations as $item) {
                $reg = Registration::create([
                    'user_uid' => $item['user_uid'],
                    'event_category_uid' => $item['category']->uid,
                    'entry_time' => now(),
                    'status' => 'pending',
                    'registration_number' => 'REG-' . strtoupper(substr(uniqid(), -6)),
                    'notes' => 'Pendaftaran melalui Halaman Publik',
                ]);

                $payment = null;
                if ($item['category']->type === 'paid') {
                    $payment = Payment::create([
                        'registration_uid' => $reg->uid,
                        'amount' => $item['category']->registration_fee,
                        'status' => 'pending',
                        'method' => $this->create_payment_method,
                        'payment_proof' => $proofPath,
                    ]);
                }

                $createdRegistrations[] = [
                    'registration' => $reg,
                    'payment' => $payment
                ];
            }

            // Generate grouped invoice
            if (!empty($createdRegistrations)) {
                $totalAmount = 0;
                $activePayment = null;
                $regUids = [];

                foreach ($createdRegistrations as $cReg) {
                    $regUids[] = $cReg['registration']->uid;
                    if ($cReg['payment']) {
                        $totalAmount += $cReg['payment']->amount;
                        if (!$activePayment) {
                            $activePayment = $cReg['payment'];
                        }
                    }
                }

                $invoiceService = app(\App\Services\InvoiceService::class);
                
                $invoice = \App\Models\Invoice::create([
                    'registration_uids' => $regUids,
                    'payment_id' => $activePayment?->uid,
                    'amount' => $totalAmount,
                    'status' => 'draft',
                ]);

                $invoice = $invoiceService->issue($invoice);

                $this->dispatch('open-invoice-url', url: route('invoice.download', [
                    'invoice' => $invoice->id, 
                    'invoice_number' => $invoice->invoice_number
                ]));
            }
            DB::commit();
            $this->dispatch('notification', [
                'status' => 'success', 
                'message' => 'Pendaftaran Berhasil Dikirim!',
                'duration' => 20000,
                'invoice_url' => isset($invoice) ? route('invoice.download', [
                    'invoice' => $invoice->id, 
                    'invoice_number' => $invoice->invoice_number
                ]) : null
            ]);
            $this->showCreateModal = false;
            $this->loadData();
            $this->create_event_categories = [];
            $this->create_payment_proof = null;
            
            // Dispatch a reload event after a short delay so the new tab can open first
            $this->dispatch('reload-page');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notification', ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    private function mapStatus($status)
    {
        $map = ['ongoing' => 'berjalan', 'upcoming' => 'mendatang', 'completed' => 'selesai', 'cancelled' => 'dibatalkan', 'draft' => 'draft'];
        return $map[$status] ?? $status;
    }

    public function render()
    {
        $availableCategories = EventCategory::with('event')
            ->where('event_uid', $this->event['uid'])
            ->when($this->lombaSearch, function($query) {
                $query->where('acara_name', 'like', '%' . $this->lombaSearch . '%');
            })->get();

        $usersQuery = \App\Models\User::with('profile')->where('is_active', true);
        if ($this->userSearch) {
            $usersQuery->where(function ($q) {
                $q->where('username', 'like', '%' . $this->userSearch . '%')
                    ->orWhere('email', 'like', '%' . $this->userSearch . '%')
                    ->orWhereHas('profile', function ($qProf) {
                        $qProf->where('full_name', 'like', '%' . $this->userSearch . '%');
                    });
            });
        }
        
        $availableUsers = [];
        $authUser = \Illuminate\Support\Facades\Auth::user();
        if ($authUser instanceof \App\Models\User && $authUser->can('master-pendaftaran.create')) {
             $availableUsers = $usersQuery->take(50)->get();
        }

        /** @var mixed $view */
        $view = view('livewire.homepage.event-detail', [
            'availableCategories' => $availableCategories,
            'availableUsers' => $availableUsers
        ]);
        return $view->layout('layouts.layout-homepage.app', ['title' => "KSC - " . $this->event['nama_event']]);
    }
}
