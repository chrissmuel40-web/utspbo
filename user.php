<?php

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
?>