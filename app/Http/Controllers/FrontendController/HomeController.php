<?php

namespace App\Http\Controllers\FrontendController;

use App\Http\Controllers\Controller;
use App\Mail\RegistrationMail;
use App\Models\Kontak;
use Illuminate\Http\Request;
use App\Models\Berita;
use App\Models\Beranda;
use App\Models\Daftar;
use App\Models\Menu;
use App\Models\Images;
use App\Models\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        // Fetching data for berita and program studi (examples)
        $berita = Berita::all(); // Adjust query as needed
        // Fetch the data from the 'beranda' table with a join to the 'page' table
        $beranda = Beranda::leftJoin('page', 'page.page_id', '=', 'beranda.page_id')
            ->where('tipe', 'posisi1')
            ->select('beranda.*', 'page.*') // Select specific fields if necessary
            ->first();

        $data = [
            'form_fee' => 150000,
            'initial_fee' => [
                'S1D3' => 600000,
                'ners' => 1250000,
            ],
            'sma_ke_s1' => [
                [
                    'jurusan' => 'S1 Bisnis Digital',
                    'spp' => '400.000',
                    
                ],
                [
                    'jurusan' => 'S1 Pariwisata',
                    'spp' => '400.000',
                    
                ],
                [
                    'jurusan' => 'S1 Kewirausahaan',
                    'spp' => '400.000',
                    
                ],
                [
                    'jurusan' => 'S1 Administrasi Rumah Sakit',
                    'spp' => '450.000',
                    
                ],
                [
                    'jurusan' => 'S1 K3 ( Kesehatan Keselamatan Kerja )',
                    'spp' => '450.000',
                    
                ],
                [
                    'jurusan' => 'S1 Keperawatan',
                    'spp' => '600.000',
                    
                ],
                [
                    'jurusan' => 'D3 Keperawatan',
                    'spp' => '750.000',
                    
                ],
            ],
            'd3_ke_s1' => [
                [
                    'jurusan' => 'Profesi Ners',
                    'spp' => '1.550.000',
                ],
            ],
        ];

        // Fetch main menu items (submenu_id=0)
        $mainMenus = Menu::where('submenu_id', 0)->orderBy('urutan', 'asc')->get();

        // Fetch all submenus
        $subMenus = Menu::where('submenu_id', '!=', 0)->get();
        $images = Images::where('kategori', 'Event Kampus')->get();
        $galleryImages = Images::all();

        return view('Frontend.home', compact('berita', 'data', 'beranda', 'mainMenus', 'subMenus', 'images', 'galleryImages'));
    }

    public function detailberita($id)
    {
        $mainMenus = Menu::where('submenu_id', 0)->orderBy('urutan', 'asc')->get();
        $subMenus = Menu::where('submenu_id', '!=', 0)->get();

        $berita = Berita::findOrFail($id);

        return view('Frontend.detail-berita', compact('mainMenus', 'subMenus', 'berita'));
    }

    public function success()
    {
        return view('Frontend.Success');
    }

    public function biayajurusan()
    {
        $mainMenus = Menu::where('submenu_id', 0)->orderBy('urutan', 'asc')->get();
        $data = [
            'form_fee' => 150000,
            'initial_fee' => [
                'S1D3' => 600000,
                'ners' => 1250000,
            ],
            'sma_ke_s1' => [
                [
                    'jurusan' => 'S1 Bisnis Digital',
                    'spp' => '400.000',
                    
                ],
                [
                    'jurusan' => 'S1 Pariwisata',
                    'spp' => '400.000',
                    
                ],
                [
                    'jurusan' => 'S1 Kewirausahaan',
                    'spp' => '400.000',
                    
                ],
                [
                    'jurusan' => 'S1 Administrasi Rumah Sakit',
                    'spp' => '450.000',
                    
                ],
                [
                    'jurusan' => 'S1 K3 ( Kesehatan Keselamatan Kerja )',
                    'spp' => '450.000',
                    
                ],
                [
                    'jurusan' => 'S1 Keperawatan',
                    'spp' => '600.000',
                    
                ],
                [
                    'jurusan' => 'D3 Keperawatan',
                    'spp' => '750.000',
                    
                ],
            ],
            'd3_ke_s1' => [
                [
                    'jurusan' => 'Profesi Ners',
                    'spp' => '1.550.000',
                ],
            ],
        ];

        // Fetch all submenus
        $subMenus = Menu::where('submenu_id', '!=', 0)->get();

        return view('Frontend.allbiayajurusan', compact('mainMenus', 'subMenus', 'data'));
    }

    public function storeKontak(Request $request)
    {
        $request->validate(
            [
                'nama' => 'required|string|max:255',
                'no_telp' => 'required|numeric|digits_between:10,15',
                'email' => 'required|email|max:255',
                'alamat' => 'nullable|string|max:255',
                'pesan' => 'required|string|max:500',
                'g-recaptcha-response' => 'required', // Validasi reCAPTCHA
            ],
            [
                'g-recaptcha-response.required' => 'Captcha wajib diisi.',
            ],
        );

        // Verifikasi reCAPTCHA
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => 'your-secret-key',
            'response' => $request->input('g-recaptcha-response'),
        ]);

        if (!$response->json('success')) {
            return back()->withErrors(['captcha' => 'Captcha tidak valid.']);
        }

        // Simpan data ke database
        Kontak::create([
            'nama' => $request->nama,
            'no_telp' => $request->no_telp,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'pesan' => $request->pesan,
        ]);

        // Redirect dengan pesan sukses
        return redirect()->back()->with('success', 'Pesan Anda telah berhasil dikirim. Terima kasih atas informasinya.');
    }

    public function sejarah()
    {
        // Fetch main menu items (submenu_id=0)
        $mainMenus = Menu::where('submenu_id', 0)->orderBy('urutan', 'asc')->get();

        // Fetch all submenus
        $subMenus = Menu::where('submenu_id', '!=', 0)->get();

        return view('Frontend.sejarah', compact('mainMenus', 'subMenus'));
    }

    public function visimisi()
    {
        // Fetch main menu items (submenu_id=0)
        $mainMenus = Menu::where('submenu_id', 0)->orderBy('urutan', 'asc')->get();

        // Fetch all submenus
        $subMenus = Menu::where('submenu_id', '!=', 0)->get();

        return view('Frontend.visimisi', compact('mainMenus', 'subMenus'));
    }

    public function strukturorganisasi()
    {
        // Fetch main menu items (submenu_id=0)
        $mainMenus = Menu::where('submenu_id', 0)->orderBy('urutan', 'asc')->get();

        // Fetch all submenus
        $subMenus = Menu::where('submenu_id', '!=', 0)->get();

        return view('Frontend.struktur', compact('mainMenus', 'subMenus'));
    }

    public function kontak()
    {
        // Fetch main menu items (submenu_id=0)
        $mainMenus = Menu::where('submenu_id', 0)->orderBy('urutan', 'asc')->get();

        // Fetch all submenus
        $subMenus = Menu::where('submenu_id', '!=', 0)->get();

        return view('Frontend.kontak', compact('mainMenus', 'subMenus'));
    }

    public function brosur()
    {
        // Fetch main menu items (submenu_id=0)
        $mainMenus = Menu::where('submenu_id', 0)->orderBy('urutan', 'asc')->get();

        // Fetch all submenus
        $subMenus = Menu::where('submenu_id', '!=', 0)->get();

        return view('Frontend.brosur', compact('mainMenus', 'subMenus'));
    }

    public function pageShow($id)
    {
        // Fetch main menu items (submenu_id=0)
        $mainMenus = Menu::where('submenu_id', 0)->orderBy('urutan', 'asc')->get();

        // Fetch all submenus
        $subMenus = Menu::where('submenu_id', '!=', 0)->get();

        $page = Page::findOrFail($id); // Fetch page by ID
        return view('Frontend.page', compact('mainMenus', 'subMenus', 'page')); // Pass the data to the view
    }

    public function showForm(Request $request)
    {
        // Ambil nilai "step" dari query string, default ke 1 jika tidak ada
        $step = $request->query('step', 1);
        $pilihan = '';
        $mainMenus = Menu::where('submenu_id', 0)->orderBy('urutan', 'asc')->get();
        $subMenus = Menu::where('submenu_id', '!=', 0)->get();

        $kelas = $request->kelas;

        return view('Frontend.daftaronline', compact('step', 'pilihan', 'mainMenus', 'subMenus', 'kelas'));
    }

     public function storeRegistration(Request $request)
    {
        $request->merge([
            'no_hp' => preg_replace('/[^0-9]/', '', $request->no_hp),
            'no_wa' => preg_replace('/[^0-9]/', '', $request->no_wa),
         ]);
         
        $request->validate(
            [
                'kelas' => 'required|string',
                'nama' => 'required|string|max:255',
                'jk' => 'required|string|in:L,P',
                'kampus' => 'required|string|max:255',
                'alamat_ktp' => 'required|string|max:255',
                'alamat_dom' => 'required|string|max:255',
                'tmpt_lahir' => 'required|string|max:255',
                'tgl_lahir' => 'required|date|before:today|after:1900-01-01',
                'ktp' => 'required|numeric|digits:16|unique:daftar,no_ktp',
                'jurusan' => 'required|string|max:255',
                'no_hp' => 'required|regex:/^[0-9]{10,13}$/',
                'no_wa' => 'required|regex:/^[0-9]{10,13}$/',
                'email' => 'required|email|max:255',
                'agama' => 'required|string|max:50',
                'ibu' => 'required|string|max:255',
                'ayah' => 'required|string|max:255',
                'jaket' => 'required|string|max:10',
                'lulusan' => 'required|string|max:255',
                'biaya' => 'required',
                'kerja' => 'nullable|string|max:255',
                'al_kerja' => 'nullable|string|max:255',
                'no_kantor' => 'nullable|numeric|digits_between:10,13',
                'g-recaptcha-response' => 'required',
            ],
            [
                // Pesan error untuk validasi setiap field
                'kelas.required' => 'Kelas wajib diisi.',
                'kelas.string' => 'Kelas harus berupa teks.',
                
                'tgl_lahir.date' => 'Tanggal lahir harus berupa format tanggal yang valid.',
                'tgl_lahir.before' => 'Anda harus berusia minimal 18 tahun.',
                'tgl_lahir.required' => 'Tanggal lahir wajib di isi.',

                'nama.required' => 'Nama lengkap wajib diisi.',
                'nama.string' => 'Nama lengkap harus berupa teks.',
                'nama.max' => 'Nama lengkap maksimal 255 karakter.',

                'jk.required' => 'Jenis kelamin wajib diisi.',
                'jk.string' => 'Jenis kelamin harus berupa teks.',
                'jk.in' => 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan).',

                'kampus.required' => 'Nama kampus wajib diisi.',
                'kampus.string' => 'Nama kampus harus berupa teks.',
                'kampus.max' => 'Nama kampus maksimal 255 karakter.',

                'alamat_ktp.string' => 'Alamat KTP harus berupa teks.',
                'alamat_ktp.max' => 'Alamat KTP maksimal 255 karakter.',

                'alamat_dom.string' => 'Alamat domisili harus berupa teks.',
                'alamat_dom.max' => 'Alamat domisili maksimal 255 karakter.',

                'tmpt_lahir.string' => 'Tempat lahir harus berupa teks.',
                'tmpt_lahir.max' => 'Tempat lahir maksimal 255 karakter.',

                'ktp.required' => 'Nomor KTP wajib diisi.',
                'ktp.numeric' => 'Nomor KTP harus berupa angka.',
                'ktp.digits' => 'Nomor KTP harus terdiri dari 16 angka.',
                'ktp.unique' => 'Nomor KTP ini sudah terdaftar.',

                'jurusan.required' => 'Jurusan wajib diisi.',
                'jurusan.string' => 'Jurusan harus berupa teks.',
                'jurusan.max' => 'Jurusan maksimal 255 karakter.',

                'biaya.required' => 'Biaya wajib diisi.',

                'no_hp.required' => 'Nomor HP wajib diisi.',
                'no_hp.regex' => 'Nomor HP harus berupa angka dengan panjang 10-13 digit.',
                'no_wa.required' => 'Nomor WhatsApp wajib diisi.',
                'no_wa.regex' => 'Nomor WhatsApp harus berupa angka dengan panjang 10-13 digit.',

                'email.required' => 'Alamat email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.max' => 'Email maksimal 255 karakter.',

                'agama.string' => 'Agama harus berupa teks.',
                'agama.max' => 'Agama maksimal 50 karakter.',

                'ibu.required' => 'Nama ibu wajib diisi.',
                'ibu.string' => 'Nama ibu harus berupa teks.',
                'ibu.max' => 'Nama ibu maksimal 255 karakter.',

                'ayah.required' => 'Nama ayah wajib diisi.',
                'ayah.string' => 'Nama ayah harus berupa teks.',
                'ayah.max' => 'Nama ayah maksimal 255 karakter.',

                'jaket.string' => 'Ukuran jaket harus berupa teks.',
                'jaket.max' => 'Ukuran jaket maksimal 10 karakter.',

                'lulusan.string' => 'Lulusan harus berupa teks.',
                'lulusan.max' => 'Lulusan maksimal 255 karakter.',

                'info.string' => 'Informasi tambahan harus berupa teks.',
                'info.max' => 'Informasi tambahan maksimal 255 karakter.',

                'kerja.string' => 'Pekerjaan harus berupa teks.',
                'kerja.max' => 'Pekerjaan maksimal 255 karakter.',

                'al_kerja.string' => 'Alamat kerja harus berupa teks.',
                'al_kerja.max' => 'Alamat kerja maksimal 255 karakter.',

                'g-recaptcha-response.required' => 'Captcha wajib diisi.',
            ]
        );

        $now = now()->format('y');
        $lastRecord = Daftar::where('daftar_id', 'LIKE', "$now%")
            ->orderBy('daftar_id', 'desc')
            ->first();
        $getno = $lastRecord ? (int) substr($lastRecord->daftar_id, -4) + 1 : 1;
        $no_daftar = $now . sprintf('%04d', $getno);
        $data = [
            'daftar_id' => $no_daftar,
            'kelas' => $request->kelas,
            'nama_leng' => $request->nama,
            'kampus' => $request->kampus,
            'al_ktp' => $request->alamat_ktp,
            'al_dom' => $request->alamat_dom,
            'j_kel' => $request->jk,
            'tmpt_lahir' => $request->tmpt_lahir,
            'tgl_lahir' => $request->tgl_lahir,
            'sekolah' => $request->sekolah,
            'no_ktp' => $request->ktp,
            'jurusan' => $request->jurusan,
            'no_hp' => $request->no_hp,
            'no_wa' => $request->no_wa,
            'email' => $request->email,
            'agama' => $request->agama,
            'ibu' => $request->ibu,
            'ayah' => $request->ayah,
            'jaket' => $request->jaket,
            'lulusan' => $request->lulusan,
            'biaya' => $request->biaya,
            'info' => $request->info,
            'kerja' => $request->kerja,
            'al_kerja' => $request->al_kerja,
        ];

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => '6Lcd1K0qAAAAAIVkNMPHsSnIp_NPNkaolBz795yu', // Ganti dengan Secret Key Anda
            'response' => $request->input('g-recaptcha-response'),
        ]);

        if (!$response->json('success')) {
            return back()->withErrors(['captcha' => 'Captcha tidak valid.']);
        }

        $dataForEmail = $request->only(['kelas', 'nama', 'jk', 'kampus', 'jurusan', 'no_hp', 'email', 'ibu', 'ayah', 'sekolah']);

        $dataForEmail['no_daftar'] = $no_daftar;
        $dataForEmail['nama_leng'] = strtoupper($request->nama);
        $dataForEmail['tgl_lahir'] = $request->tgl_lahir ?? now()->toDateString();
        $dataForEmail['no_wa'] = $request->no_wa;
        $dataForEmail['tmpt_lahir'] = $request->tmpt_lahir;

        try {
            Mail::to($request->email)->send(new RegistrationMail($dataForEmail));
        } catch (\Exception $e) {
            Log::error('Email gagal dikirim: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withErrors(['email' => 'Gagal mengirim email. Silakan coba lagi.']);
        }
        // @dd($data);
        Daftar::create($data);

        return redirect()
            ->route('registration.success', ['no_daftar' => $no_daftar])
            ->with('success', 'Pendaftaran berhasil.')
            ->withInput();
    }

    public function showSuccessPage($no_daftar)
    {
        // Fetch main menu items (submenu_id=0)
        $mainMenus = Menu::where('submenu_id', 0)->orderBy('urutan', 'asc')->get();

        // Fetch all submenus
        $subMenus = Menu::where('submenu_id', '!=', 0)->get();

        $registration = Daftar::where('daftar_id', $no_daftar)->firstOrFail();
        if (!$registration) {
            dd('Data not found for no_daftar: ' . $no_daftar);
        }

        return view('Frontend.confirm', compact('registration', 'mainMenus', 'subMenus'));
    }

    public function kegiatanKampus()
    {
        $status = [
            'title' => 'Kegiatan Kampus Universitas Kepanjen',
            'current' => 'Kegiatan Kampus',
        ];
        $mainMenus = Menu::where('submenu_id', 0)->orderBy('urutan', 'asc')->get();

        // Fetch all submenus
        $subMenus = Menu::where('submenu_id', '!=', 0)->get();
        $events = DB::table('images')->where('kategori', 'Kegiatan Mahasiswa')->paginate(5);
        // Kirim data ke view
        return view('Frontend.gallery', compact('events', 'mainMenus', 'subMenus', 'status'));
    }

    public function eventKampus()
    {
        $mainMenus = Menu::where('submenu_id', 0)->orderBy('urutan', 'asc')->get();

        // Fetch all submenus
        $subMenus = Menu::where('submenu_id', '!=', 0)->get();
        $events = Images::where('kategori', 'Event Kampus')->paginate(5);
        $status = [
            'title' => 'Event Kampus Universitas Kepanjen',
            'current' => 'Event Kampus',
        ];
        // Kirim data ke view
        return view('Frontend.gallery', compact('events', 'mainMenus', 'subMenus', 'status'));
    }
    public function fasilitasKampus()
    {
        $status = [
            'title' => 'Fasilitas Kampus Universitas Kepanjen',
            'current' => 'Fasilitas Kampus',
        ];
        $mainMenus = Menu::where('submenu_id', 0)->orderBy('urutan', 'asc')->get();

        // Fetch all submenus
        $subMenus = Menu::where('submenu_id', '!=', 0)->get();
        $events = DB::table('images')->where('kategori', 'Fasilitas Kampus')->paginate(5);
        // Kirim data ke view
        return view('Frontend.gallery', compact('events', 'mainMenus', 'subMenus', 'status'));
    }

    public function cetak($no_daftar)
    {
        // Fetch main menu items (submenu_id=0)
        $mainMenus = Menu::where('submenu_id', 0)->orderBy('urutan', 'asc')->get();

        // Fetch all submenus
        $subMenus = Menu::where('submenu_id', '!=', 0)->get();

        $data = Daftar::where('daftar_id', $no_daftar)->firstOrFail();
        if (!$data) {
            dd('Data not found for no_daftar: ' . $no_daftar);
        }

        return view('Frontend.cetak', compact('data', 'mainMenus', 'subMenus'));
    }
}
