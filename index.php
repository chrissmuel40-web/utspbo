<?php
require_once 'Mahasiswa.php';
require_once 'Dosen.php';
require_once 'MataKuliah.php';

session_start();

// =================================================================
// LOGIKA AUTENTIKASI (LOGIN & LOGOUT)
// =================================================================
$valid_username = "polije";
$valid_password = "kampus2bondowoso";

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}

$login_error = '';
$login_success = false; // Variabel baru untuk mengecek jika login baru saja berhasil

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['logged_in'] = true;
        $login_success = true; // Set true untuk menampilkan animasi sukses
    } else {
        $login_error = "Username atau password salah!";
    }
}

// Tampilkan dasbor HANYA JIKA sudah login DAN tidak sedang menampilkan pesan sukses
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && !$login_success;

// =================================================================
// LOGIKA PENYIMPANAN DATA
// =================================================================
if ($is_logged_in) {
    if (!isset($_SESSION['mahasiswa'])) $_SESSION['mahasiswa'] = [];
    if (!isset($_SESSION['matakuliah'])) $_SESSION['matakuliah'] = [];
    
    // Inisialisasi daftar jurusan default
    if (!isset($_SESSION['daftar_jurusan'])) {
        $_SESSION['daftar_jurusan'] = [
            'Bisnis Digital', 
            'Manajemen Agribisnis', 
            'Produksi Media'
        ];
    }

    $tab = $_GET['tab'] ?? 'mahasiswa';

    // 0. Simpan Jurusan Baru
    if (isset($_POST['add_jurusan'])) {
        $jurusan_baru = trim($_POST['nama_jurusan']);
        if (!empty($jurusan_baru) && !in_array($jurusan_baru, $_SESSION['daftar_jurusan'])) {
            $_SESSION['daftar_jurusan'][] = $jurusan_baru;
        }
        header("Location: ?tab=mahasiswa");
        exit;
    }

    // 1. Simpan Mahasiswa
    if (isset($_POST['add_mhs'])) {
        $nim = $_POST['nim'];
        $nama = $_POST['nama'];
        $email = $_POST['email']; 
        $jurusan = $_POST['jurusan'];
        $dosen_wali = $_POST['dosen_wali'] ?? '-'; 
        
        $mhs = new Mahasiswa($nim, $nama, $email, $jurusan, $dosen_wali); 
        $_SESSION['mahasiswa'][$nim] = $mhs; 
        
        header("Location: ?tab=mahasiswa");
        exit;
    }

    // 2. Hapus Mahasiswa
    if (isset($_GET['action']) && $_GET['action'] == 'delete_mhs' && isset($_GET['nim'])) {
        $nim = $_GET['nim'];
        if (isset($_SESSION['mahasiswa'][$nim])) unset($_SESSION['mahasiswa'][$nim]);
        header("Location: ?tab=mahasiswa");
        exit;
    }

    // 3. Simpan Mata Kuliah
    if (isset($_POST['add_mk'])) {
        $kode = $_POST['kode'];
        $nama_mk = $_POST['nama_mk'];
        $sks = $_POST['sks'];
        $dosen_pengampu = $_POST['dosen_pengampu'] ?? '-'; 
        
        $mk = new MataKuliah($kode, $nama_mk, $sks, $dosen_pengampu);
        $_SESSION['matakuliah'][$kode] = $mk;
        
        header("Location: ?tab=matakuliah");
        exit;
    }

    // 4. Hapus Mata Kuliah
    if (isset($_GET['action']) && $_GET['action'] == 'delete_mk' && isset($_GET['kode'])) {
        $kode = $_GET['kode'];
        if (isset($_SESSION['matakuliah'][$kode])) unset($_SESSION['matakuliah'][$kode]);
        header("Location: ?tab=matakuliah");
        exit;
    }

    // 5. Simpan Nilai
    if (isset($_POST['add_nilai'])) {
        $nim = $_POST['nim'];
        $kode_mk = $_POST['kode_mk'];
        $nilai = $_POST['nilai'];
        
        if (isset($_SESSION['mahasiswa'][$nim]) && isset($_SESSION['matakuliah'][$kode_mk])) {
            $matkul = $_SESSION['matakuliah'][$kode_mk];
            $_SESSION['mahasiswa'][$nim]->tambahNilai($matkul, $nilai);
        }
        
        header("Location: ?tab=nilai");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Akademik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary: #4F46E5; --secondary: #1e293b; --accent: #F43F5E; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(to bottom right, rgba(255, 255, 255, 0.4), rgba(166, 193, 238, 0.7)), 
                        url('https://images.unsplash.com/photo-1557682250-33bd709cbe85?q=80&w=2029&auto=format&fit=crop') no-repeat center center fixed;
            background-size: cover; color: var(--secondary); min-height: 100vh;
        }
        .heading-font { font-family: 'Space Grotesk', sans-serif; }
        .card-neo {
            background: rgba(255, 255, 255, 0.65); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.8); box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.15);
            border-radius: 24px; color: var(--secondary);
        }
        .input-elegant {
            background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(0, 0, 0, 0.05); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .input-elegant:focus {
            background: #ffffff; border-color: #a6c1ee; box-shadow: 0 0 0 4px rgba(166, 193, 238, 0.3); outline: none; transform: translateY(-2px);
        }
        .tab-active { background: var(--secondary); color: white !important; box-shadow: 0 4px 15px rgba(15, 23, 42, 0.2); position: relative; }
        .tab-active::after { content: ""; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 20px; height: 4px; background: #fbc2eb; border-radius: 10px 10px 0 0; }
        .nav-glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border-bottom: 1px solid rgba(255, 255, 255, 0.4); }
        .khs-container * { color: #1e293b !important; border-color: #cbd5e1 !important; }
        .khs-container h3 { color: #4F46E5 !important; font-weight: 800 !important; }
        .khs-container .bg-white\/10 { background: transparent !important; box-shadow: none !important; border: none !important; }
        @media print {
            body { background: none !important; background-color: white !important; color: black !important; }
            .no-print { display: none !important; }
            .card-neo { box-shadow: none !important; border: 1px solid #cbd5e1 !important; background: white !important; color: black !important; page-break-inside: avoid; margin-bottom: 20px; }
            .khs-container * { color: black !important; }
        }
    </style>
</head>
<body class="antialiased pb-20">

<?php if (!$is_logged_in): ?>
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="card-neo w-full max-w-md p-10 relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-pink-300 rounded-full mix-blend-multiply filter blur-2xl opacity-40"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-blue-300 rounded-full mix-blend-multiply filter blur-2xl opacity-40"></div>
            
            <div class="relative z-10">
                <div class="flex justify-center mb-6">
                    <div class="p-3 bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-white">
                        <img src="logopolije.png" alt="Logo" class="w-12 h-12" onerror="this.style.display='none'">
                    </div>
                </div>
                <div class="text-center mb-8">
                    <h1 class="heading-font text-2xl md:text-3xl font-bold tracking-tight text-slate-800">Sistem Informasi Akademik<span class="text-indigo-500">.</span></h1>
                    <p class="text-sm font-semibold text-slate-500 mt-2">Masuk ke Dasbor Akademik</p>
                    <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mt-1">Kampus 2 Bondowoso</p>
                </div>

                <?php if ($login_success): ?>
                    <div class="mb-6 p-6 bg-emerald-50 border border-emerald-200 rounded-xl text-center shadow-sm">
                        <svg class="w-12 h-12 mx-auto mb-3 text-emerald-500 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h3 class="text-emerald-700 font-bold text-lg">Login Berhasil!</h3>
                        <p class="text-emerald-600 font-medium text-sm mt-1">Kredensial benar, masuk sekarang...</p>
                    </div>
                    <script>
                        setTimeout(function() { window.location.href = 'index.php'; }, 1500);
                    </script>
                
                <?php else: ?>
                    <?php if ($login_error): ?>
                        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-600 rounded-xl text-sm font-bold text-center shadow-sm">
                            <?= $login_error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Username</label>
                            <div class="relative">
                                <input type="text" id="username_input" name="username" required placeholder="Masukkan username" class="w-full input-elegant p-4 pr-12 rounded-xl font-medium text-slate-700">
                                
                                <button type="button" onclick="toggleVisibility('username_input', 'eye_closed_user', 'eye_open_user')" class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 hover:text-indigo-600 focus:outline-none transition-colors">
                                    <svg id="eye_closed_user" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                    <svg id="eye_open_user" class="w-5 h-5 block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.543 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Password</label>
                            <div class="relative">
                                <input type="password" id="password_input" name="password" required placeholder="Masukkan password" class="w-full input-elegant p-4 pr-12 rounded-xl font-medium text-slate-700">
                                
                                <button type="button" onclick="toggleVisibility('password_input', 'eye_closed_pw', 'eye_open_pw')" class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 hover:text-indigo-600 focus:outline-none transition-colors">
                                    <svg id="eye_closed_pw" class="w-5 h-5 block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                    <svg id="eye_open_pw" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.543 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                            </div>
                        </div>
                        <button type="submit" name="login" class="w-full py-4 mt-4 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-xl font-bold text-lg shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-0.5 transition-all">
                            Masuk Sekarang
                        </button>
                    </form>

                    <div class="mt-8 text-center bg-white/50 backdrop-blur-sm p-4 rounded-xl border border-white">
                        <p class="text-xs text-slate-500 font-medium">Username: <span class="text-indigo-600 font-bold">polije</span> | Password: <span class="text-indigo-600 font-bold">kampus2bondowoso</span></p>
                    </div>

                    <script>
                        function toggleVisibility(inputId, closedId, openId) {
                            const inputField = document.getElementById(inputId);
                            const eyeClosed = document.getElementById(closedId);
                            const eyeOpen = document.getElementById(openId);
                            
                            if (inputField.type === 'password') {
                                inputField.type = 'text';
                                eyeClosed.classList.replace('block', 'hidden');
                                eyeOpen.classList.replace('hidden', 'block');
                            } else {
                                inputField.type = 'password';
                                eyeClosed.classList.replace('hidden', 'block');
                                eyeOpen.classList.replace('block', 'hidden');
                            }
                        }
                    </script>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php else: ?>
    <nav class="nav-glass sticky top-0 z-50 mb-8 shadow-sm no-print">
        <div class="max-w-7xl mx-auto px-6 py-4 flex flex-col xl:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-white/80 rounded-2xl shadow-sm border border-white">
                    <img src="logopolije.png" alt="Logo" class="w-10 h-10" onerror="this.style.display='none'">
                </div>
                <div>
                    <h1 class="heading-font text-lg md:text-xl font-bold tracking-tight text-slate-800">Sistem Informasi Akademik<span class="text-indigo-500">.</span></h1>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">Politeknik Negeri Jember</p>
                    <p class="text-[10px] font-extrabold text-indigo-500 uppercase tracking-widest mt-0.5">Kampus 2 Bondowoso</p>
                </div>
            </div>

            <div class="flex flex-wrap justify-center items-center gap-3">
                <div class="flex gap-2 p-1.5 bg-white/40 rounded-2xl backdrop-blur-md border border-white/60">
                    <a href="?tab=mahasiswa" class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all <?= $tab == 'mahasiswa' ? 'tab-active' : 'text-slate-600 hover:bg-white/80' ?>">Mahasiswa</a>
                    <a href="?tab=matakuliah" class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all <?= $tab == 'matakuliah' ? 'tab-active' : 'text-slate-600 hover:bg-white/80' ?>">Mata Kuliah</a>
                    <a href="?tab=nilai" class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all <?= $tab == 'nilai' ? 'tab-active' : 'text-slate-600 hover:bg-white/80' ?>">Input Nilai</a>
                    <a href="?tab=laporan" class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all <?= $tab == 'laporan' ? 'tab-active' : 'text-slate-600 hover:bg-white/80' ?>">Cetak KHS</a>
                </div>
                <a href="?action=logout" class="px-5 py-3 ml-2 bg-rose-500/10 text-rose-600 hover:bg-rose-500 hover:text-white border border-rose-500/20 rounded-xl text-sm font-bold transition-all shadow-sm">Logout</a>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-6 mt-4">
        
        <?php if ($tab === 'mahasiswa'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div>
                    <div class="card-neo p-6 mb-6">
                        <h2 class="heading-font text-md font-bold mb-3 text-slate-800 border-b border-slate-200 pb-2">Tambah Jurusan Baru</h2>
                        <form method="POST" class="flex gap-2">
                            <input type="text" name="nama_jurusan" placeholder="Contoh: Teknik Informatika" required class="w-full input-elegant p-3 rounded-xl text-sm font-medium">
                            <button type="submit" name="add_jurusan" class="px-4 py-3 bg-slate-800 text-white rounded-xl font-bold text-xs hover:bg-slate-700 transition-all shadow-sm">
                                Tambah
                            </button>
                        </form>
                    </div>

                    <div class="card-neo p-8 h-fit lg:sticky lg:top-32">
                        <h2 class="heading-font text-xl font-bold mb-6 text-slate-800">Manajemen Mahasiswa</h2>
                        <form method="POST" class="space-y-4">
                            <input type="text" name="nim" placeholder="NIM Mahasiswa" required class="w-full input-elegant p-4 rounded-xl text-sm font-medium">
                            <input type="text" name="nama" placeholder="Nama Lengkap" required class="w-full input-elegant p-4 rounded-xl text-sm font-medium">
                            <input type="email" name="email" placeholder="Alamat Email" required class="w-full input-elegant p-4 rounded-xl text-sm font-medium">
                            
                            <div class="relative">
                                <select name="jurusan" required class="w-full input-elegant p-4 rounded-xl text-sm font-bold text-indigo-600 appearance-none cursor-pointer">
                                    <option value="" disabled selected>-- Pilih Jurusan --</option>
                                    <?php foreach($_SESSION['daftar_jurusan'] as $jrs): ?>
                                        <option value="<?= htmlspecialchars($jrs) ?>"><?= htmlspecialchars($jrs) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>

                            <div class="relative">
                                <select name="dosen_wali" required class="w-full input-elegant p-4 rounded-xl text-sm font-bold text-indigo-600 appearance-none cursor-pointer">
                                    <option value="" disabled selected>-- Pilih Dosen Wali --</option>
                                    <option value="Eka Yuniar, S.Kom., MMSI">Eka Yuniar, S.Kom., MMSI</option>
                                    <option value="Rizky Adhitya N., S.A.B., M.M.">Rizky Adhitya N., S.A.B., M.M.</option>
                                    <option value="Lukman Hakim, S.Kom., M.Kom.">Lukman Hakim, S.Kom., M.Kom.</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>

                            <button type="submit" name="add_mhs" class="w-full py-4 mt-2 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-xl font-bold hover:shadow-indigo-500/30 transition-all shadow-md">
                                Simpan Data
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="heading-font text-xl font-bold text-slate-800 drop-shadow-sm">Direktori Mahasiswa</h3>
                        <span class="px-4 py-1.5 bg-white/80 backdrop-blur-md rounded-full text-xs font-bold text-indigo-600 border border-white shadow-sm"><?= count($_SESSION['mahasiswa']) ?> Terdaftar</span>
                    </div>
                    
                    <div class="space-y-4">
                        <?php foreach ($_SESSION['mahasiswa'] as $nim => $mhs): ?>
                        <div class="card-neo p-5 flex items-center justify-between group hover:bg-white/80 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center font-bold text-indigo-500 shadow-inner border border-indigo-100">
                                    <?= substr($mhs->getNama(), 0, 1) ?>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-lg"><?= $mhs->getNama() ?></h4>
                                    <div class="flex gap-2 items-center mt-1">
                                        <p class="text-xs text-slate-500 font-mono bg-white/60 px-2 py-0.5 rounded-md"><?= $mhs->getId() ?? $nim ?></p>
                                        <?php if (method_exists($mhs, 'getDosenWali')): ?>
                                            <p class="text-xs text-indigo-600 font-bold bg-indigo-100/50 px-2 py-0.5 rounded-md truncate max-w-[200px]">Wali: <?= htmlspecialchars($mhs->getDosenWali()) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <a href="?action=delete_mhs&nim=<?= $nim ?>" class="p-2 text-rose-400 hover:text-white hover:bg-rose-500 rounded-lg transition-colors font-bold text-xs">HAPUS</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($_SESSION['mahasiswa'])) echo "<div class='text-center py-12 text-slate-600 font-medium bg-white/40 rounded-3xl border border-white/60 backdrop-blur-md shadow-sm'>Belum ada data mahasiswa...</div>"; ?>
                    </div>
                </div>
            </div>

        <?php elseif ($tab === 'matakuliah'): ?>
            <div class="max-w-4xl mx-auto">
                <div class="card-neo p-8 mb-10 border-t-4 border-t-indigo-400">
                    <h2 class="heading-font text-2xl font-bold mb-6 text-slate-800">Kelola Mata Kuliah</h2>
                    <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <input type="text" name="kode" placeholder="Kode" required class="input-elegant p-4 rounded-xl text-sm font-medium">
                        <input type="text" name="nama_mk" placeholder="Nama Mata Kuliah" required class="md:col-span-2 input-elegant p-4 rounded-xl text-sm font-medium">
                        <input type="number" name="sks" placeholder="SKS" required class="input-elegant p-4 rounded-xl text-sm font-medium text-center">
                        
                        <div class="relative md:col-span-4">
                            <select name="dosen_pengampu" required class="w-full input-elegant p-4 rounded-xl text-sm font-bold text-indigo-600 appearance-none cursor-pointer">
                                <option value="" disabled selected>-- Pilih Dosen Pengampu --</option>
                                <option value="Eka Yuniar, S.Kom., MMSI">Eka Yuniar, S.Kom., MMSI</option>
                                <option value="Rizky Adhitya N., S.A.B., M.M.">Rizky Adhitya N., S.A.B., M.M.</option>
                                <option value="Lukman Hakim, S.Kom., M.Kom.">Lukman Hakim, S.Kom., M.Kom.</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>

                        <button type="submit" name="add_mk" class="md:col-span-4 py-4 bg-slate-800 text-white rounded-xl font-bold hover:bg-slate-700 hover:shadow-lg transition-all shadow-sm">
                            Simpan Mata Kuliah
                        </button>
                    </form>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($_SESSION['matakuliah'] as $kode => $mk): ?>
                        <div class="card-neo p-6 relative overflow-hidden transition-all group hover:bg-white/80">
                            <div class="absolute top-0 left-0 w-1.5 h-full bg-gradient-to-b from-blue-400 to-indigo-400"></div>
                            <div class="flex justify-between items-start mb-4">
                                <span class="text-xs font-bold px-3 py-1 bg-white/60 text-indigo-600 rounded-lg shadow-sm border border-indigo-50"><?= htmlspecialchars($mk->getKode()) ?></span>
                                <span class="text-xs font-extrabold text-slate-500 bg-white/60 px-3 py-1 rounded-lg border border-slate-100"><?= htmlspecialchars($mk->getSks()) ?> SKS</span>
                            </div>
                            <h4 class="font-bold text-slate-800 mb-1 text-lg leading-tight"><?= htmlspecialchars($mk->getNamaMK()) ?></h4>
                            <?php if (method_exists($mk, 'getDosenPengampu')): ?>
                                <p class="text-xs text-slate-500 font-medium mb-4 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    <span class="truncate"><?= htmlspecialchars($mk->getDosenPengampu()) ?></span>
                                </p>
                            <?php endif; ?>
                            <div class="pt-4 border-t border-slate-200/60">
                                <a href="?action=delete_mk&kode=<?= $kode ?>" class="text-xs font-bold text-rose-500 hover:text-rose-700 transition-colors">Hapus Matkul</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php elseif ($tab === 'nilai'): ?>
            <div class="max-w-xl mx-auto">
                <div class="card-neo p-10 relative overflow-hidden">
                    <div class="text-center mb-10 relative z-10">
                        <h2 class="heading-font text-2xl font-bold text-slate-800">Input Skor Akademik</h2>
                        <p class="text-slate-500 text-sm mt-1">Pastikan data mahasiswa dan mata kuliah sudah lengkap</p>
                    </div>
                    <?php if (empty($_SESSION['mahasiswa']) || empty($_SESSION['matakuliah'])): ?>
                         <div class="p-6 bg-rose-50 border border-rose-200 rounded-2xl text-center shadow-sm"><p class="text-rose-600 font-bold text-sm">Harap isi data Mahasiswa dan Mata Kuliah terlebih dahulu.</p></div>
                    <?php else: ?>
                        <form method="POST" class="space-y-6 relative z-10">
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-500 uppercase ml-2 tracking-wider">Subjek Mahasiswa</label>
                                <select name="nim" class="w-full input-elegant p-4 rounded-2xl font-semibold text-slate-700 cursor-pointer">
                                    <?php foreach ($_SESSION['mahasiswa'] as $nim => $mhs): ?>
                                        <option value="<?= htmlspecialchars($nim) ?>"><?= htmlspecialchars($mhs->getNama()) ?> (<?= htmlspecialchars($nim) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-500 uppercase ml-2 tracking-wider">Mata Kuliah</label>
                                <select name="kode_mk" class="w-full input-elegant p-4 rounded-2xl font-semibold text-slate-700 cursor-pointer">
                                    <?php foreach ($_SESSION['matakuliah'] as $kode => $mk): ?>
                                        <option value="<?= htmlspecialchars($kode) ?>"><?= htmlspecialchars($mk->getNamaMK()) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-500 uppercase ml-2 tracking-wider">Nilai Final (0-100)</label>
                                <input type="number" name="nilai" required min="0" max="100" class="w-full input-elegant p-6 rounded-2xl text-4xl font-extrabold text-center text-indigo-500 placeholder-slate-300" placeholder="0">
                            </div>
                            <button type="submit" name="add_nilai" class="w-full py-5 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-2xl font-bold text-lg shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-1 transition-all">
                                Verifikasi & Simpan Nilai
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($tab === 'laporan'): ?>
            <div class="max-w-6xl mx-auto">
                <div class="card-neo p-6 md:px-8 mb-8 flex flex-col md:flex-row items-center justify-between gap-4 border-t-4 border-t-indigo-500 no-print">
                    <div>
                        <h2 class="heading-font text-2xl font-bold text-slate-800">Cetak Kartu Hasil Studi (KHS)</h2>
                        <p class="text-slate-500 text-sm mt-1">Lihat dan cetak transkrip nilai mahasiswa</p>
                    </div>
                    <?php if (!empty($_SESSION['mahasiswa'])): ?>
                    <button onclick="window.print()" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-md shadow-indigo-500/30 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Print Halaman Ini
                    </button>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 khs-container items-start">
                    <?php 
                    if (empty($_SESSION['mahasiswa'])) {
                        echo "<div class='col-span-full text-center text-slate-600 font-medium bg-white/60 border border-white/60 backdrop-blur-md rounded-3xl py-20 shadow-sm'>Belum ada data KHS yang dapat dicetak.</div>";
                    } else {
                        foreach ($_SESSION['mahasiswa'] as $mhs) {
                            echo "<div class='card-neo p-8 overflow-hidden bg-white/80'>";
                            echo $mhs->cetakLaporan(); 
                            echo "</div>";
                        }
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <footer class="mt-20 text-center pb-8 no-print">
        <p class="text-slate-600 text-xs font-semibold tracking-widest uppercase bg-white/60 inline-block px-6 py-3 rounded-full backdrop-blur-md shadow-sm border border-white/50">© 2026 Lab Akademik • Sistem Informasi Akademik</p>
    </footer>
<?php endif; ?>

</body>
</html>