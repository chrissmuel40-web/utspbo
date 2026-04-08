<?php
class MataKuliah {
    private $kode;
    private $nama_mk;
    private $sks;
    private $dosen_pengampu;

    public function __construct($kode, $nama_mk, $sks, $dosen_pengampu = '-') {
        $this->kode = $kode;
        $this->nama_mk = $nama_mk;
        $this->sks = $sks;
        $this->dosen_pengampu = $dosen_pengampu;
    }

    public function getKode() { return $this->kode; }
    public function getNamaMK() { return $this->nama_mk; }
    public function getSks() { return $this->sks; }
    public function getDosenPengampu() { return $this->dosen_pengampu; }
}