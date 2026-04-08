<?php
require_once 'User.php';
require_once 'CetakLaporan.php';
require_once 'MataKuliah.php';

class Dosen extends User implements CetakLaporan {
    private $nidn;
    private $mataKuliahDiajar = [];

    public function __construct($nidn, $nama, $email) {
        parent::__construct($nidn, $nama, $email);
        $this->nidn = $nidn; // menggunakan property id dari parent
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
        $html .= "<h3 class='text-xl font-bold text-emerald-300'>Daftar Mata Kuliah Diajar</h3>";
        $html .= "</div>";
        $html .= "<div class='grid grid-cols-2 gap-2 text-sm mb-6 bg-black/20 p-4 rounded-xl'>";
        $html .= "<div><span class='text-slate-400'>NIDN:</span> <br><strong class='text-amber-400'>{$this->id}</strong></div>";
        $html .= "<div><span class='text-slate-400'>Nama Dosen:</span> <br><strong>{$this->nama}</strong></div>";
        $html .= "</div>";
        
        $html .= "<div class='overflow-x-auto'>";
        $html .= "<table class='w-full text-left border-collapse text-sm'>";
        $html .= "<thead><tr class='border-b border-white/20 text-slate-400'><th class='p-3 font-medium'>Kode MK</th><th class='p-3 font-medium'>Mata Kuliah</th><th class='p-3 font-medium text-center'>SKS</th></tr></thead>";
        $html .= "<tbody>";
        
        if (empty($this->mataKuliahDiajar)) {
            $html .= "<tr><td colspan='3' class='p-3 text-center text-slate-500'>Belum ada mata kuliah yang diampu.</td></tr>";
        } else {
            foreach ($this->mataKuliahDiajar as $mk) {
                $html .= "<tr class='border-b border-white/5 hover:bg-white/5 transition-colors'>";
                $html .= "<td class='p-3'>{$mk->getKode()}</td>";
                $html .= "<td class='p-3'>{$mk->getNamaMK()}</td>";
                $html .= "<td class='p-3 text-center'>{$mk->getSks()}</td>";
                $html .= "</tr>";
            }
        }
        
        $html .= "</tbody></table>";
        $html .= "</div></div>";

        return $html;
    }
}
?>