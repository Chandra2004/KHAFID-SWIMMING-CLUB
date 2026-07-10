<?php

use Livewire\Volt\Component;
use App\Models\Invoice;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Registration;
use App\Models\Payment;
use App\Models\DataUser;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;

new class extends Component {
    use WithPagination, WithFileUploads;

    public $filterEvent = '';
    public $filterCategory = '';
    public $statusFilter = '';

    // Modal view properties
    public $showDetailModal = false;
    public $selectedRegistration = null;

    // Manual Create properties
    public $showCreateModal = false;
    public $create_user_uids = [];
    public $create_event_categories = [];
    public $create_payment_method = 'transfer';
    public $create_payment_proof = null;
    public $create_status = 'pending';
    public $create_errors = [];

    // Confirm Modal properties
    public $showConfirmModal = false;
    public $confirmAction = null;
    public $confirmParam = null;
    public $confirmTitle = '';
    public $confirmMessage = '';
    public $confirmType = 'danger'; // 'danger' or 'success'
    public $withInput = false;
    public $confirmInput = '';

    public function promptConfirm($action, $param, $title, $message, $type = 'danger', $withInput = false)
    {
        $this->confirmAction = $action;
        $this->confirmParam = $param;
        $this->confirmTitle = $title;
        $this->confirmMessage = $message;
        $this->confirmType = $type;
        $this->withInput = $withInput;
        $this->confirmInput = '';
        $this->showConfirmModal = true;
    }

    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
    }

    public function executeConfirm()
    {
        if ($this->confirmAction === 'updateStatus') {
            $this->updateStatus($this->confirmParam, $this->confirmInput);
        } elseif ($this->confirmAction === 'updatePaymentStatus') {
            $this->updatePaymentStatus($this->confirmParam);
        } elseif ($this->confirmAction === 'deleteRegistration') {
            $this->deleteRegistration($this->confirmParam);
        }
        $this->showConfirmModal = false;
    }

    public function updatePaymentStatus($status)
    {
        $this->authorize('master-pendaftaran.edit');
        if (!$this->selectedRegistration || !$this->selectedRegistration->payment) {
            return;
        }

        $this->selectedRegistration->payment->update([
            'status' => $status,
            'paid_at' => $status === 'paid' ? now() : null,
        ]);

        $this->dispatch('notification', [
            'status' => 'success',
            'message' => 'Status pembayaran berhasil diverifikasi.',
        ]);

        $this->selectedRegistration->refresh()->load(['user.profile', 'eventCategory.event', 'payment']);
    }

    public $activeSlide = 0;

    public function updatedCreateUserUids()
    {
        $this->activeSlide = 0;
    }

    public function prevSlide()
    {
        if ($this->activeSlide > 0) {
            $this->activeSlide--;
        } else {
            $this->activeSlide = count($this->create_user_uids) - 1;
        }
    }

    public function nextSlide()
    {
        if ($this->activeSlide < count($this->create_user_uids) - 1) {
            $this->activeSlide++;
        } else {
            $this->activeSlide = 0;
        }
    }

    public $userSearch = '';
    public $lombaSearch = '';

    public function updatedFilterEvent()
    {
        $this->filterCategory = ''; // Reset category when event changes
        $this->resetPage();
    }

    public function updatedFilterCategory()
    {
        $this->resetPage();
    }

    public function with()
    {
        $allEvents = \App\Models\Event::orderBy('name', 'asc')->get();
        $allCategoriesQuery = \App\Models\EventCategory::when($this->filterEvent, function ($q) {
            $q->where('event_uid', $this->filterEvent);
        })->orderBy('acara_name', 'asc');

        // Filter based on user's gender, but only for non-admins
        if (!auth()->user()->can('master-pendaftaran.create')) {
            $userProfile = \App\Models\DataUser::where('user_uid', auth()->user()->uid)->first();
            if ($userProfile && $userProfile->gender) {
                $genderParam = \App\Models\RequirementParameter::where('parameter_key', 'gender')->first();
                if ($genderParam) {
                    $allCategoriesQuery->where(function ($q) use ($genderParam, $userProfile) {
                        $q->where(function ($subQ) use ($genderParam, $userProfile) {
                            $subQ->where('parameter_uid', $genderParam->uid)->where('parameter_value', $userProfile->gender);
                        })->orWhere(function ($subQ) use ($genderParam) {
                            $subQ->where('parameter_uid', '!=', $genderParam->uid)->orWhereNull('parameter_uid');
                        });
                    });

                    $allCategoriesQuery->whereDoesntHave('requirements', function ($q) use ($genderParam, $userProfile) {
                        $q->where('parameter_uid', $genderParam->uid)
                            ->where('parameter_value', '!=', $userProfile->gender)
                            ->where('parameter_value', 'not like', '%"' . $userProfile->gender . '"%');
                    });
                }
            }
        }

        $allCategories = $allCategoriesQuery->get();

        $usersQuery = \App\Models\User::with(['profile.club'])
            ->where('is_active', true)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'atlet');
            });

        // Batasi pilihan pendaftaran hanya untuk atlet dari klub yang sama (kecuali super_admin)
        $userClubUid = auth()->user()->profile?->club_uid;
        if ($userClubUid && !auth()->user()->hasRole('super_admin')) {
            $usersQuery->whereHas('profile', function ($q) use ($userClubUid) {
                $q->where('club_uid', $userClubUid);
            });
        }
        if ($this->userSearch) {
            $usersQuery->where(function ($q) {
                $q->where('username', 'like', '%' . $this->userSearch . '%')
                    ->orWhere('email', 'like', '%' . $this->userSearch . '%')
                    ->orWhereHas('profile', function ($qProf) {
                        $qProf->where('full_name', 'like', '%' . $this->userSearch . '%');
                    });
            });
        }
        $rawUsers = $usersQuery->take(50)->get();

        $myClubUid = auth()->user()->profile?->club_uid;

        // Urutkan agar klub pendaftar berada di paling atas, kemudian berabjad
        $sortedUsers = $rawUsers->sortBy(function ($u) use ($myClubUid) {
            $uClubUid = $u->profile?->club_uid;
            $uClubName = $u->profile?->club?->name ?? 'Z_Tanpa Klub';
            $isMyClub = $uClubUid === $myClubUid && $myClubUid !== null ? 0 : 1;
            return $isMyClub . '_' . $uClubName . '_' . $u->username;
        });

        // Group by nama klub
        $groupedUsers = $sortedUsers->groupBy(function ($u) {
            return $u->profile?->club?->name ?? 'TANPA KLUB / SEKOLAH';
        });

        $users = $groupedUsers;

        $categoriesQuery = \App\Models\EventCategory::with(['event.financeAccount'])->whereHas('event', function ($q) {
            $q->where('status', 'ongoing');
        });
        if ($this->lombaSearch) {
            $categoriesQuery->where(function ($q) {
                $q->where('acara_name', 'like', '%' . $this->lombaSearch . '%')->orWhereHas('event', function ($qEvent) {
                    $qEvent->where('name', 'like', '%' . $this->lombaSearch . '%');
                });
            });
        }

        // Filter based on user's gender, but only for non-admins
        if (!auth()->user()->can('master-pendaftaran.create')) {
            $userProfile = \App\Models\DataUser::where('user_uid', auth()->user()->uid)->first();
            if ($userProfile && $userProfile->gender) {
                $genderParam = \App\Models\RequirementParameter::where('parameter_key', 'gender')->first();
                if ($genderParam) {
                    // If main requirement is gender, it must match user's gender
                    $categoriesQuery->where(function ($q) use ($genderParam, $userProfile) {
                        $q->where(function ($subQ) use ($genderParam, $userProfile) {
                            $subQ->where('parameter_uid', $genderParam->uid)->where('parameter_value', $userProfile->gender);
                        })->orWhere(function ($subQ) use ($genderParam) {
                            $subQ->where('parameter_uid', '!=', $genderParam->uid)->orWhereNull('parameter_uid');
                        });
                    });

                    // If supporting requirement is gender, it must match user's gender
                    $categoriesQuery->whereDoesntHave('requirements', function ($q) use ($genderParam, $userProfile) {
                        $q->where('parameter_uid', $genderParam->uid)
                            ->where('parameter_value', '!=', $userProfile->gender)
                            ->where('parameter_value', 'not like', '%"' . $userProfile->gender . '"%');
                    });
                }
            }
        }

        $categories = $categoriesQuery->get();

        $query = Registration::withTrashed()
            ->with(['user.profile', 'eventCategory.event', 'payment'])
            ->when(!auth()->user()->can('master-pendaftaran.view') && (auth()->user()->can('master-pendaftaran.view.self') || auth()->user()->can('master-history-pendaftaran.view.self')), function ($q) {
                $q->where('registrations.user_uid', auth()->user()->uid);
            })
            ->when($this->filterEvent, function ($q) {
                $q->where('events.uid', $this->filterEvent);
            })
            ->when($this->filterCategory, function ($q) {
                $q->where('registrations.event_category_uid', $this->filterCategory);
            })
            ->when($this->statusFilter, function ($q) {
                $q->where('registrations.status', $this->statusFilter);
            })
            ->leftJoin('event_categories', 'registrations.event_category_uid', '=', 'event_categories.uid')
            ->leftJoin('events', 'event_categories.event_uid', '=', 'events.uid')
            ->select('registrations.*')
            ->orderBy('events.name', 'asc')
            ->orderBy('event_categories.acara_name', 'asc')
            ->orderBy('registrations.created_at', 'desc');

        return [
            'registrations' => $query->paginate(10),
            'stats' => [
                'total' => auth()->user()->can('master-pendaftaran.view')
                    ? Registration::withTrashed()->count()
                    : Registration::withTrashed()
                        ->where('user_uid', auth()->user()->uid)
                        ->count(),
                'pending' => auth()->user()->can('master-pendaftaran.view')
                    ? Registration::where('status', 'pending')->count()
                    : Registration::where('status', 'pending')
                        ->where('user_uid', auth()->user()->uid)
                        ->count(),
                'confirmed' => auth()->user()->can('master-pendaftaran.view')
                    ? Registration::where('status', 'confirmed')->count()
                    : Registration::where('status', 'confirmed')
                        ->where('user_uid', auth()->user()->uid)
                        ->count(),
                'rejected' => auth()->user()->can('master-pendaftaran.view')
                    ? Registration::withTrashed()->where('status', 'rejected')->count()
                    : Registration::withTrashed()
                        ->where('status', 'rejected')
                        ->where('user_uid', auth()->user()->uid)
                        ->count(),
            ],
            'availableUsers' => $users,
            'availableCategories' => $categories,
            'allEvents' => $allEvents,
            'allCategories' => $allCategories,
        ];
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openDetailModal($uid)
    {
        $reg = Registration::withTrashed()->where('uid', $uid)->firstOrFail();

        // Cek izin: Admin bisa lihat semua, Atlet hanya bisa lihat miliknya
        if (auth()->user()->can('master-pendaftaran.view')) {
            // OK
        } elseif ((auth()->user()->can('master-pendaftaran.view.self') || auth()->user()->can('master-history-pendaftaran.view.self')) && $reg->user_uid === auth()->user()->uid) {
            // OK
        } else {
            abort(403, 'Anda tidak memiliki akses ke detail pendaftaran ini.');
        }

        $this->selectedRegistration = Registration::withTrashed()
            ->with(['user.profile', 'eventCategory.event', 'payment'])
            ->where('uid', $uid)
            ->firstOrFail();
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedRegistration = null;
    }

    public function updateStatus($status, $reason = null)
    {
        if (!$this->selectedRegistration) {
            return;
        }

        // Otorisasi Logika
        $isAdmin = auth()->user()->can('master-pendaftaran.edit');
        $isSelf = auth()->user()->can('master-pendaftaran.edit.self') && $this->selectedRegistration->user_uid === auth()->user()->uid;
        $isFree = $this->selectedRegistration->eventCategory?->registration_fee <= 0;

        if ($isAdmin) {
            // Admin bebas ganti status apapun
        } elseif ($isSelf && $isFree && $status === 'cancelled') {
            // Atlet boleh membatalkan sendiri jika GRATIS
        } else {
            if ($isSelf && !$isFree) {
                $this->dispatch('notification', ['status' => 'error', 'message' => 'Pendaftaran berbayar tidak dapat diubah sendiri.']);
                return;
            }
            abort(403, 'Anda tidak memiliki izin untuk mengubah status ini.');
        }

        if (!$this->selectedRegistration) {
            return;
        }

        if (!in_array($status, ['pending', 'confirmed', 'cancelled', 'rejected'])) {
            return;
        }

        // Jika sebelumnya Rejected (Terhapus), Pulihkan dulu
        if ($this->selectedRegistration->trashed() && $status !== 'rejected') {
            $this->selectedRegistration->restore();
        }

        // Pemetaan Status Pembayaran Otomatis
        if ($this->selectedRegistration->payment) {
            $paymentStatus = match ($status) {
                'confirmed' => 'paid',
                'cancelled' => 'failed',
                'rejected' => 'failed',
                'pending' => 'pending',
                default => 'pending',
            };
            $this->selectedRegistration->payment->update([
                'status' => $paymentStatus,
                'paid_at' => $status === 'confirmed' ? now() : null,
            ]);
        }

        // Update Status Pendaftaran
        $this->selectedRegistration->update([
            'status' => $status,
            'notes' => in_array($status, ['rejected', 'cancelled']) ? $reason : null,
        ]);

        // Jika status adalah rejected, lakukan soft delete
        if ($status === 'rejected') {
            $this->selectedRegistration->delete();
        }

        $this->dispatch('notification', [
            'status' => 'success',
            'message' => 'Status pendaftaran berhasil diubah menjadi ' . strtoupper($status),
        ]);

        $this->closeDetailModal();
    }

    public function openCreateModal()
    {
        if (!auth()->user()->can('master-pendaftaran.create') && !auth()->user()->can('master-pendaftaran.create.self')) {
            abort(403);
        }
        $this->create_payment_method = auth()->user()->can('master-pendaftaran.pay.cash') ? 'cash' : 'transfer';
        $this->create_payment_proof = null;
        $this->create_status = 'pending';
        $this->create_errors = [];

        // Jika atlet, otomatis isi user_uid dengan miliknya sendiri
        if (!auth()->user()->can('master-pendaftaran.create') && auth()->user()->can('master-pendaftaran.create.self')) {
            $this->create_user_uids = [auth()->user()->uid];
        }

        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function saveManualRegistration()
    {
        if (!auth()->user()->can('master-pendaftaran.create') && !auth()->user()->can('master-pendaftaran.create.self')) {
            abort(403);
        }

        // Jika atlet, paksa pendaftaran untuk dirinya sendiri
        if (!auth()->user()->can('master-pendaftaran.create') && auth()->user()->can('master-pendaftaran.create.self')) {
            $this->create_user_uids = [auth()->user()->uid];
            $this->create_status = 'pending';
        }

        // Hitung apakah ada lomba berbayar yang dipilih
        $hasPaidCategory = false;
        foreach ($this->create_event_categories as $catUid) {
            $cat = \App\Models\EventCategory::where('uid', $catUid)->first();
            if ($cat && $cat->type === 'paid') {
                $hasPaidCategory = true;
                break;
            }
        }

        try {
            $this->validate(
                [
                    'create_user_uids' => 'required|array|min:1',
                    'create_event_categories' => 'required|array|min:1',
                    'create_status' => 'required|in:pending,confirmed',
                    'create_payment_method' => 'required|in:cash,transfer',
                    'create_payment_proof' => $hasPaidCategory && $this->create_payment_method === 'transfer' ? 'required|image|max:5120' : 'nullable|image|max:5120',
                ],
                [
                    'create_payment_proof.required' => 'Wajib melampirkan bukti pembayaran untuk metode transfer.',
                    'create_user_uids.required' => 'Pilih minimal satu peserta.',
                    'create_event_categories.required' => 'Pilih minimal satu kategori lomba.',
                ],
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('notification', [
                'status' => 'error',
                'message' => 'Gagal Simpan! Data yang Anda masukkan belum lengkap.',
            ]);
            throw $e;
        }

        $this->create_errors = [];

        // 1. Validasi awal: Cek apakah ada pendaftaran yang benar-benar bisa dibuat
        // (menghindari upload gambar jika semua peserta sudah terdaftar atau tidak memenuhi syarat)
        $validRegistrations = [];
        $failedMessages = [];

        foreach ($this->create_user_uids as $usrUid) {
            $user = \App\Models\User::with('profile')->where('uid', $usrUid)->first();
            if (!$user) {
                continue;
            }

            foreach ($this->create_event_categories as $catUid) {
                // Check if already registered (including soft-deleted/rejected ones)
                $exists = Registration::withTrashed()->where('user_uid', $usrUid)->where('event_category_uid', $catUid)->exists();

                if ($exists) {
                    continue;
                }

                $cat = \App\Models\EventCategory::with('requirements.parameter')->where('uid', $catUid)->first();
                if (!$cat) {
                    continue;
                }

                // --- VALIDASI PERSYARATAN (PARAMETERS) ---
                $check = $this->validateCategoryRequirements($user, $cat);
                if (!$check['status']) {
                    $failedMessages[] = 'Peserta ' . ($user->profile?->full_name ?: $user->username) . ' gagal mendaftar di ' . $cat->acara_name . ': ' . $check['message'];
                    continue;
                }

                // --- VALIDASI KUOTA ---
                $event = $cat->event;
                if ($event->status !== 'ongoing') {
                    $failedMessages[] = "Pendaftaran gagal: Event '{$event->name}' saat ini tidak dalam status Ongoing (Status: {$event->status}).";
                    continue;
                }
                $maxQuota = (int) $event->lane_count * (int) $cat->total_series;
                $currentRegCount = Registration::where('event_category_uid', $catUid)->count();

                // Hitung berapa banyak yang sudah masuk antrian validasi di batch ini untuk kategori yang sama
                $inBatchCount = collect($validRegistrations)->where('category.uid', $catUid)->count();

                if ($currentRegCount + $inBatchCount >= $maxQuota) {
                    $failedMessages[] = 'Kuota pendaftaran untuk ' . $cat->acara_name . " sudah penuh (Maks: {$maxQuota} peserta).";
                    continue;
                }

                $validRegistrations[] = [
                    'user_uid' => $usrUid,
                    'category' => $cat,
                ];
            }
        }

        if (empty($validRegistrations)) {
            if (empty($failedMessages)) {
                $this->create_errors = ['Gagal: Semua peserta yang dipilih sudah terdaftar di kategori lomba tersebut.'];
            } else {
                $this->create_errors = $failedMessages;
            }

            $this->dispatch('notification', [
                'status' => 'error',
                'message' => 'Pendaftaran gagal diproses. Silakan cek detail kendala di dalam form.',
            ]);

            return;
        }

        // 2. Upload file HANYA JIKA ada pendaftaran yang valid
        $proofPath = null;
        if ($this->create_payment_proof) {
            $proofPath = \App\Helpers\ImageHelper::uploadToWebp($this->create_payment_proof, 'payments');
        }

        // 3. Proses penyimpanan
        $successCount = 0;
        $createdRegistrations = [];

        foreach ($validRegistrations as $item) {
            $usrUid = $item['user_uid'];
            $cat = $item['category'];

            $reg = Registration::create([
                'user_uid' => $usrUid,
                'event_category_uid' => $cat->uid,
                'entry_time' => now(),
                'status' => $this->create_status,
                'registration_number' => 'REG-' . strtoupper(substr(uniqid(), -6)),
                'notes' => 'Pendaftaran Manual oleh Admin',
            ]);

            $payment = null;
            if ($cat->type === 'paid') {
                $payment = Payment::create([
                    'registration_uid' => $reg->uid,
                    'amount' => $cat->registration_fee,
                    'status' => $this->create_status === 'confirmed' ? 'paid' : 'pending',
                    'method' => $this->create_payment_method,
                    'payment_proof' => $proofPath,
                    'paid_at' => $this->create_status === 'confirmed' ? now() : null,
                    'admin_notes' => 'Didaftarkan manual oleh Admin',
                ]);
            }

            $createdRegistrations[] = [
                'registration' => $reg,
                'payment' => $payment,
            ];

            $successCount++;
        }

        // ==== Generate single invoice for the batch if there is any registration ====
        if (!empty($createdRegistrations)) {
            try {
                $firstRegData = $createdRegistrations[0];
                $firstReg = $firstRegData['registration'];

                // Kita cari payment yang aktif di transaksi ini
                // Hitung total amount dari seluruh pendaftaran berbayar di batch ini
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

                // Buat draft invoice menggunakan registrasi yang dikelompokkan
                $invoice = \App\Models\Invoice::create([
                    'registration_uids' => $regUids,
                    'payment_id' => $activePayment?->uid,
                    'amount' => $totalAmount, // Total tagihan keseluruhan lomba
                    'status' => 'draft',
                ]);

                // Simpan & render PDF
                $invoice = $invoiceService->issue($invoice);

                // Dispatch JS event untuk membuka preview
                $this->dispatch(
                    'open-invoice-url',
                    url: route('invoice.download', [
                        'invoice' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                    ]),
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Invoice generation failed: ' . $e->getMessage());
            }
        }

        $this->dispatch('notification', [
            'status' => 'success',
            'message' => "Berhasil mendaftarkan peserta ke $successCount lomba.",
            'duration' => 20000,
            'invoice_url' => isset($invoice)
                ? route('invoice.download', [
                    'invoice' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                ])
                : null,
        ]);
        $this->closeCreateModal();
        $this->resetPage();

        $this->dispatch('reload-page');
    }

    public function deleteRegistration($uid)
    {
        $reg = Registration::withTrashed()->where('uid', $uid)->with('eventCategory')->first();
        if (!$reg) {
            return;
        }

        // Otorisasi Logika
        $isAdmin = auth()->user()->can('master-pendaftaran.delete');
        $isSelf = auth()->user()->can('master-pendaftaran.delete.self') && $reg->user_uid === auth()->user()->uid;
        $isFree = $reg->eventCategory?->registration_fee <= 0;

        if ($isAdmin) {
            // Admin bebas
        } elseif ($isSelf && $isFree) {
            // Atlet boleh hapus sendiri jika GRATIS
        } else {
            if ($isSelf && !$isFree) {
                $this->dispatch('notification', ['status' => 'error', 'message' => 'Pendaftaran berbayar tidak dapat dihapus sendiri. Silakan hubungi Admin.']);
                return;
            }
            abort(403, 'Anda tidak memiliki akses untuk menghapus data ini.');
        }

        // Hapus data terkait secara permanen
        if ($reg->payment) {
            $payment = $reg->payment()->withTrashed()->first();
            if ($payment && $payment->payment_proof) {
                \App\Helpers\ImageHelper::deleteFile($payment->payment_proof);
            }
            $reg->payment()->withTrashed()->forceDelete();
        }

        if ($reg->result) {
            $reg->result()->withTrashed()->forceDelete();
        }

        if ($reg->schedule) {
            $reg->schedule()->withTrashed()->forceDelete();
        }

        // Hard delete pendaftaran
        $reg->forceDelete();

        $this->dispatch('notification', [
            'status' => 'success',
            'message' => 'Data pendaftaran berhasil dihapus secara permanen dari database',
        ]);
    }

    private function validateCategoryRequirements($user, $category)
    {
        $profile = $user->profile;
        $missingData = [];

        if (!$profile) {
            $missingData[] = 'Data Profil Dasar Belum Ada Sama Sekali';
        } else {
            if (empty($profile->full_name)) {
                $missingData[] = 'Nama Lengkap';
            }
            if (empty($profile->nickname)) {
                $missingData[] = 'Nama Panggilan';
            }
            if (empty($user->username)) {
                $missingData[] = 'Username';
            }
            if (empty($user->email)) {
                $missingData[] = 'Email';
            }
            if (empty($profile->phone_number)) {
                $missingData[] = 'No. Telepon';
            }
            if (empty($profile->birth_place)) {
                $missingData[] = 'Tempat Lahir';
            }
            if (empty($profile->birth_date)) {
                $missingData[] = 'Tanggal Lahir';
            }
            if (empty($profile->gender)) {
                $missingData[] = 'Jenis Kelamin';
            }
            if (empty($profile->identity_number)) {
                $missingData[] = 'NIK / No. KTP';
            }
            if (empty($profile->address)) {
                $missingData[] = 'Alamat Lengkap';
            }
            if (empty($profile->club_uid)) {
                $missingData[] = 'Klub / Asal Sekolah';
            }
            if (empty($profile->profile_picture)) {
                $missingData[] = 'Foto Profil';
            }
            if (empty($profile->identity_photo)) {
                $missingData[] = 'Foto KTP / Identitas';
            }
            if (empty($profile->birth_certificate_photo)) {
                $missingData[] = 'Foto Akta Kelahiran';
            }
            if (empty($profile->family_card_photo)) {
                $missingData[] = 'Foto Kartu Keluarga (KK)';
            }
        }

        // 1. Check Main Requirement (fields directly on EventCategory)
        if ($category->parameter_uid) {
            $mainParam = \App\Models\RequirementParameter::where('uid', $category->parameter_uid)->first();
            if ($mainParam) {
                $userValue = $this->getUserValueForParameter($profile, $mainParam->parameter_key);

                if (is_null($userValue)) {
                    $missingData[] = "{$mainParam->display_name} (Harus {$category->operator} {$category->parameter_value})";
                } else {
                    $passed = $this->evaluateCondition($userValue, $category->operator, $category->parameter_value);
                    if (!$passed) {
                        return [
                            'status' => false,
                            'message' => "Syarat utama tidak terpenuhi ({$mainParam->display_name}: Harus {$category->operator} {$category->parameter_value}, saat ini: " . ($userValue ?? 'Kosong') . ')',
                        ];
                    }
                }
            }
        }

        // 2. Check Supporting Requirements (CategoryRequirement models)
        foreach ($category->requirements as $req) {
            $param = $req->parameter;
            if (!$param) {
                continue;
            }

            $userValue = $this->getUserValueForParameter($profile, $param->parameter_key);

            if (is_null($userValue) && $req->is_required) {
                $targetValue = is_array($req->parameter_value) ? $req->parameter_value[0] ?? null : $req->parameter_value;
                $missingData[] = "{$param->display_name} (Harus {$req->operator} {$targetValue})";
                continue;
            }

            // Supporting requirements value is stored as JSON array, take the first one
            $targetValue = is_array($req->parameter_value) ? $req->parameter_value[0] ?? null : $req->parameter_value;

            $passed = $this->evaluateCondition($userValue, $req->operator, $targetValue);

            if (!$passed) {
                if ($req->is_required) {
                    return [
                        'status' => false,
                        'message' => $req->error_message ?: "Syarat pendukung tidak terpenuhi ({$param->display_name}: Harus {$req->operator} {$targetValue}, saat ini: " . ($userValue ?? 'Kosong') . ')',
                    ];
                }
            }
        }

        if (!empty($missingData)) {
            $fields = implode(', ', array_unique($missingData));
            return [
                'status' => false,
                'message' => "Data profil berikut belum diisi: [{$fields}]. Silakan lengkapi di menu Profil.",
            ];
        }

        return ['status' => true];
    }

    private function getUserValueForParameter($profile, $key)
    {
        if (!$profile) {
            return null;
        }

        switch ($key) {
            case 'gender':
                return $profile->gender; // Male / Female
            case 'birth_year':
                return $profile->birth_date ? $profile->birth_date->year : null;
            case 'age':
                return $profile->birth_date ? $profile->birth_date->age : null;
            case 'club_uid':
                return $profile->club_uid;
            case 'kta_number':
                return $profile->identity_number;
            case 'membership_status':
                return $profile->is_active ? 'active' : 'inactive';
            case 'verified_status':
                // Logika verifikasi bisa berdasarkan kelengkapan foto identitas
                return $profile->identity_photo && $profile->birth_certificate_photo ? 'verified' : 'unverified';
            default:
                return $profile->{$key} ?? null;
        }
    }

    private function evaluateCondition($userValue, $operator, $targetValue)
    {
        if (is_null($userValue)) {
            return false;
        }

        // Normalisasi untuk perbandingan numerik jika memungkinkan
        if (is_numeric($userValue) && is_numeric($targetValue)) {
            $userValue = (float) $userValue;
            $targetValue = (float) $targetValue;
        }

        switch ($operator) {
            case '=':
                return $userValue == $targetValue;
            case '!=':
                return $userValue != $targetValue;
            case '>':
                return $userValue > $targetValue;
            case '>=':
                return $userValue >= $targetValue;
            case '<':
                return $userValue < $targetValue;
            case '<=':
                return $userValue <= $targetValue;
            case 'IN':
                $targets = is_array($targetValue) ? $targetValue : explode(',', $targetValue);
                return in_array($userValue, array_map('trim', $targets));
            default:
                return false;
        }
    }
}; ?>

<div class="p-4 md:p-8">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase leading-none">Manajemen Pendaftaran
            </h2>
            <p class="text-sm text-slate-500 font-medium mt-2 uppercase tracking-widest italic">Verifikasi Berkas &
                Persetujuan Peserta Lomba</p>
        </div>

        @if (auth()->user()->can('master-pendaftaran.create') || auth()->user()->can('master-pendaftaran.create.self'))
            <button wire:click="openCreateModal"
                class="flex items-center gap-3 bg-ksc-blue hover:bg-blue-700 text-white px-8 py-4 rounded-2xl font-black transition shadow-xl shadow-blue-100 transform hover:-translate-y-1 uppercase text-xs tracking-widest group relative overflow-hidden">
                <div
                    class="absolute inset-0 bg-gradient-to-r from-emerald-500/10 to-transparent opacity-0 group-hover:opacity-100 transition">
                </div>
                <x-lucide-plus-circle class="w-5 h-5 text-emerald-400 relative z-10" />
                <span class="relative z-10">
                    {{ auth()->user()->can('master-pendaftaran.create') ? 'Pendaftaran Manual' : 'Daftar Event Sekarang' }}
                </span>
            </button>
        @endif
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div
            class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-xl shadow-slate-200 relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Total Pendaftar</p>
                <h3 class="text-4xl font-black">{{ $stats['total'] }}</h3>
            </div>
            <x-lucide-users
                class="absolute -right-6 -bottom-6 w-32 h-32 text-white/5 group-hover:scale-110 transition duration-500" />
        </div>
        <div
            class="bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-sm flex items-center justify-between hover:shadow-xl hover:shadow-slate-200/50 transition duration-500">
            <div>
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Menunggu Acc</p>
                <h3 class="text-3xl font-black text-amber-500">{{ $stats['pending'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-500">
                <x-lucide-clock class="w-6 h-6" />
            </div>
        </div>
        <div
            class="bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-sm flex items-center justify-between hover:shadow-xl hover:shadow-slate-200/50 transition duration-500">
            <div>
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Diterima</p>
                <h3 class="text-3xl font-black text-emerald-500">{{ $stats['confirmed'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-500">
                <x-lucide-check-circle class="w-6 h-6" />
            </div>
        </div>
        <div
            class="bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-sm flex items-center justify-between hover:shadow-xl hover:shadow-slate-200/50 transition duration-500">
            <div>
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Ditolak</p>
                <h3 class="text-3xl font-black text-rose-500">{{ $stats['rejected'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-500">
                <x-lucide-x-circle class="w-6 h-6" />
            </div>
        </div>
    </div>

    @can('master-pendaftaran.filter')
        {{-- Filters --}}
        <div class="flex flex-col md:flex-row gap-4 mb-8">
            {{-- Filter Event --}}
            <div class="flex-1 relative group">
                <x-lucide-calendar
                    class="w-5 h-5 absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors" />
                <select wire:model.live="filterEvent"
                    class="w-full pl-14 pr-10 py-4 bg-white border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-blue-50 outline-none transition shadow-sm appearance-none cursor-pointer">
                    <option value="">Semua Event</option>
                    @foreach ($allEvents as $ev)
                        <option value="{{ $ev->uid }}">{{ $ev->name }}</option>
                    @endforeach
                </select>
                <x-lucide-chevron-down
                    class="w-4 h-4 absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none" />
            </div>

            {{-- Filter Lomba --}}
            <div class="flex-1 relative group">
                <x-lucide-award
                    class="w-5 h-5 absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors" />
                <select wire:model.live="filterCategory"
                    class="w-full pl-14 pr-10 py-4 bg-white border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-blue-50 outline-none transition shadow-sm appearance-none cursor-pointer">
                    <option value="">Semua Lomba</option>
                    @foreach ($allCategories as $cat)
                        <option value="{{ $cat->uid }}">{{ $cat->acara_name }}</option>
                    @endforeach
                </select>
                <x-lucide-chevron-down
                    class="w-4 h-4 absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none" />
            </div>

            {{-- Filter Status --}}
            <div class="w-full md:w-64 relative group">
                <x-lucide-filter
                    class="w-5 h-5 absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors" />
                <select wire:model.live="statusFilter"
                    class="w-full pl-14 pr-10 py-4 bg-white border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-blue-50 outline-none transition shadow-sm appearance-none cursor-pointer uppercase tracking-widest">
                    <option value="">Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="rejected">Rejected</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <x-lucide-chevron-down
                    class="w-4 h-4 absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none" />
            </div>
        </div>
    @else
        <div class="mb-8 p-6 bg-blue-50/50 rounded-3xl border border-blue-100/50">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-200">
                    <x-lucide-clipboard-list class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Data Pendaftaran Anda</h3>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest italic">Menampilkan riwayat
                        pendaftaran yang telah Anda lakukan</p>
                </div>
            </div>
        </div>
    @endcan


    {{-- Table Content --}}
    <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Pendaftar
                        </th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Acara
                            Lomba</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                            Pembayaran</th>
                        <th
                            class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">
                            Status</th>
                        <th
                            class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @php
                        $groupedRegistrations = $registrations->getCollection()->groupBy([
                            function ($reg) {
                                return $reg->eventCategory->event->name ?? 'Event Tidak Diketahui';
                            },
                            function ($reg) {
                                return $reg->eventCategory->acara_name ?? 'Lomba Tidak Diketahui';
                            },
                        ]);
                    @endphp

                    @forelse($groupedRegistrations as $eventName => $categories)
                        {{-- Event Header Row --}}
                        <tr class="bg-slate-100 border-y border-slate-200">
                            <td colspan="5" class="px-8 py-3">
                                <div class="flex items-center gap-2">
                                    <x-lucide-calendar class="w-4 h-4 text-slate-500" />
                                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Event:
                                        {{ $eventName }}</span>
                                </div>
                            </td>
                        </tr>

                        @foreach ($categories as $lombaName => $items)
                            {{-- Lomba/Category Header Row --}}
                            <tr class="bg-white border-b border-slate-100">
                                <td colspan="5" class="px-12 py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-blue-500 rounded-full"></div>
                                        <span
                                            class="text-[11px] font-semibold text-slate-600 uppercase tracking-wide">Lomba:
                                            {{ $lombaName }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium">({{ count($items) }}
                                            Peserta)</span>
                                    </div>
                                </td>
                            </tr>

                            @foreach ($items as $reg)
                                <tr wire:key="reg-{{ $reg->uid }}"
                                    class="hover:bg-slate-50 transition border-b border-slate-100 last:border-none">
                                    <td class="px-8 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-xl overflow-hidden bg-slate-100 shrink-0 border border-slate-200">
                                                @if ($reg->user?->profile?->profile_picture)
                                                    <img src="{{ asset($reg->user?->profile->profile_picture) }}"
                                                        class="w-full h-full object-cover">
                                                @else
                                                    <div
                                                        class="w-full h-full flex items-center justify-center text-slate-300">
                                                        <x-lucide-user class="w-4 h-4" />
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <p
                                                    class="text-sm font-semibold text-slate-800 uppercase leading-none mb-1">
                                                    {{ $reg->user?->profile?->full_name ?: $reg->user?->username ?? 'Deleted User' }}
                                                </p>
                                                <p
                                                    class="text-[9px] font-medium text-slate-400 uppercase tracking-widest">
                                                    #{{ $reg->registration_number ?: substr($reg->uid, 0, 8) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-4">
                                        <div class="flex flex-col">
                                            <span
                                                class="text-xs font-semibold text-slate-600 uppercase">{{ $reg->eventCategory?->acara_name }}</span>
                                            <span
                                                class="text-[9px] text-slate-400 uppercase tracking-wide italic">{{ $reg->eventCategory?->type === 'paid' ? 'Paid' : 'Free' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-4">
                                        @if ($reg->eventCategory?->type === 'paid')
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold text-slate-700">Rp
                                                    {{ number_format($reg->payment?->amount ?? 0, 0, ',', '.') }}</span>
                                                <span
                                                    class="text-[9px] text-slate-400 uppercase">{{ $reg->payment?->method ?: 'N/A' }}</span>
                                            </div>
                                        @else
                                            <span
                                                class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest">Free</span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-4 text-center">
                                        <span
                                            class="px-3 py-1 rounded-lg text-[9px] font-bold uppercase tracking-widest border
                                        {{ $reg->status === 'pending' ? 'bg-amber-50 text-amber-600 border-amber-100' : '' }}
                                        {{ $reg->status === 'confirmed' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : '' }}
                                        {{ $reg->status === 'rejected' ? 'bg-rose-50 text-rose-600 border-rose-100' : '' }}
                                        {{ $reg->status === 'cancelled' ? 'bg-slate-100 text-slate-500 border-slate-200' : '' }}">
                                            {{ $reg->status }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex justify-center gap-2">
                                            @php
                                                $canViewDetail =
                                                    auth()->user()->can('master-pendaftaran.view') ||
                                                    (auth()->user()->can('master-pendaftaran.view.self') &&
                                                        $reg->user_uid === auth()->user()->uid);
                                            @endphp

                                            @if ($canViewDetail)
                                                <button wire:click="openDetailModal('{{ $reg->uid }}')"
                                                    class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition"
                                                    title="Cek Dokumen & Bukti">
                                                    <x-lucide-search-check class="w-5 h-5" />
                                                </button>
                                            @endif
                                            @php
                                                $canDelete =
                                                    auth()->user()->can('master-pendaftaran.delete') ||
                                                    (auth()->user()->can('master-pendaftaran.delete.self') &&
                                                        $reg->user_uid === auth()->user()->uid &&
                                                        ($reg->eventCategory->registration_fee ?? 0) <= 0);
                                            @endphp

                                            @if ($canDelete)
                                                <button
                                                    wire:click="promptConfirm('deleteRegistration', '{{ $reg->uid }}', 'Hapus Pendaftaran', 'Yakin ingin menghapus pendaftaran ini?', 'danger')"
                                                    class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition"
                                                    title="Hapus Pendaftaran">
                                                    <x-lucide-trash-2 class="w-5 h-5" />
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                            @endforeach
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div
                                        class="w-20 h-20 bg-slate-50 rounded-[2.5rem] flex items-center justify-center mb-4">
                                        <x-lucide-clipboard-x class="w-10 h-10 text-slate-200" />
                                    </div>
                                    <h3 class="text-lg font-black text-slate-400 uppercase tracking-widest">Belum Ada
                                        Pendaftar</h3>
                                    <p class="text-xs text-slate-300 font-bold mt-1">Data pendaftaran lomba akan muncul
                                        di sini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8">
        {{ $registrations->links() }}
    </div>

    {{-- Detail & Verification Modal --}}
    @if ($showDetailModal && $selectedRegistration)
        <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeDetailModal"></div>

            <div
                class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-4xl relative z-50 border border-slate-100 max-h-[90vh] flex flex-col">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    @if (!auth()->user()->can('master-pendaftaran.edit.self'))
                        <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">Verifikasi Pendaftar
                        </h3>
                    @else
                        <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">Lihat Pendaftaran</h3>
                    @endif

                    <button wire:click="closeDetailModal" class="text-slate-400 hover:text-slate-600 transition">
                        <x-lucide-x class="w-6 h-6" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        {{-- Identitas Peserta --}}
                        <div>
                            <div class="flex items-center gap-4 mb-4 border-b border-slate-100 pb-4">
                                @if ($selectedRegistration->user?->profile?->profile_picture)
                                    <img src="{{ asset($selectedRegistration->user?->profile->profile_picture) }}"
                                        class="w-16 h-16 rounded-2xl object-cover shadow-sm border border-slate-200">
                                @else
                                    <div
                                        class="w-16 h-16 bg-blue-50 text-blue-400 rounded-2xl flex items-center justify-center border border-blue-100">
                                        <x-lucide-user class="w-8 h-8" />
                                    </div>
                                @endif
                                <div>
                                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest">Identitas
                                        Atlet</h4>
                                    <p class="text-lg font-black text-slate-900 leading-none mt-1">
                                        {{ $selectedRegistration->user?->profile?->full_name ?: $selectedRegistration->user?->username ?? 'Deleted User' }}
                                    </p>
                                    <p class="text-[10px] font-bold text-slate-400 mt-0.5">
                                        {{ $selectedRegistration->user?->email ?? 'No Email' }}</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Tempat,
                                            Tgl Lahir</label>
                                        <p class="text-sm font-bold text-slate-900">
                                            {{ $selectedRegistration->user?->profile?->birth_place ?: '-' }},
                                            {{ $selectedRegistration->user?->profile?->birth_date ? \Carbon\Carbon::parse($selectedRegistration->user?->profile->birth_date)->format('d M Y') : '-' }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Jenis
                                            Kelamin</label>
                                        <p class="text-sm font-bold text-slate-900 uppercase">
                                            {{ $selectedRegistration->user?->profile?->gender ?: '-' }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">No.
                                            Telp / WA</label>
                                        <p class="text-sm font-bold text-slate-900">
                                            {{ $selectedRegistration->user?->profile?->phone_number ?: '-' }}</p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Klub
                                            Renang</label>
                                        <p class="text-sm font-bold text-slate-900 uppercase">
                                            {{ $selectedRegistration->user?->profile?->club?->name ?: 'INDEPENDENT (TIDAK ADA)' }}
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Alamat
                                        Lengkap</label>
                                    <p class="text-sm font-bold text-slate-900">
                                        {{ $selectedRegistration->user?->profile?->address ?: '-' }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Foto
                                            KTP / Identitas (Sbg Pengganti KK)</label>
                                        @if ($selectedRegistration->user?->profile?->identity_photo)
                                            <a href="{{ route('document.view', ['type' => 'ktp', 'filename' => basename($selectedRegistration->user?->profile->identity_photo) ?: 'none']) }}"
                                                target="_blank"
                                                class="block w-full h-24 bg-slate-100 rounded-2xl overflow-hidden border border-slate-200 hover:border-blue-400 transition relative group">
                                                <img src="{{ route('document.view', ['type' => 'ktp', 'filename' => basename($selectedRegistration->user?->profile->identity_photo) ?: 'none']) }}"
                                                    class="w-full h-full object-cover">
                                                <div
                                                    class="absolute inset-0 bg-slate-900/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                                    <x-lucide-external-link class="w-6 h-6 text-white" />
                                                </div>
                                            </a>
                                        @else
                                            <div
                                                class="w-full h-24 bg-slate-50 border border-dashed border-slate-200 rounded-2xl flex items-center justify-center text-slate-400 text-xs font-bold">
                                                Belum Diupload</div>
                                        @endif
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Akte
                                            Kelahiran</label>
                                        @if ($selectedRegistration->user?->profile?->birth_certificate_photo)
                                            <a href="{{ route('document.view', ['type' => 'akta', 'filename' => basename($selectedRegistration->user?->profile->birth_certificate_photo) ?: 'none']) }}"
                                                target="_blank"
                                                class="block w-full h-24 bg-slate-100 rounded-2xl overflow-hidden border border-slate-200 hover:border-blue-400 transition relative group">
                                                <img src="{{ route('document.view', ['type' => 'akta', 'filename' => basename($selectedRegistration->user?->profile->birth_certificate_photo) ?: 'none']) }}"
                                                    class="w-full h-full object-cover">
                                                <div
                                                    class="absolute inset-0 bg-slate-900/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                                    <x-lucide-external-link class="w-6 h-6 text-white" />
                                                </div>
                                            </a>
                                        @else
                                            <div
                                                class="w-full h-24 bg-slate-50 border border-dashed border-slate-200 rounded-2xl flex items-center justify-center text-slate-400 text-xs font-bold">
                                                Belum Diupload</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Bukti Pembayaran & Status --}}
                        <div>
                            <h4
                                class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 border-b border-slate-100 pb-2">
                                Acara & Pembayaran</h4>
                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Nomor
                                        Lomba</label>
                                    <p class="text-sm font-black text-slate-900 uppercase">
                                        {{ $selectedRegistration->eventCategory?->acara_name }}</p>
                                </div>
                                @if ($selectedRegistration->eventCategory?->type === 'paid')
                                    <div class="p-4 bg-blue-50/50 rounded-2xl border border-blue-100">
                                        <div class="flex justify-between items-center mb-2">
                                            <span
                                                class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Total
                                                Tagihan</span>
                                            <span
                                                class="px-2 py-0.5 bg-blue-100 text-blue-600 rounded-md text-[9px] font-black uppercase">{{ $selectedRegistration->payment?->status ?: 'PENDING' }}</span>
                                        </div>
                                        <p class="text-xl font-black text-blue-900">Rp
                                            {{ number_format($selectedRegistration->payment?->amount ?? 0, 0, ',', '.') }}
                                        </p>

                                        <div class="mt-4">
                                            <label
                                                class="block text-[10px] font-black text-blue-400 uppercase tracking-widest mb-2">Bukti
                                                Transfer</label>
                                            @if ($selectedRegistration->payment?->payment_proof)
                                                <a href="{{ asset($selectedRegistration->payment->payment_proof) }}"
                                                    target="_blank"
                                                    class="block w-full h-32 bg-white rounded-xl overflow-hidden border border-blue-100 hover:border-blue-400 transition">
                                                    <img src="{{ asset($selectedRegistration->payment->payment_proof) }}"
                                                        class="w-full h-full object-cover">
                                                </a>
                                            @else
                                                <div
                                                    class="w-full h-32 bg-white/50 border border-dashed border-blue-200 rounded-xl flex items-center justify-center text-blue-400 text-xs font-bold">
                                                    Belum mengunggah bukti</div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                                        <p
                                            class="text-sm font-black text-emerald-600 uppercase tracking-widest text-center">
                                            GRATIS / FREE ENTRY</p>
                                    </div>
                                @endif

                                <div>
                                    <label
                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Waktu
                                        Pendaftaran</label>
                                    <p class="text-xs font-bold text-slate-600">
                                        {{ $selectedRegistration->created_at->format('d F Y, H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-8 border-t border-slate-50 flex items-center justify-between bg-slate-50/30 gap-4">
                    <div class="flex-1">
                        @if ($selectedRegistration->status === 'pending')
                            <p
                                class="text-xs font-bold text-amber-500 uppercase tracking-widest flex items-center gap-2">
                                <x-lucide-alert-circle class="w-4 h-4" /> Butuh Tindakan
                            </p>
                        @else
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Status Saat Ini:
                                <span
                                    class="font-black
                                    {{ $selectedRegistration->status === 'confirmed' ? 'text-emerald-500' : '' }}
                                    {{ $selectedRegistration->status === 'pending' ? 'text-amber-500' : '' }}
                                    {{ $selectedRegistration->status === 'rejected' ? 'text-rose-500' : '' }}
                                    {{ $selectedRegistration->status === 'cancelled' ? 'text-slate-400' : '' }}
                                    ">
                                    {{ strtoupper($selectedRegistration->status) }}
                                </span>
                            </p>
                        @endif
                    </div>
                    @php
                        $canEdit = auth()->user()->can('master-pendaftaran.edit');
                        $canCancelSelf =
                            auth()->user()->can('master-pendaftaran.edit.self') &&
                            $selectedRegistration->user_uid === auth()->user()->uid &&
                            ($selectedRegistration->eventCategory->registration_fee ?? 0) <= 0;
                    @endphp

                    @if ($canEdit || $canCancelSelf)
                        <div class="flex flex-wrap justify-end gap-2">
                            {{-- Admin Only Buttons --}}
                            @if ($canEdit)
                                {{-- Pending Button --}}
                                @if ($selectedRegistration->status !== 'pending')
                                    <button wire:key="btn-pending-{{ $selectedRegistration->uid }}"
                                        wire:click="promptConfirm('updateStatus', 'pending', 'Set Pending', 'Kembalikan status pendaftaran ke Pending?', 'warning')"
                                        wire:loading.attr="disabled"
                                        class="px-4 py-2 bg-amber-50 text-amber-600 border border-amber-100 rounded-xl font-black uppercase text-[9px] tracking-widest transition hover:bg-amber-100 flex items-center gap-1">
                                        <x-lucide-clock class="w-3 h-3" /> Pending
                                    </button>
                                @endif

                                {{-- Confirm Button --}}
                                @if ($selectedRegistration->status !== 'confirmed')
                                    @if (
                                        $selectedRegistration->eventCategory?->type === 'paid' &&
                                            $selectedRegistration->payment &&
                                            $selectedRegistration->payment->status !== 'paid')
                                        <button wire:key="btn-verify-{{ $selectedRegistration->uid }}"
                                            wire:click="promptConfirm('updatePaymentStatus', 'paid', 'Verifikasi Pembayaran', 'Yakin ingin MEMVERIFIKASI BUKTI PEMBAYARAN ini?', 'success')"
                                            wire:loading.attr="disabled"
                                            class="px-4 py-2 bg-blue-500 text-white hover:bg-blue-600 rounded-xl font-black uppercase text-[9px] tracking-widest shadow-lg shadow-blue-100 transition flex items-center gap-1">
                                            <x-lucide-receipt class="w-3 h-3" /> Verifikasi & Acc
                                        </button>
                                    @else
                                        <button wire:key="btn-confirm-{{ $selectedRegistration->uid }}"
                                            wire:click="promptConfirm('updateStatus', 'confirmed', 'Setujui Pendaftaran', 'Yakin ingin MENYETUJUI pendaftaran ini?', 'success')"
                                            wire:loading.attr="disabled"
                                            class="px-4 py-2 bg-emerald-500 text-white hover:bg-emerald-600 rounded-xl font-black uppercase text-[9px] tracking-widest shadow-lg shadow-emerald-100 transition flex items-center gap-1">
                                            <x-lucide-check-circle class="w-3 h-3" /> Setujui
                                        </button>
                                    @endif
                                @endif

                                {{-- Reject Button --}}
                                @if ($selectedRegistration->status !== 'rejected')
                                    <button wire:key="btn-reject-{{ $selectedRegistration->uid }}"
                                        wire:click="promptConfirm('updateStatus', 'rejected', 'Tolak Pendaftaran', 'Yakin ingin MENOLAK pendaftaran ini? Silakan berikan alasannya.', 'danger', true)"
                                        wire:loading.attr="disabled"
                                        class="px-4 py-2 bg-rose-50 text-rose-600 border border-rose-200 rounded-xl font-black uppercase text-[9px] tracking-widest transition hover:bg-rose-100 flex items-center gap-1">
                                        <x-lucide-x-circle class="w-3 h-3" /> Tolak
                                    </button>
                                @endif
                            @endif

                            {{-- Cancel Button (Admin or Self if Free) --}}
                            @if ($selectedRegistration->status !== 'cancelled')
                                <button wire:key="btn-cancel-{{ $selectedRegistration->uid }}"
                                    wire:click="promptConfirm('updateStatus', 'cancelled', 'Batalkan Pendaftaran', 'Yakin ingin MEMBATALKAN pendaftaran ini? Silakan berikan alasannya.', 'danger', true)"
                                    wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-slate-100 text-slate-600 border border-slate-200 rounded-xl font-black uppercase text-[9px] tracking-widest transition hover:bg-slate-200 flex items-center gap-1">
                                    <x-lucide-ban class="w-3 h-3" /> Batal
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Create Manual Modal --}}
    @if ($showCreateModal)
        <div x-data="{}"
            class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeCreateModal"></div>

            <div
                class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-5xl relative z-50 border border-slate-100 flex flex-col max-h-[95vh]">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">
                        {{ auth()->user()->can('master-pendaftaran.create') ? 'Pendaftaran Manual' : 'Form Pendaftaran Lomba' }}
                    </h3>
                    <button wire:click="closeCreateModal" class="text-slate-400 hover:text-slate-600 transition">
                        <x-lucide-x class="w-6 h-6" />
                    </button>
                </div>

                <div class="p-8 space-y-6 overflow-y-auto flex-1 custom-scrollbar">
                    {{-- Validation Errors --}}
                    @if (!empty($create_errors))
                        <div
                            class="bg-rose-50 border border-rose-200 rounded-[2rem] p-8 mb-4 flex flex-col md:flex-row items-start gap-6 animate-in fade-in slide-in-from-top-4 duration-300">
                            <div
                                class="w-14 h-14 bg-rose-500 text-white rounded-2xl flex items-center justify-center shrink-0 shadow-xl shadow-rose-200">
                                <x-lucide-alert-octagon class="w-8 h-8" />
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4
                                            class="text-lg font-black text-rose-900 uppercase tracking-tighter leading-tight">
                                            Pendaftaran Gagal Diproses</h4>
                                        <p class="text-[10px] font-bold text-rose-400 uppercase tracking-widest mt-1">
                                            Ditemukan kendala pada persyaratan lomba</p>
                                    </div>
                                    <button wire:click="$set('create_errors', [])"
                                        class="p-2 hover:bg-rose-100 rounded-xl transition text-rose-400">
                                        <x-lucide-x class="w-5 h-5" />
                                    </button>
                                </div>

                                <div class="mt-6 space-y-3">
                                    @foreach ($create_errors as $err)
                                        <div
                                            class="flex items-start gap-3 bg-white/60 p-4 rounded-2xl border border-rose-100/50 shadow-sm group">
                                            <div
                                                class="w-6 h-6 bg-rose-100 text-rose-600 rounded-lg flex items-center justify-center shrink-0 mt-0.5 group-hover:scale-110 transition">
                                                <x-lucide-x class="w-3.5 h-3.5" />
                                            </div>
                                            <span
                                                class="text-xs font-bold text-rose-700 leading-relaxed uppercase tracking-tight">{{ $err }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (auth()->user()->can('master-pendaftaran.create'))
                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Cari
                                & Pilih Peserta <span class="text-rose-500">*</span></label>

                            <div class="space-y-3 p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                                <!-- Search Input -->
                                <div class="relative">
                                    <x-lucide-search
                                        class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                                    <input type="text" wire:key="user-search-input"
                                        wire:model.live.debounce.300ms="userSearch"
                                        placeholder="Ketik nama atau email peserta untuk mencari..."
                                        class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-blue-100 border-b-2 focus:border-blue-500 outline-none transition shadow-sm placeholder:font-medium placeholder:text-slate-400">
                                </div>

                                <!-- User Checkboxes List -->
                                <div class="space-y-4 pr-2 overflow-y-auto custom-scrollbar"
                                    style="max-height: 200px;">
                                    @forelse($availableUsers as $clubName => $usersInClub)
                                        <div class="space-y-2">
                                            <div class="sticky top-0 z-10 bg-white/90 backdrop-blur-sm py-1">
                                                <h5
                                                    class="text-[10px] font-black text-blue-600 uppercase tracking-widest">
                                                    {{ $clubName }}</h5>
                                            </div>
                                            @foreach ($usersInClub as $usr)
                                                <label
                                                    class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-all {{ in_array($usr->uid, $create_user_uids) ? 'border-blue-500 bg-blue-50/50 shadow-sm' : 'border-slate-200 hover:border-blue-300 hover:bg-slate-50' }}">
                                                    <div class="relative flex items-center justify-center shrink-0">
                                                        <input type="checkbox" wire:model.live="create_user_uids"
                                                            value="{{ $usr->uid }}"
                                                            class="peer w-5 h-5 rounded-md border-2 border-slate-300 text-blue-600 focus:ring-blue-500 transition-all cursor-pointer appearance-none checked:bg-blue-600 checked:border-blue-600">
                                                        <x-lucide-check
                                                            class="w-3 h-3 text-white absolute pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity" />
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <h4
                                                            class="text-xs font-black text-slate-900 uppercase tracking-tight truncate">
                                                            {{ $usr->profile?->full_name ?: $usr->username ?? 'Deleted User' }}
                                                        </h4>
                                                        <p class="text-[10px] font-bold text-slate-500 truncate">
                                                            {{ $usr->email }}</p>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    @empty
                                        <div class="py-4 text-center text-slate-400 font-medium text-[10px]">-- Tidak
                                            ada
                                            peserta yang cocok --</div>
                                    @endforelse
                                </div>
                                <p
                                    class="text-[10px] text-slate-400 font-bold uppercase tracking-widest text-center mt-2">
                                    Daftar dibatasi 50 hasil. Anda dapat memilih lebih dari satu atlet sekaligus.</p>
                            </div>

                            {{-- PREVIEW DATA PESERTA (SLIDER) --}}
                            @if (count($create_user_uids) > 0)
                                @php
                                    $selectedUsersPreview = \App\Models\User::with('profile')
                                        ->whereIn('uid', $create_user_uids)
                                        ->get();
                                @endphp
                                @if ($selectedUsersPreview->count() > 0)
                                    <div
                                        class="mt-4 p-6 bg-blue-50/60 border border-blue-100 rounded-2xl shadow-sm relative">
                                        <div
                                            class="flex justify-between items-center mb-4 border-b border-blue-100 pb-2">
                                            <h4 class="text-xs font-black text-blue-500 uppercase tracking-widest">
                                                Preview
                                                Data Atlet ({{ $selectedUsersPreview->count() }} Terpilih)</h4>

                                            @if ($selectedUsersPreview->count() > 1)
                                                <div class="flex items-center gap-2">
                                                    <button wire:click="prevSlide" type="button"
                                                        class="w-6 h-6 rounded-full bg-white border border-blue-200 text-blue-500 flex items-center justify-center hover:bg-blue-50 transition"><x-lucide-chevron-left
                                                            class="w-4 h-4" /></button>
                                                    <span
                                                        class="text-[10px] font-black text-blue-600 w-8 text-center">{{ $activeSlide + 1 }}/{{ $selectedUsersPreview->count() }}</span>
                                                    <button wire:click="nextSlide" type="button"
                                                        class="w-6 h-6 rounded-full bg-white border border-blue-200 text-blue-500 flex items-center justify-center hover:bg-blue-50 transition"><x-lucide-chevron-right
                                                            class="w-4 h-4" /></button>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="relative overflow-hidden min-h-[300px]">
                                            @foreach ($selectedUsersPreview as $index => $userPreview)
                                                @if ($activeSlide === $index)
                                                    <div wire:key="slide-preview-{{ $userPreview->uid }}"
                                                        class="animate-in fade-in slide-in-from-right-4 duration-500">
                                                        <div class="flex items-center gap-4 mb-5">
                                                            @if ($userPreview->profile?->profile_picture)
                                                                <img src="{{ asset($userPreview->profile?->profile_picture) }}"
                                                                    class="w-14 h-14 rounded-2xl object-cover shadow-sm border border-blue-200">
                                                            @else
                                                                <div
                                                                    class="w-14 h-14 bg-white text-blue-400 rounded-2xl flex items-center justify-center border border-blue-200">
                                                                    <x-lucide-user class="w-6 h-6" />
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <p
                                                                    class="text-lg font-black text-slate-900 leading-none mt-1">
                                                                    {{ $userPreview->profile?->full_name ?: $userPreview->username ?? 'Deleted User' }}
                                                                </p>
                                                                <p class="text-[10px] font-bold text-slate-500 mt-0.5">
                                                                    {{ $userPreview->email }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="space-y-4">
                                                            <div class="grid grid-cols-2 gap-4">
                                                                <div>
                                                                    <label
                                                                        class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Tempat,
                                                                        Tgl Lahir</label>
                                                                    <p class="text-xs font-bold text-slate-800">
                                                                        {{ $userPreview->profile?->birth_place ?: '-' }},
                                                                        {{ $userPreview->profile?->birth_date ? \Carbon\Carbon::parse($userPreview->profile->birth_date)->format('d M Y') : '-' }}
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <label
                                                                        class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Jenis
                                                                        Kelamin</label>
                                                                    <p
                                                                        class="text-xs font-bold text-slate-800 uppercase">
                                                                        {{ $userPreview->profile?->gender ?: '-' }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="grid grid-cols-2 gap-4">
                                                                <div>
                                                                    <label
                                                                        class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">NIK
                                                                        / No. Identitas</label>
                                                                    <p class="text-xs font-bold text-slate-800">
                                                                        {{ $userPreview->profile?->identity_number ?: '-' }}
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <label
                                                                        class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Tinggi
                                                                        & Berat Badan</label>
                                                                    <p class="text-xs font-bold text-slate-800">
                                                                        {{ $userPreview->profile?->height ? $userPreview->profile->height . ' cm' : '-' }}
                                                                        /
                                                                        {{ $userPreview->profile?->weight ? $userPreview->profile->weight . ' kg' : '-' }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            @if ($userPreview->profile?->medical_history)
                                                                <div>
                                                                    <label
                                                                        class="block text-[9px] font-black text-rose-400 uppercase tracking-widest">Riwayat
                                                                        Medis</label>
                                                                    <p class="text-xs font-bold text-rose-600">
                                                                        {{ $userPreview->profile->medical_history }}
                                                                    </p>
                                                                </div>
                                                            @endif
                                                            <div class="grid grid-cols-2 gap-4">
                                                                <div>
                                                                    <label
                                                                        class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">No.
                                                                        Telp / WA</label>
                                                                    <p class="text-xs font-bold text-slate-800">
                                                                        {{ $userPreview->profile?->phone_number ?: '-' }}
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <label
                                                                        class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Klub
                                                                        Renang</label>
                                                                    <p
                                                                        class="text-xs font-bold text-slate-800 uppercase">
                                                                        {{ $userPreview->profile?->club?->name ?: 'INDEPENDENT' }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <label
                                                                    class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Alamat
                                                                    Lengkap</label>
                                                                <p class="text-xs font-bold text-slate-800">
                                                                    {{ $userPreview->profile?->address ?: '-' }}</p>
                                                            </div>
                                                            <div
                                                                class="grid grid-cols-2 gap-4 pt-3 border-t border-blue-100/50">
                                                                <div>
                                                                    <label
                                                                        class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Foto
                                                                        KTP / Identitas (Sbg KK)</label>
                                                                    @if ($userPreview->profile?->identity_photo)
                                                                        <a href="{{ route('document.view', ['type' => 'ktp', 'filename' => basename($userPreview->profile->identity_photo) ?: 'none']) }}"
                                                                            target="_blank"
                                                                            class="block w-full h-16 bg-white rounded-xl overflow-hidden border border-blue-100 hover:border-blue-400 transition relative group">
                                                                            <img src="{{ route('document.view', ['type' => 'ktp', 'filename' => basename($userPreview->profile->identity_photo) ?: 'none']) }}"
                                                                                class="w-full h-full object-cover">
                                                                            <div
                                                                                class="absolute inset-0 bg-slate-900/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                                                                <x-lucide-external-link
                                                                                    class="w-4 h-4 text-white" />
                                                                            </div>
                                                                        </a>
                                                                    @else
                                                                        <div
                                                                            class="w-full h-16 bg-white border border-dashed border-blue-200 rounded-xl flex items-center justify-center text-slate-400 text-[10px] font-bold">
                                                                            Belum Ada</div>
                                                                    @endif
                                                                </div>
                                                                <div>
                                                                    <label
                                                                        class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Foto
                                                                        Akte Kelahiran</label>
                                                                    @if ($userPreview->profile?->birth_certificate_photo)
                                                                        <a href="{{ route('document.view', ['type' => 'akta', 'filename' => basename($userPreview->profile->birth_certificate_photo) ?: 'none']) }}"
                                                                            target="_blank"
                                                                            class="block w-full h-16 bg-white rounded-xl overflow-hidden border border-blue-100 hover:border-blue-400 transition relative group">
                                                                            <img src="{{ route('document.view', ['type' => 'akta', 'filename' => basename($userPreview->profile->birth_certificate_photo) ?: 'none']) }}"
                                                                                class="w-full h-full object-cover">
                                                                            <div
                                                                                class="absolute inset-0 bg-slate-900/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                                                                <x-lucide-external-link
                                                                                    class="w-4 h-4 text-white" />
                                                                            </div>
                                                                        </a>
                                                                    @else
                                                                        <div
                                                                            class="w-full h-16 bg-white border border-dashed border-blue-200 rounded-xl flex items-center justify-center text-slate-400 text-[10px] font-bold">
                                                                            Belum Ada</div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @else
                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 flex items-center gap-4">
                            <div
                                class="w-12 h-12 bg-blue-500 text-white rounded-xl flex items-center justify-center shrink-0 shadow-lg shadow-blue-200">
                                <x-lucide-user-check class="w-6 h-6" />
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-blue-900 uppercase tracking-tighter leading-tight">
                                    Konfirmasi Peserta</h4>
                                <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mt-1">Anda
                                    mendaftar sebagai: <span
                                        class="text-blue-600">{{ auth()->user()->profile?->full_name ?: auth()->user()->username }}</span>
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 mt-8 border-t border-slate-100 pt-8">

                        {{-- LEFT COLUMN: PILIH KATEGORI LOMBA --}}
                        <div class="lg:col-span-3">
                            <div class="mb-4">
                                <h2 class="text-xl font-black text-slate-900 tracking-tighter uppercase italic">Pilih
                                    Kategori</h2>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Pilih
                                    Nomor Lomba (Bisa pilih lebih dari satu)</p>
                            </div>

                            <div class="space-y-4 p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                                <!-- Search Input -->
                                <div class="relative">
                                    <x-lucide-search
                                        class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                                    <input type="text" wire:key="lomba-search-input"
                                        wire:model.live.debounce.300ms="lombaSearch"
                                        placeholder="Ketik nama lomba atau event untuk mencari..."
                                        class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-blue-100 outline-none transition shadow-sm placeholder:font-medium placeholder:text-slate-400">
                                </div>

                                <!-- Event Checkboxes List -->
                                <div class="space-y-4 pr-4 pb-6 overflow-y-auto custom-scrollbar"
                                    style="max-height: 450px;">
                                    @forelse($availableCategories->groupBy('event_uid') as $eventUid => $cats)
                                        @php $event = $cats->first()->event; @endphp
                                        <div
                                            class="sticky top-0 bg-blue-500 z-20 py-3 pl-3 border-b border-slate-100 mb-2 rounded-lg">
                                            <span
                                                class="text-[14px] font-black text-white uppercase tracking-widest">{{ $event ? $event->name : 'Tanpa Event' }}</span>
                                        </div>
                                        @foreach ($cats as $cat)
                                            <label
                                                class="flex items-start gap-4 p-4 border rounded-2xl cursor-pointer transition-all {{ in_array($cat->uid, $create_event_categories) ? 'border-blue-500 bg-blue-50/50 shadow-sm' : 'border-slate-200 hover:border-green-300 hover:bg-slate-50' }}">
                                                <div class="relative flex items-center justify-center shrink-0 mt-1">
                                                    <input type="checkbox" wire:model.live="create_event_categories"
                                                        value="{{ $cat->uid }}"
                                                        class="peer w-6 h-6 rounded-md border-2 border-slate-300 text-blue-600 focus:ring-blue-500 transition-all cursor-pointer appearance-none checked:bg-green-600 checked:border-green-600">
                                                    <x-lucide-check
                                                        class="w-4 h-4 text-white absolute pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity" />
                                                </div>
                                                <div class="flex-1">
                                                    <h4
                                                        class="text-sm font-black text-slate-900 uppercase tracking-tight">
                                                        {{ $cat->acara_name }}</h4>
                                                    <div
                                                        class="flex flex-wrap items-center gap-3 mt-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                                        <span class="flex items-center gap-1"
                                                            title="Tanggal Pelaksanaan"><x-lucide-calendar
                                                                class="w-3 h-3" />
                                                            {{ $cat->start_date ? \Carbon\Carbon::parse($cat->start_date)->format('d M Y') : 'TBA' }}
                                                            {{ $cat->end_date && $cat->end_date != $cat->start_date ? '- ' . \Carbon\Carbon::parse($cat->end_date)->format('d M Y') : '' }}
                                                        </span>
                                                        <span class="flex items-center gap-1"
                                                            title="Waktu Pelaksanaan"><x-lucide-clock
                                                                class="w-3 h-3" />
                                                            {{ $cat->start_time ? \Carbon\Carbon::parse($cat->start_time)->format('H:i') : 'TBA' }}
                                                            {{ $cat->end_time ? '- ' . \Carbon\Carbon::parse($cat->end_time)->format('H:i') : '' }}
                                                            WIB</span>
                                                        <span class="flex items-center gap-1"
                                                            title="Lokasi"><x-lucide-map-pin class="w-3 h-3" />
                                                            {{ $cat->location ?: '-' }}</span>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    @if ($cat->type === 'paid')
                                                        <span
                                                            class="text-sm font-black text-slate-900 italic tracking-tighter">Rp{{ number_format($cat->registration_fee, 0, ',', '.') }}</span>
                                                    @else
                                                        <span
                                                            class="text-sm font-black text-emerald-500 italic tracking-tighter uppercase">Gratis</span>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    @empty
                                        <div
                                            class="py-10 text-center border-2 border-dashed border-slate-200 rounded-2xl">
                                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">--
                                                Tidak ada lomba yang cocok --</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT COLUMN: PEMBAYARAN & KONFIRMASI --}}
                        <div class="lg:col-span-2 relative">
                            <div class="bg-slate-50/50 border border-slate-100 rounded-3xl p-6 sticky top-0">
                                <div class="mb-6">
                                    <h2 class="text-xl font-black text-slate-900 tracking-tighter uppercase italic">
                                        Ringkasan</h2>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                                        Selesaikan Pendaftaran Anda</p>
                                </div>

                                @php
                                    // Hitung total biaya dari kategori yang dipilih untuk semua user
                                    $totalBiaya = 0;
                                    $isPaidEventSelected = false;
                                    $userCount = count($create_user_uids) > 0 ? count($create_user_uids) : 1;

                                    foreach ($create_event_categories as $selUid) {
                                        $selCat = \App\Models\EventCategory::where('uid', $selUid)->first();
                                        if ($selCat && $selCat->type === 'paid') {
                                            $totalBiaya += $selCat->registration_fee * $userCount;
                                            $isPaidEventSelected = true;
                                        }
                                    }
                                @endphp

                                @if (count($create_event_categories) === 0)
                                    <div
                                        class="bg-white border border-slate-200 rounded-2xl p-8 text-center shadow-sm">
                                        <div
                                            class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <x-lucide-shopping-cart class="w-8 h-8" />
                                        </div>
                                        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">Belum
                                            Ada Pilihan</h3>
                                        <p class="text-xs font-bold text-slate-400 mt-2">Pilih minimal satu lomba di
                                            samping untuk melanjutkan.</p>
                                    </div>
                                @elseif(!$isPaidEventSelected)
                                    {{-- JIKA HANYA LOMBA GRATIS --}}
                                    <div
                                        class="bg-white border border-emerald-100 rounded-2xl p-8 text-center shadow-sm relative overflow-hidden">
                                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-white z-0">
                                        </div>
                                        <div class="relative z-10">
                                            <div
                                                class="w-16 h-16 bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
                                                <x-lucide-gift class="w-8 h-8" />
                                            </div>
                                            <h3
                                                class="text-lg font-black text-emerald-600 uppercase tracking-tighter italic mb-1">
                                                Pendaftaran Gratis</h3>
                                            <p
                                                class="text-[10px] font-bold text-emerald-600/70 uppercase tracking-widest">
                                                Tidak ada tagihan untuk pendaftaran ini.</p>
                                        </div>
                                    </div>
                                @else
                                    {{-- JIKA ADA LOMBA BERBAYAR --}}
                                    <div
                                        class="bg-ksc-blue rounded-3xl p-6 text-white shadow-xl shadow-blue-900/20 relative overflow-hidden mb-6">
                                        <div
                                            class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl">
                                        </div>
                                        <div class="flex justify-between items-start mb-6 relative z-10">
                                            <div>
                                                <p
                                                    class="text-[10px] font-black text-blue-200 uppercase tracking-widest mb-1">
                                                    Total Biaya</p>
                                                <p
                                                    class="text-3xl font-black text-amber-400 italic tracking-tighter leading-none">
                                                    Rp {{ number_format($totalBiaya, 0, ',', '.') }}</p>
                                            </div>
                                            <div
                                                class="px-3 py-1 bg-white/10 backdrop-blur border border-white/20 rounded-lg">
                                                <span
                                                    class="text-[10px] font-black text-white tracking-widest uppercase">Invoice</span>
                                            </div>
                                        </div>
                                        <div
                                            class="pt-4 border-t border-white/10 flex justify-between items-end relative z-10">
                                            <div>
                                                <p
                                                    class="text-[9px] font-bold text-blue-200 uppercase tracking-widest mb-0.5">
                                                    Metode Terpilih</p>
                                                <p class="text-xs font-black text-white uppercase tracking-widest">
                                                    {{ $create_payment_method === 'cash' ? 'Tunai (Cash)' : 'Transfer Bank' }}
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p
                                                    class="text-[9px] font-bold text-blue-200 uppercase tracking-widest mb-0.5">
                                                    Status</p>
                                                <p class="text-xs font-black text-white uppercase tracking-widest">
                                                    {{ $create_status }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <label
                                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Metode
                                                Pembayaran</label>
                                            <select wire:model.live="create_payment_method"
                                                class="w-full px-4 py-3.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-700 focus:ring-4 focus:ring-blue-100 outline-none transition uppercase tracking-widest">
                                                @can('master-pendaftaran.pay.cash')
                                                    <option value="cash">💵 Tunai (Cash)</option>
                                                @endcan
                                                @php
                                                    $accNames = collect();
                                                    foreach ($create_event_categories as $selUid) {
                                                        $cat = \App\Models\EventCategory::with('event.financeAccount')
                                                            ->where('uid', $selUid)
                                                            ->first();
                                                        if ($cat?->event?->financeAccount) {
                                                            $accNames->push($cat->event->financeAccount->bank_name);
                                                        }
                                                    }
                                                    $uniqueAccNames = $accNames->unique();
                                                @endphp
                                                @if ($uniqueAccNames->count() > 0)
                                                    @foreach ($uniqueAccNames as $name)
                                                        <option value="transfer">💳 {{ $name }}</option>
                                                    @endforeach
                                                @else
                                                    <option value="transfer">💳 Transfer Bank</option>
                                                @endif
                                            </select>
                                        </div>

                                        @if ($create_payment_method === 'transfer')
                                            <div class="animate-in fade-in slide-in-from-top-2 duration-300 space-y-4">
                                                {{-- TAMPILKAN REKENING TUJUAN --}}
                                                @php
                                                    $selectedFinanceAccounts = collect();
                                                    foreach ($create_event_categories as $selUid) {
                                                        $selCat = \App\Models\EventCategory::with(
                                                            'event.financeAccount',
                                                        )
                                                            ->where('uid', $selUid)
                                                            ->first();
                                                        if (
                                                            $selCat &&
                                                            $selCat->event &&
                                                            $selCat->event->financeAccount
                                                        ) {
                                                            $selectedFinanceAccounts->push(
                                                                $selCat->event->financeAccount,
                                                            );
                                                        }
                                                    }
                                                    $uniqueAccounts = $selectedFinanceAccounts->unique('uid');
                                                @endphp

                                                @if ($uniqueAccounts->count() > 0)
                                                    <div
                                                        class="p-6 bg-amber-50/50 rounded-3xl border border-amber-200 shadow-inner">
                                                        <label
                                                            class="block text-[10px] font-black text-amber-600 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                                            <x-lucide-info class="w-4 h-4" /> Detail Tujuan Pembayaran
                                                        </label>
                                                        <div class="grid grid-cols-1 gap-4">
                                                            @foreach ($uniqueAccounts as $acc)
                                                                <div
                                                                    class="bg-white rounded-2xl border border-amber-100 shadow-sm overflow-hidden">
                                                                    @if ($acc->image)
                                                                        <div
                                                                            class="w-full p-4 bg-white flex justify-center border-b border-slate-50">
                                                                            <a href="{{ asset($acc->image) }}"
                                                                                target="_blank"
                                                                                class="group relative block cursor-zoom-in"
                                                                                title="Klik untuk memperbesar">
                                                                                <img src="{{ asset($acc->image) }}"
                                                                                    class="max-h-64 w-auto object-contain rounded-xl shadow-md transition duration-300 group-hover:scale-[1.02]">
                                                                                <div
                                                                                    class="absolute inset-0 bg-slate-900/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-xl">
                                                                                    <div
                                                                                        class="bg-white/20 backdrop-blur-md p-2 rounded-lg border border-white/30">
                                                                                        <x-lucide-maximize-2
                                                                                            class="w-5 h-5 text-white" />
                                                                                    </div>
                                                                                </div>
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                    <div
                                                                        class="p-5 flex items-center justify-between gap-4">
                                                                        <div class="flex items-center gap-4">
                                                                            @if (!$acc->image)
                                                                                <div
                                                                                    class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center shrink-0">
                                                                                    <x-lucide-landmark
                                                                                        class="w-6 h-6 text-amber-600" />
                                                                                </div>
                                                                            @endif
                                                                            <div class="flex-1 min-w-0">
                                                                                <p
                                                                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1.5">
                                                                                    {{ $acc->bank_name }}</p>
                                                                                <p
                                                                                    class="text-lg font-black text-slate-900 tracking-tight leading-none mb-1.5">
                                                                                    {{ $acc->account_number }}</p>
                                                                                <p
                                                                                    class="text-[11px] font-bold text-amber-600 uppercase tracking-tight">
                                                                                    {{ $acc->account_name }}</p>
                                                                            </div>
                                                                        </div>
                                                                        <button type="button"
                                                                            onclick="navigator.clipboard.writeText('{{ $acc->account_number }}'); alert('Nomor rekening berhasil disalin!');"
                                                                            class="flex flex-col items-center gap-1 p-3 hover:bg-amber-50 text-amber-600 rounded-2xl transition group">
                                                                            <x-lucide-copy
                                                                                class="w-5 h-5 group-hover:scale-110 transition" />
                                                                            <span
                                                                                class="text-[8px] font-black uppercase tracking-tighter">Salin</span>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                <div>
                                                    <label
                                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Unggah
                                                        Bukti Transfer</label>
                                                    <div class="relative group cursor-pointer" wire:ignore>
                                                        <input type="file" id="mp_payment_proof" accept="image/*"
                                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20"
                                                            onchange="
                                                                const file = this.files[0];
                                                                if (!file) return;
                                                                if (file.size > 5242880) { alert('Ukuran file terlalu besar! Maksimal 5MB.'); this.value=''; return; }
                                                                const reader = new FileReader();
                                                                reader.onload = function(e) {
                                                                    const previewImg = document.getElementById('mp_proof_preview');
                                                                    const placeholder = document.getElementById('mp_proof_placeholder');
                                                                    const uploaded = document.getElementById('mp_proof_uploaded');
                                                                    if (previewImg) { previewImg.src = e.target.result; previewImg.classList.remove('hidden'); }
                                                                    if (placeholder) placeholder.classList.add('hidden');
                                                                    if (uploaded) uploaded.classList.remove('hidden');
                                                                };
                                                                reader.readAsDataURL(file);
                                                            ">
                                                        <div
                                                            class="w-full h-32 bg-white border-2 border-dashed border-slate-200 group-hover:border-blue-400 group-hover:bg-blue-50/30 rounded-2xl flex flex-col items-center justify-center transition-all relative z-10 overflow-hidden">
                                                            <img id="mp_proof_preview" src=""
                                                                class="absolute inset-0 w-full h-full object-cover opacity-30 hidden">
                                                            <div id="mp_proof_placeholder"
                                                                class="flex flex-col items-center justify-center relative z-10">
                                                                <x-lucide-camera
                                                                    class="w-8 h-8 text-slate-300 group-hover:text-blue-400 transition-colors mb-2" />
                                                                <span
                                                                    class="text-[10px] font-black text-slate-400 group-hover:text-blue-500 uppercase tracking-widest transition-colors">Pilih
                                                                    Foto Bukti</span>
                                                            </div>
                                                            <div id="mp_proof_uploaded"
                                                                class="hidden flex-col items-center justify-center relative z-10">
                                                                <x-lucide-check-circle
                                                                    class="w-8 h-8 text-blue-600 mb-2" />
                                                                <span
                                                                    class="text-xs font-black text-blue-700 uppercase tracking-widest">Bukti
                                                                    Terunggah</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @error('create_payment_proof')
                                                    <span
                                                        class="text-xs font-bold text-rose-500 mt-1 block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @if (auth()->user()->can('master-pendaftaran.create'))
                                    <div class="mt-6 pt-6 border-t border-slate-200/60">
                                        <label
                                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Status
                                            Akhir Pendaftaran</label>
                                        <select wire:model="create_status"
                                            class="w-full px-4 py-3.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-blue-100 outline-none transition uppercase">
                                            <option value="pending" selected>Pending (Menunggu)</option>
                                            <option value="confirmed">Confirmed (Disetujui Langsung)</option>
                                        </select>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-8 border-t border-slate-50 flex justify-end gap-3 bg-slate-50/30">
                    <button wire:click="closeCreateModal"
                        class="px-8 py-3.5 bg-white text-slate-600 rounded-2xl text-[10px] font-black uppercase tracking-widest border border-slate-200 hover:bg-slate-50 transition">Batal</button>
                    <button type="button" id="mpSaveBtn"
                        @click="
                            const proofFile = document.getElementById('mp_payment_proof')?.files[0];
                            if (proofFile) {
                                document.getElementById('mpSaveText').classList.add('hidden');
                                document.getElementById('mpSaveLoading').classList.remove('hidden');
                                document.getElementById('mpSaveBtn').disabled = true;
                                new Promise((resolve, reject) => { @this.upload('create_payment_proof', proofFile, resolve, reject); })
                                    .then(() => { @this.call('saveManualRegistration'); })
                                    .catch(() => {
                                        alert('Gagal mengunggah bukti transfer. Silakan coba lagi.');
                                        document.getElementById('mpSaveText').classList.remove('hidden');
                                        document.getElementById('mpSaveLoading').classList.add('hidden');
                                        document.getElementById('mpSaveBtn').disabled = false;
                                    });
                            } else {
                                @this.call('saveManualRegistration');
                            }
                        "
                        class="px-8 py-3.5 bg-ksc-blue text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-blue-100 hover:bg-blue-700 transition transform hover:-translate-y-1 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-2">
                        <span id="mpSaveText">Simpan Pendaftaran</span>
                        <span id="mpSaveLoading" class="hidden flex items-center gap-2">
                            <x-lucide-loader-2 class="w-4 h-4 animate-spin" />
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Confirm Modal --}}
    @if ($showConfirmModal)
        <div class="fixed inset-0 z-[100] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeConfirmModal"></div>

            <div
                class="bg-white rounded-3xl overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-md relative z-[100] border border-slate-100 flex flex-col p-8 text-center animate-in zoom-in-95 duration-200">
                <div
                    class="mx-auto w-20 h-20 mb-5 rounded-[2rem] flex items-center justify-center shadow-inner {{ $confirmType === 'success' ? 'bg-emerald-100 text-emerald-500' : 'bg-rose-100 text-rose-500' }}">
                    @if ($confirmType === 'success')
                        <x-lucide-check-circle class="w-10 h-10" />
                    @else
                        <x-lucide-alert-triangle class="w-10 h-10" />
                    @endif
                </div>

                <h3 class="text-2xl font-black text-slate-900 tracking-tighter uppercase mb-2">{{ $confirmTitle }}
                </h3>
                <p class="text-sm font-bold text-slate-500 mb-8">{{ $confirmMessage }}</p>

                @if ($withInput)
                    <div class="mb-6 text-left">
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Alasan
                            Penolakan</label>
                        <textarea wire:model="confirmInput"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-rose-100 focus:border-rose-300 outline-none transition placeholder-slate-400"
                            rows="3" placeholder="Tuliskan alasan penolakan..."></textarea>
                    </div>
                @endif

                <div class="flex gap-3 justify-center">
                    <button wire:click="closeConfirmModal"
                        class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition">Batal</button>
                    <button wire:click="executeConfirm" wire:loading.attr="disabled" wire:target="executeConfirm"
                        class="flex-1 px-6 py-4 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl transition transform hover:-translate-y-1 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-2 {{ $confirmType === 'success' ? 'bg-emerald-500 hover:bg-emerald-600 shadow-emerald-200' : 'bg-rose-500 hover:bg-rose-600 shadow-rose-200' }}">
                        <span wire:loading.remove wire:target="executeConfirm">Ya, Lanjutkan</span>
                        <span wire:loading wire:target="executeConfirm" class="flex items-center gap-2">
                            <x-lucide-loader-2 class="w-4 h-4 animate-spin" />
                            Memproses...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@script
    <script>
        $wire.on('open-invoice-url', (event) => {
            const url = event.url || (Array.isArray(event) ? event[0].url : null);
            if (url) {
                const a = document.createElement('a');
                a.href = url;
                a.target = '_blank';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }
        });

        $wire.on('reload-page', () => {
            setTimeout(() => {
                window.location.reload();
            }, 20000); // Give time for new tab to open and notification to appear
        });
    </script>
@endscript
