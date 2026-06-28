<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Gallery;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Mail\ContactNotificationMail;
use Illuminate\Support\Facades\Mail;

class HomepageController extends Controller
{
    public function contactProcess(Request $request)
    {
        $data = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subjek' => 'required|string|max:255',
            'pesan' => 'required|string',
        ]);

        try {
            $receiver = env('MAIL_RECEIVER_ADDRESS', 'khafid.swimmingclub16@gmail.com');
            Mail::to($receiver)->send(new ContactNotificationMail($data, $request->ip()));

            return back()->with('notification', [
                'status' => 'success',
                'message' => 'Pesan Anda telah berhasil dikirim. Kami akan segera menghubungi Anda.',
                'duration' => 5000
            ]);
        } catch (\Exception $e) {
            return back()->with('notification', [
                'status' => 'error',
                'message' => 'Gagal mengirim pesan. Silakan coba lagi nanti. Error: ' . $e->getMessage(),
                'duration' => 5000
            ])->withInput();
        }
    }

    public function homepage()
    {
        $events = Event::query()
            ->with(['categories.category', 'author.profile'])
            ->whereIn('status', ['ongoing', 'upcoming'])
            ->orderBy('start_date', 'asc')
            ->limit(4)
            ->get()
            ->map(function($event) {
                return [
                    'uid' => $event->uid,
                    'slug' => $event->slug,
                    'nama_event' => $event->name,
                    'banner_event' => $event->banner,
                    'tipe_event' => $event->categories->first()->type ?? 'gratis',
                    'kategori' => $event->categories->first()->category->name ?? 'Umum',
                    'status_event' => $this->mapStatus($event->status),
                    'tanggal_event' => $event->start_date,
                    'waktu_event' => $event->start_time,
                    'lokasi_event' => $event->location,
                ];
            });

        $galleries = Gallery::query()
            ->with('event')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function($gallery) {
                return [
                    'foto_event' => $gallery->cover_image,
                    'nama_foto' => $gallery->title,
                ];
            });

        return view('homepage.beranda', [
            'title' => 'Khafid Swimming Club (KSC) - Official Website | Beranda',
            'events' => $events,
            'galleries' => $galleries
        ]);
    }

    public function events(Request $request, $keyword = null, $page = 1)
    {
        // Handle search and pagination parameters from route
        $currentKeyword = $keyword;
        $currentPage = $page;

        $query = Event::query()
            ->with(['categories.category', 'author.profile'])
            ->withCount(['categories as registrations_count' => function($q) {
                $q->join('registrations', 'event_categories.uid', '=', 'registrations.event_category_uid');
            }]);

        if ($currentKeyword) {
            $query->where(function($q) use ($currentKeyword) {
                $q->where('name', 'like', "%{$currentKeyword}%")
                  ->orWhere('location', 'like', "%{$currentKeyword}%");
            });
        }

        $pagination = $query->orderBy('start_date', 'desc')->paginate(9, ['*'], 'page', $currentPage);

        $pagination->getCollection()->transform(function($event) {
            $totalReg = DB::table('registrations')
                ->join('event_categories', 'registrations.event_category_uid', '=', 'event_categories.uid')
                ->where('event_categories.event_uid', $event->uid)
                ->count();

            return [
                'uid' => $event->uid,
                'slug' => $event->slug,
                'nama_event' => $event->name,
                'banner_event' => $event->banner,
                'biaya_event' => $event->categories->min('registration_fee') ?? 0,
                'status_event' => $this->mapStatus($event->status),
                'tanggal_mulai' => $event->start_date,
                'lokasi_event' => $event->location,
                'registrations_count' => $totalReg,
                'jumlah_lintasan' => $event->lane_count,
                'eventCategories' => $event->categories->map(function($cat) {
                    return [
                        'category' => ['nama_kategori' => $cat->category->name ?? 'Swimming']
                    ];
                })->toArray()
            ];
        });

        return view('homepage.event', [
            'title' => "Khafid Swimming Club (KSC) - Official Website | Event",
            'events' => $pagination->toArray(),
            'currentKeyword' => $currentKeyword,
        ]);
    }

    public function eventDetail($slug, $uid)
    {
        $event = Event::with('categories.category')->where('uid', $uid)->firstOrFail();

        // Generate keywords from name and location
        $keywords = $event->name . ', ' . $event->location . ', Khafid Swimming Club, KSC, Lomba Renang Sidoarjo';

        // Get the first category name if available
        $categoryName = $event->categories->first()?->category?->name ?? 'Event Renang';

        return view('homepage.event-detail', [
            'title' => $event->name . ' | Khafid Swimming Club',
            'meta_description' => strip_tags(\Illuminate\Support\Str::limit($event->description, 160)),
            'meta_keywords' => $keywords,
            'meta_image' => $event->banner ? url($event->banner) : null,
            'category' => $categoryName,
            'published_time' => $event->created_at->toIso8601String(),
            'event' => [
                'banner_event' => $event->banner
            ]
        ]);
    }

    public function gallery(Request $request)
    {
        $eventUid = $request->get('event');
        $page = (int)$request->get('page', 1);

        $events = Event::query()->select(['uid', 'name as nama_event'])->get();

        $query = Gallery::query()->where('is_active', true);

        if ($eventUid) {
            $query->where('event_uid', $eventUid);
        }

        $pagination = $query->orderBy('created_at', 'desc')->paginate(24, ['*'], 'page', $page);

        return view('homepage.galeri', [
            'title' => 'Khafid Swimming Club (KSC) - Official Website | Galeri',
            'events' => $events->toArray(),
            'galleries' => $pagination->getCollection()->map(function($g) {
                return [
                    'foto_event' => $g->cover_image,
                    'nama_event' => $g->title
                ];
            })->toArray(),
            'pagination' => $pagination->toArray(),
            'activeEvent' => $eventUid,
        ]);
    }

    public function facilities()
    {
        return view('homepage.fasilitas', [
            'title' => 'Khafid Swimming Club (KSC) - Official Website | Fasilitas',
            'user' => Auth::user(),
        ]);
    }

    public function contact()
    {
        return view('homepage.kontak', [
            'title' => 'Khafid Swimming Club (KSC) - Official Website | Kontak',
            'user' => Auth::user(),
        ]);
    }

    public function coaches()
    {
        $coaches = User::role('pelatih')->with('profile')->get();
        $mentors = $coaches->map(function($coach) {
            return [
                'nama_lengkap' => $coach->profile->full_name ?? $coach->username,
                'foto_profil' => $coach->profile->profile_picture ?? 'default.jpg',
                'no_telepon' => $coach->profile->phone_number ?? '',
            ];
        });

        return view('homepage.pelatih', [
            'title' => 'Khafid Swimming Club (KSC) - Official Website | Pelatih',
            'mentors' => $mentors,
            'user' => Auth::user(),
        ]);
    }

    public function aboutUs()
    {
        $coaches = User::role('pelatih')->with('profile')->limit(3)->get();
        $mentors = $coaches->map(function($coach) {
            return [
                'nama_lengkap' => $coach->profile->full_name ?? $coach->username,
                'foto_profil' => $coach->profile->profile_picture ?? 'default.jpg',
                'no_telepon' => $coach->profile->phone_number ?? '',
            ];
        });

        return view('homepage.tentang-kami', [
            'title' => 'Khafid Swimming Club (KSC) - Official Website | Tentang Kami',
            'mentors' => $mentors,
            'user' => Auth::user(),
        ]);
    }

    private function mapStatus($status)
    {
        $map = [
            'ongoing' => 'berjalan',
            'upcoming' => 'mendatang',
            'completed' => 'selesai',
            'cancelled' => 'dibatalkan',
            'draft' => 'draft'
        ];
        return $map[$status] ?? $status;
    }
}
