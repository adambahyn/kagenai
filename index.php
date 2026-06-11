<?php
$result = null;
$error = null;

$is_post = $_SERVER['REQUEST_METHOD'] === 'POST';

$nama = $is_post ? trim($_POST['nama']) : '';
$jurusan = $is_post ? trim($_POST['jurusan']) : '';
$prodi = $is_post ? trim($_POST['prodi']) : '';
$semester = $is_post ? filter_input(INPUT_POST, 'semester', FILTER_VALIDATE_INT) : null;
$ipk_lama = $is_post ? filter_input(INPUT_POST, 'ipk_lama', FILTER_VALIDATE_FLOAT) : null;
$ipk_target = $is_post ? filter_input(INPUT_POST, 'ipk_target', FILTER_VALIDATE_FLOAT) : null;
$kesulitan = $is_post ? trim($_POST['kesulitan']) : '';

// Jalankan AI Engine hanya jika POST dan semua data wajib valid
if ($is_post && $jurusan !== '' && $prodi !== '' && $semester !== null && $semester !== false && $ipk_lama !== null && $ipk_lama !== false && $ipk_target !== null && $ipk_target !== false && $kesulitan !== '') {
    $python_executable = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'python' : 'python3';
    
    $args = [
        "--jurusan " . escapeshellarg($jurusan),
        "--prodi " . escapeshellarg($prodi),
        "--semester " . escapeshellarg($semester),
        "--ipk-lama " . escapeshellarg($ipk_lama),
        "--ipk-target " . escapeshellarg($ipk_target),
        "--kesulitan " . escapeshellarg($kesulitan)
    ];
    
    if ($nama !== '') {
        $args[] = "--nama " . escapeshellarg($nama);
    }
    
    $command = "$python_executable ai_engine.py " . implode(" ", $args);
    $output = shell_exec($command);
    
    if ($output) {
        $data = json_decode($output, true);
        if ($data && isset($data['success']) && $data['success'] === true) {
            $result = $data;
        } else {
            $error = isset($data['error']) ? $data['error'] : "Terjadi kesalahan saat memproses data di AI Engine.";
        }
    } else {
        $error = "Gagal memanggil AI Engine.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator Cumlaude - Politeknik Edition</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Tema Dark (Default) */
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-hover: #4f46e5;
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.4);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --input-bg: rgba(15, 23, 42, 0.5);
            --input-border: rgba(255, 255, 255, 0.1);
            --glass-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            --blob1: hsla(253, 60%, 40%, 0.4);
            --blob2: hsla(339, 60%, 40%, 0.4);
            --success: #34d399;
            --danger: #f87171;
            --formula-bg: rgba(15, 23, 42, 0.5);
            --theme-toggle-bg: rgba(255, 255, 255, 0.1);
        }

        body.light-mode {
            /* Tema Light */
            --bg-color: #f1f5f9;
            --card-bg: rgba(255, 255, 255, 0.5);
            --card-border: rgba(255, 255, 255, 0.5);
            --text-main: #0f172a;
            --text-muted: #475569;
            --input-bg: rgba(255, 255, 255, 0.6);
            --input-border: rgba(0, 0, 0, 0.1);
            --glass-shadow: 0 10px 40px -10px rgba(31, 38, 135, 0.15);
            --blob1: hsla(253, 80%, 75%, 0.6);
            --blob2: hsla(339, 80%, 75%, 0.6);
            --success: #059669;
            --danger: #dc2626;
            --formula-bg: rgba(255, 255, 255, 0.5);
            --theme-toggle-bg: rgba(0, 0, 0, 0.1);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            overflow: hidden; /* Non-scrollable web */
            height: 100vh;
            width: 100vw;
            display: flex;
            transition: background-color 0.5s ease;
            position: relative;
        }
        
        /* Animasi Blobs untuk Liquid Glass */
        .blob {
            position: absolute;
            filter: blur(90px);
            z-index: -1;
            border-radius: 50%;
            animation: moveBlobs 15s infinite alternate ease-in-out;
        }
        .blob-1 {
            top: -10%;
            left: -10%;
            width: 60vw;
            height: 60vw;
            background: var(--blob1);
        }
        .blob-2 {
            bottom: -10%;
            right: -10%;
            width: 50vw;
            height: 50vw;
            background: var(--blob2);
            animation-delay: -5s;
        }

        @keyframes moveBlobs {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(5%, 10%) scale(1.1); }
            100% { transform: translate(10%, -5%) scale(0.9); }
        }
        
        /* Main Layout */
        .app-container {
            display: flex;
            width: 100%;
            height: 100%;
            padding: 24px;
            gap: 24px;
            z-index: 1;
        }
        
        .panel {
            background: var(--card-bg);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid var(--card-border);
            border-radius: 32px;
            box-shadow: var(--glass-shadow);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        /* Left Panel: Form */
        .form-panel {
            flex: 0 0 380px; /* Mampatkan lebar form */
            padding: 20px 24px;
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Matikan scroll vertikal dan horizontal total */
        }
        
        /* Right Panel: Results */
        .result-panel {
            flex: 1; /* Sisa ruang untuk output AI */
            padding: 20px 24px; /* Perkecil padding agar teks naik ke atas */
            display: flex;
            flex-direction: column;
            gap: 16px;
            overflow: hidden;
        }

        /* Scrollable area inside panels jika diperlukan */
        .scrollable-content {
            overflow-y: auto;
            flex-grow: 1;
            padding-right: 8px; /* space for scrollbar */
        }

        .scrollable-content::-webkit-scrollbar { width: 6px; }
        .scrollable-content::-webkit-scrollbar-track { background: transparent; }
        .scrollable-content::-webkit-scrollbar-thumb { background: rgba(150, 150, 150, 0.3); border-radius: 10px; }
        .scrollable-content::-webkit-scrollbar-thumb:hover { background: rgba(150, 150, 150, 0.5); }

        /* Typography & Header */
        h1 {
            font-size: 24px; /* Perkecil ukuran judul */
            font-weight: 800;
            background: linear-gradient(135deg, #a5b4fc, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 4px;
        }
        
        p.subtitle {
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.4;
            margin-bottom: 16px;
        }

        /* Forms */
        .form-grid {
            display: flex;
            flex-direction: column;
            gap: 10px; /* Lebih mampat */
        }
        
        .form-group-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px; /* Lebih mampat */
        }

        label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px; /* Perkecil ukuran label */
            font-weight: 600;
            margin-bottom: 4px;
            color: var(--text-main);
            letter-spacing: 0.3px;
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px 12px; /* Lebih mampat */
            border: 1px solid var(--input-border);
            border-radius: 10px;
            font-size: 13px;
            color: var(--text-main);
            background-color: var(--input-bg);
            transition: all 0.25s ease;
            outline: none;
            backdrop-filter: blur(10px);
        }
        
        input:focus,
        textarea:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.2);
        }
        
        textarea {
            resize: none;
            height: 60px; /* Perkecil tinggi textarea */
        }

        button.btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        }

        
        button.btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
            background: linear-gradient(135deg, var(--primary-hover), var(--primary));
        }

        /* Tooltip */
        .tooltip {
            position: relative;
            cursor: pointer;
            color: var(--text-muted);
            font-size: 12px;
        }
        .tooltip .tooltiptext {
            visibility: hidden;
            width: 220px;
            background-color: var(--bg-color);
            color: var(--text-main);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            padding: 10px;
            position: absolute;
            z-index: 99;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 11px;
            font-weight: 500;
            line-height: 1.4;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3);
        }
        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }

        /* Theme Toggle */
        .theme-toggle {
            position: absolute;
            top: 30px;
            right: 30px;
            background: var(--theme-toggle-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
            border-radius: 50px;
            width: 50px;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            z-index: 10;
            color: var(--text-main);
            transition: all 0.3s ease;
        }
        .theme-toggle:hover {
            background: rgba(129, 140, 248, 0.2);
            transform: rotate(15deg);
        }

        /* Right Panel Cards */
        .top-cards {
            display: flex;
            gap: 16px;
        }

        .result-card {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05));
            border: 1px solid rgba(52, 211, 153, 0.2);
            padding: 14px 18px; /* Perkecil padding */
            border-radius: 16px; /* Perkecil radius */
            flex: 0 0 150px; /* Perkecil lebar tetap card target IPS */
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }

        .formula-card {
            background: var(--formula-bg);
            border: 1px solid var(--card-border);
            padding: 14px 18px; /* Perkecil padding */
            border-radius: 16px; /* Perkecil radius */
            flex: 1; /* Biarkan melebar ke kiri mengisi sisa ruang */
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }

        .result-title {
            font-size: 11px; /* Perkecil ukuran */
            font-weight: 700;
            color: var(--success);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .formula-title {
            color: var(--primary-light);
        }

        .result-value {
            font-size: 32px; /* Perkecil ukuran dari 56px */
            font-weight: 800;
            color: var(--text-main);
            line-height: 1;
            text-shadow: 0 0 20px rgba(52, 211, 153, 0.3);
        }

        .formula-expression {
            font-family: monospace;
            color: var(--primary-light);
            font-size: 11px; /* Perkecil */
            background: rgba(0, 0, 0, 0.2);
            padding: 4px 8px; /* Perkecil */
            border-radius: 6px;
            margin: 6px 0;
            display: inline-block;
        }

        .formula-simulation {
            font-size: 11px; /* Perkecil */
            color: var(--text-muted);
            line-height: 1.4;
        }

        /* Strategy Content Area */
        .ai-header {
            font-size: 15px; /* Perkecil */
            font-weight: 700;
            color: var(--primary-light);
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--card-border);
            margin-bottom: 12px;
        }


        .strategy-content {
            font-size: 15px;
            line-height: 1.7;
            color: var(--text-main);
        }

        .strategy-content p { margin-bottom: 14px; }
        .strategy-content b, .strategy-content strong { color: var(--primary-light); font-weight: 700; }
        .strategy-content ul { margin-left: 20px; margin-bottom: 14px; }
        .strategy-content li { margin-bottom: 8px; }

        .error-box {
            margin-top: 10px;
            padding: 12px;
            border-radius: 10px;
            background-color: rgba(248, 113, 113, 0.1);
            border: 1px solid rgba(248, 113, 113, 0.2);
            color: var(--danger);
            font-size: 12px;
            font-weight: 500;
        }
        
        .loading-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 100;
            border-radius: 24px;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }

    </style>
</head>
<body>
    <!-- Liquid Glass Blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <!-- Theme Toggle -->
    <div class="theme-toggle" id="themeToggle" title="Toggle Light/Dark Mode">
        <svg id="moonIcon" style="width:24px;height:24px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
        <svg id="sunIcon" style="width:24px;height:24px;display:none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
    </div>

    <div class="app-container">
        <!-- Panel Kiri: Form -->
        <div class="panel form-panel">
            <h1>Kalkulator Cumlaude</h1>
            <p class="subtitle">Sistem evaluasi akademik cerdas bertenaga AI untuk Politeknik.</p>
            
            <form method="POST" action="" id="mainForm">
                <div class="form-grid">
                    <div>
                        <label>Nama <span class="tooltip">ℹ️<span class="tooltiptext">Untuk personalisasi AI.</span></span></label>
                        <input type="text" name="nama" value="<?php echo htmlspecialchars($nama); ?>">
                    </div>
                    
                    <div class="form-group-row">
                        <div>
                            <label>Jurusan <span class="tooltip">ℹ️<span class="tooltiptext">Jurusan Anda.</span></span></label>
                            <input type="text" name="jurusan" required value="<?php echo htmlspecialchars($jurusan); ?>">
                        </div>
                        <div>
                            <label>Prodi <span class="tooltip">ℹ️<span class="tooltiptext">Program Studi Anda.</span></span></label>
                            <input type="text" name="prodi" required value="<?php echo htmlspecialchars($prodi); ?>">
                        </div>
                    </div>

                    <div class="form-group-row">
                        <div>
                            <label>Semester <span class="tooltip">ℹ️<span class="tooltiptext">Semester saat ini yang dijalani.</span></span></label>
                            <input type="number" id="semester" name="semester" min="1" max="10" required value="<?php echo htmlspecialchars($semester); ?>">
                        </div>
                        <div>
                            <label>IPK Saat Ini <span class="tooltip">ℹ️<span class="tooltiptext">Rata-rata semester 1 sampai N-1.</span></span></label>
                            <input type="number" id="ipk_lama" name="ipk_lama" step="0.01" min="0" max="4" required value="<?php echo htmlspecialchars($ipk_lama); ?>">
                        </div>
                    </div>
                    
                    <div>
                        <label>Target IPK Baru <span class="tooltip">ℹ️<span class="tooltiptext">IPK kumulatif yang ingin dicapai.</span></span></label>
                        <input type="number" id="ipk_target" name="ipk_target" step="0.01" min="0" max="4" required value="<?php echo htmlspecialchars($ipk_target); ?>">
                    </div>
                    
                    <div>
                        <label>Kesulitan Semester Ini <span class="tooltip">ℹ️<span class="tooltiptext">Agar AI memberi solusi spesifik.</span></span></label>
                        <textarea name="kesulitan" required><?php echo htmlspecialchars($kesulitan); ?></textarea>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit" onclick="document.getElementById('loading').style.display='flex'">Analisis Sekarang</button>
            </form>

            <?php if ($error): ?>
                <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
        </div>


        <!-- Panel Kanan: AI Output -->
        <div class="panel result-panel">
            <div class="loading-overlay" id="loading">Memproses Analisis AI...</div>
            
            <?php if ($result): ?>
                <div class="top-cards">
                    <div class="result-card">
                        <div class="result-title">Target IPS Anda</div>
                        <div class="result-value"><?php echo htmlspecialchars($result['target_ips']); ?></div>
                    </div>
                    
                    <div class="formula-card">
                        <div class="result-title formula-title">
                            Rumus Kalkulasi IPS
                            <span class="tooltip">ℹ️<span class="tooltiptext">IPS (Indeks Prestasi Semester) adalah target rata-rata nilai yang wajib Anda raih di semester depan agar target IPK baru tercapai.</span></span>
                        </div>
                        <div class="formula-expression">IPS = (Target IPK &times; Sem) - (IPK Lama &times; (Sem-1))</div>
                        <div class="formula-simulation">
                            IPS = (<?php echo number_format($ipk_target, 2); ?> &times; <?php echo $semester; ?>) - (<?php echo number_format($ipk_lama, 2); ?> &times; <?php echo ($semester - 1); ?>)<br>
                            <strong>IPS = <?php echo htmlspecialchars($result['target_ips']); ?></strong>
                        </div>
                    </div>
                </div>

                <div class="ai-header">
                    <svg style="width:24px;height:24px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Strategi Belajar Personal dari Gemini AI
                </div>
                
                <div class="scrollable-content strategy-content">
                    <?php echo $result['strategy']; ?>
                </div>
            <?php else: ?>
                <div style="display:flex; height:100%; justify-content:center; align-items:center; color:var(--text-muted); font-size:18px;">
                    Isi form di samping untuk melihat analisis AI.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Light/Dark Mode Toggle
        const themeToggle = document.getElementById('themeToggle');
        const moonIcon = document.getElementById('moonIcon');
        const sunIcon = document.getElementById('sunIcon');
        
        // Cek localStorage
        if (localStorage.getItem('theme') === 'light') {
            document.body.classList.add('light-mode');
            moonIcon.style.display = 'none';
            sunIcon.style.display = 'block';
        }

        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('light-mode');
            const isLight = document.body.classList.contains('light-mode');
            localStorage.setItem('theme', isLight ? 'light' : 'dark');
            
            moonIcon.style.display = isLight ? 'none' : 'block';
            sunIcon.style.display = isLight ? 'block' : 'none';
        });

        // Dynamic Placeholders (sama seperti versi sebelumnya)
        const semesterInput = document.getElementById('semester');
        const ipkLamaInput = document.getElementById('ipk_lama');
        const ipkTargetInput = document.getElementById('ipk_target');

        function updatePlaceholders() {
            const semester = parseInt(semesterInput.value);
            const ipkLama = parseFloat(ipkLamaInput.value);

            if (!isNaN(semester) && semester > 1) {
                ipkLamaInput.placeholder = `Rata-rata ${semester - 1} semester`;
            } else {
                ipkLamaInput.placeholder = "Masukkan IPK";
            }

            if (!isNaN(semester) && semester >= 1 && !isNaN(ipkLama) && ipkLama >= 0 && ipkLama <= 4.0) {
                const maxIpk = ((ipkLama * (semester - 1)) + 4.0) / semester;
                const maxIpkRounded = Math.floor(maxIpk * 100) / 100;
                ipkTargetInput.placeholder = `Maks: ${maxIpkRounded.toFixed(2)}`;
            } else {
                ipkTargetInput.placeholder = "Maks 4.00";
            }
        }

        semesterInput.addEventListener('input', updatePlaceholders);
        ipkLamaInput.addEventListener('input', updatePlaceholders);
        window.addEventListener('DOMContentLoaded', updatePlaceholders);
    </script>
</body>
</html>
