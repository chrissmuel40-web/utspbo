<?php
// ==========================================
// 1. INTERFACE
// ==========================================
interface CetakLaporan {
    public function cetakLaporan();
}

// ==========================================
// 2. ABSTRACT CLASS & ENCAPSULATION
// ==========================================
abstract class User {
    // Encapsulation: menggunakan protected agar bisa diakses oleh class turunan
    protected $id;
    protected $nama;
    protected $email;

    public function __construct($id, $nama, $email) {
        $this->id = $id;
        $this->nama = $nama;
        $this->email = $email;
    }

    // Getter untuk encapsulation
    public function getNama() { return $this->nama; }
    public function getId() { return $this->id; }

    // Abstract method
    abstract public function getRole();
}

// ==========================================
// KELAS PENDUKUNG: MATA KULIAH
// ==========================================
class MataKuliah {
    private $kode;
    private $namaMK;
    private $sks;

    public function __construct($kode, $namaMK, $sks) {
        $this->kode = $kode;
        $this->namaMK = $namaMK;
        $this->sks = $sks;
    }

    public function getKode() { return $this->kode; }
    public function getNamaMK() { return $this->namaMK; }
    public function getSks() { return $this->sks; }
}

// ==========================================
// 3. CLASS TURUNAN (INHERITANCE) & POLYMORPHISM
// ==========================================

// --- Class Mahasiswa ---
class Mahasiswa extends User implements CetakLaporan {
    // Encapsulation: property private
    private $jurusan;
    private $daftarNilai = []; // Array untuk menyimpan nilai (MataKuliah => Nilai Huruf)

    public function __construct($nim, $nama, $email, $jurusan) {
        parent::__construct($nim, $nama, $email); // Memanggil constructor parent
        $this->jurusan = $jurusan;
    }

    public function getRole() {
        return "Mahasiswa";
    }

    // Fitur: Input Nilai
    public function tambahNilai(MataKuliah $mk, $nilaiAngka) {
        $this->daftarNilai[] = [
            'mk' => $mk,
            'nilai' => $nilaiAngka
        ];
    }

    // Fitur: Hitung IPK
    public function hitungIPK() {
        if (empty($this->daftarNilai)) return 0;

        $totalBobot = 0;
        $totalSKS = 0;

        foreach ($this->daftarNilai as $data) {
            $sks = $data['mk']->getSks();
            $nilai = $data['nilai'];
            
            // Konversi nilai angka ke bobot
            $bobot = 0;
            if ($nilai >= 85) $bobot = 4.0; // A
            elseif ($nilai >= 70) $bobot = 3.0; // B
            elseif ($nilai >= 55) $bobot = 2.0; // C
            elseif ($nilai >= 40) $bobot = 1.0; // D
            else $bobot = 0.0; // E

            $totalBobot += ($bobot * $sks);
            $totalSKS += $sks;
        }

        return $totalSKS > 0 ? round($totalBobot / $totalSKS, 2) : 0;
    }

    // Polymorphism: Implementasi dari CetakLaporan (Cetak KHS)
    public function cetakLaporan() {
        $html = "<div class='bg-white/10 backdrop-blur-lg border border-white/20 p-6 rounded-2xl shadow-xl text-white'>";
        $html .= "<h3 class='text-2xl font-bold mb-4 text-purple-300'>Kartu Hasil Studi (KHS)</h3>";
        $html .= "<p><strong>NIM:</strong> {$this->id}</p>";
        $html .= "<p><strong>Nama:</strong> {$this->nama}</p>";
        $html .= "<p class='mb-4'><strong>Jurusan:</strong> {$this->jurusan}</p>";
        
        $html .= "<table class='w-full text-left border-collapse'>";
        $html .= "<thead><tr class='border-b border-white/20'><th class='p-2'>Mata Kuliah</th><th class='p-2'>SKS</th><th class='p-2'>Nilai</th></tr></thead>";
        $html .= "<tbody>";
        foreach ($this->daftarNilai as $data) {
            $mk = $data['mk'];
            $nilai = $data['nilai'];
            $huruf = $nilai >= 85 ? 'A' : ($nilai >= 70 ? 'B' : ($nilai >= 55 ? 'C' : ($nilai >= 40 ? 'D' : 'E')));
            $html .= "<tr class='border-b border-white/10'><td class='p-2'>{$mk->getNamaMK()}</td><td class='p-2'>{$mk->getSks()}</td><td class='p-2'>{$huruf} ({$nilai})</td></tr>";
        }
        $html .= "</tbody></table>";
        $html .= "<div class='mt-4 text-xl font-bold text-green-300'>IPK: " . $this->hitungIPK() . "</div>";
        $html .= "</div>";

        return $html;
    }
}

// --- Class Dosen ---
class Dosen extends User implements CetakLaporan {
    private $nidn;
    private $mataKuliahDiajar = [];

    public function __construct($nidn, $nama, $email) {
        parent::__construct($nidn, $nama, $email);
        $this->nidn = $nidn;
    }

    public function getRole() {
        return "Dosen";
    }

    public function tambahMataKuliah(MataKuliah $mk) {
        $this->mataKuliahDiajar[] = $mk;
    }

    // Polymorphism: Implementasi dari CetakLaporan yang BERBEDA perilakunya dari Mahasiswa
    public function cetakLaporan() {
        $html = "<div class='glass-card p-6 rounded-3xl text-white'>";
        $html .= "<div class='border-b border-white/10 pb-4 mb-4'>";
        $html .= "<h3 class='text-xl font-bold text-emerald-300'>Kartu Hasil Studi</h3>";
        $html .= "</div>";
        $html .= "<div class='grid grid-cols-2 gap-2 text-sm mb-6 bg-black/20 p-4 rounded-xl'>";
        $html .= "<div><span class='text-slate-400'>NIM:</span> <br><strong class='text-amber-400'>{$this->id}</strong></div>";
        $html .= "<div><span class='text-slate-400'>Nama:</span> <br><strong>{$this->nama}</strong></div>";
        $html .= "<div class='col-span-2 mt-2'><span class='text-slate-400'>Program Studi:</span> <br><strong>{$this->jurusan}</strong></div>";
        $html .= "</div>";
        
        $html .= "<div class='overflow-x-auto'>";
        $html .= "<table class='w-full text-left border-collapse text-sm'>";
        $html .= "<thead><tr class='border-b border-white/20 text-slate-400'><th class='p-3 font-medium'>Mata Kuliah</th><th class='p-3 font-medium text-center'>SKS</th><th class='p-3 font-medium text-center'>Nilai</th></tr></thead>";
        $html .= "<tbody>";
        foreach ($this->daftarNilai as $data) {
            $mk = $data['mk'];
            $nilai = $data['nilai'];
            $huruf = $nilai >= 85 ? 'A' : ($nilai >= 70 ? 'B' : ($nilai >= 55 ? 'C' : ($nilai >= 40 ? 'D' : 'E')));
            $warnaHuruf = $huruf == 'A' ? 'text-emerald-400' : ($huruf == 'E' ? 'text-red-400' : 'text-blue-400');
            $html .= "<tr class='border-b border-white/5 hover:bg-white/5 transition-colors'><td class='p-3'>{$mk->getNamaMK()}</td><td class='p-3 text-center'>{$mk->getSks()}</td><td class='p-3 text-center'><strong class='{$warnaHuruf} text-lg'>{$huruf}</strong> <span class='text-xs text-slate-500'>({$nilai})</span></td></tr>";
        }
        $html .= "</tbody></table>";
        $html .= "</div>";
        $html .= "<div class='mt-6 flex items-center justify-between bg-emerald-500/10 border border-emerald-500/20 p-4 rounded-xl'>";
        $html .= "<span class='text-slate-300'>Indeks Prestasi Kumulatif</span>";
        $html .= "<span class='text-2xl font-bold text-emerald-400'>" . number_format($this->hitungIPK(), 2) . "</span>";
        $html .= "</div></div>";

        return $html;
    }
}
?>