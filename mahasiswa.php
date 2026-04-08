<?php
require_once 'User.php';
require_once 'CetakLaporan.php';

class Mahasiswa extends User implements CetakLaporan {
    private $jurusan;
    private $dosen_wali;
    private $nilai_matakuliah = [];

    public function __construct($nim, $nama, $email, $jurusan, $dosen_wali = '-') {
        parent::__construct($nim, $nama, $email);
        $this->jurusan = $jurusan;
        $this->dosen_wali = $dosen_wali;
    }

    public function getRole() { return "Mahasiswa"; }
    public function getId() { return $this->id; }
    public function getNama() { return $this->nama; }
    public function getDosenWali() { return $this->dosen_wali; }

    public function tambahNilai($mata_kuliah, $nilai) {
        $this->nilai_matakuliah[] = [
            'mk' => $mata_kuliah,
            'nilai' => $nilai
        ];
    }

    private function hitungBobot($nilai) {
        if ($nilai >= 85) return 4.0;
        if ($nilai >= 75) return 3.0;
        if ($nilai >= 60) return 2.0;
        if ($nilai >= 50) return 1.0;
        return 0.0;
    }

    private function getHuruf($nilai) {
        if ($nilai >= 85) return 'A';
        if ($nilai >= 75) return 'B';
        if ($nilai >= 60) return 'C';
        if ($nilai >= 50) return 'D';
        return 'E';
    }

    public function hitungIPK() {
        if (empty($this->nilai_matakuliah)) return 0;
        $total_sks = 0;
        $total_bobot = 0;

        foreach ($this->nilai_matakuliah as $data) {
            $sks = $data['mk']->getSks();
            $bobot = $this->hitungBobot($data['nilai']);
            $total_sks += $sks;
            $total_bobot += ($bobot * $sks);
        }

        return $total_sks > 0 ? round($total_bobot / $total_sks, 2) : 0;
    }

    // INI BAGIAN YANG MENAMPILKAN KHS SECARA LENGKAP
    public function cetakLaporan() {
        $html = "<div class='mb-6'>";
        $html .= "<h3 class='text-2xl font-bold text-indigo-600 mb-4'>Kartu Hasil Studi (KHS)</h3>";
        $html .= "<div class='grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-6 bg-slate-50 p-4 rounded-xl border border-slate-200'>";
        $html .= "<div><p><span class='font-bold text-slate-500'>NIM:</span> " . htmlspecialchars($this->id) . "</p>";
        $html .= "<p><span class='font-bold text-slate-500'>Nama:</span> " . htmlspecialchars($this->nama) . "</p></div>";
        $html .= "<div><p><span class='font-bold text-slate-500'>Jurusan:</span> " . htmlspecialchars($this->jurusan) . "</p>";
        $html .= "<p><span class='font-bold text-slate-500'>Dosen Wali:</span> " . htmlspecialchars($this->dosen_wali) . "</p></div>";
        $html .= "</div></div>";

        $html .= "<div class='overflow-x-auto'>";
        $html .= "<table class='w-full text-left border-collapse'>";
        $html .= "<thead><tr class='border-b-2 border-slate-300 text-slate-600'>";
        $html .= "<th class='pb-2 font-bold'>Mata Kuliah</th>";
        $html .= "<th class='pb-2 font-bold'>Dosen Pengampu</th>";
        $html .= "<th class='pb-2 font-bold text-center'>SKS</th>";
        $html .= "<th class='pb-2 font-bold text-center'>Nilai</th></tr></thead>";
        $html .= "<tbody>";

        $total_sks = 0;
        foreach ($this->nilai_matakuliah as $data) {
            $mk = $data['mk'];
            $nilai_angka = $data['nilai'];
            $huruf = $this->getHuruf($nilai_angka);
            $sks = $mk->getSks();
            $total_sks += $sks;
            
            $html .= "<tr class='border-b border-slate-200'>";
            $html .= "<td class='py-3 font-medium text-slate-800'>" . htmlspecialchars($mk->getNamaMK()) . "</td>";
            $html .= "<td class='py-3 text-slate-600 text-sm'>" . htmlspecialchars($mk->getDosenPengampu()) . "</td>";
            $html .= "<td class='py-3 text-center'>" . $sks . "</td>";
            $html .= "<td class='py-3 text-center font-bold text-indigo-600'>" . $huruf . " <span class='text-xs text-slate-400 font-normal'>(" . $nilai_angka . ")</span></td>";
            $html .= "</tr>";
        }

        $html .= "</tbody></table></div>";
        
        $html .= "<div class='mt-6 pt-4 border-t-2 border-slate-300 flex justify-between items-center'>";
        $html .= "<p class='font-bold text-slate-600'>Total SKS: <span class='text-slate-800'>" . $total_sks . "</span></p>";
        $html .= "<p class='text-xl font-black text-indigo-600'>IPK: " . $this->hitungIPK() . "</p>";
        $html .= "</div>";

        return $html;
    }
}