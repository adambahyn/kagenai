<?php
$result = null;
$error = null;

// Tangkap input POST
$nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$jurusan = isset($_POST['jurusan']) ? trim($_POST['jurusan']) : '';
$prodi = isset($_POST['prodi']) ? trim($_POST['prodi']) : '';
$semester = filter_input(INPUT_POST, 'semester', FILTER_VALIDATE_INT);
$ipk_lama = filter_input(INPUT_POST, 'ipk_lama', FILTER_VALIDATE_FLOAT);
$ipk_target = filter_input(INPUT_POST, 'ipk_target', FILTER_VALIDATE_FLOAT);
$kesulitan = isset($_POST['kesulitan']) ? trim($_POST['kesulitan']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($jurusan !== '' && $prodi !== '' && $semester !== false && $ipk_lama !== false && $ipk_target !== false && $kesulitan !== '') {
        
        $python_executable = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'python' : 'python3';
        
        // Membangun command arguments secara dinamis dan aman
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
            $error = "Gagal memanggil AI Engine. Pastikan Python terinstal dan dependensi sudah terpasang.";
        }
    } else {
        $error = "Harap isi semua kolom wajib dengan benar.";
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
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --primary-hover: #4338ca;
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.08);
            --danger: #f87171;
            --success: #34d399;
            --input-bg: rgba(15, 23, 42, 0.6);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        body {
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%),
                radial-gradient(at 50% 0%, hsla(225,39%,30%,0.3) 0, transparent 50%),
                radial-gradient(at 100% 0%, hsla(339,49%,30%,0.2) 0, transparent 50%),
                radial-gradient(at 100% 100%, hsla(225,39%,30%,0.2) 0, transparent 50%),
                radial-gradient(at 0% 100%, hsla(253,16%,7%,1) 0, transparent 50%);
            background-attachment: fixed;
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 580px;
            padding: 40px;
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        /* Grid Layout ketika ada hasil kalkulasi */
        .container.has-result {
            max-width: 1100px;
            display: grid;
            grid-template-columns: 1.1fr 1.4fr;
            gap: 40px;
        }
        
        .header {
            grid-column: span 1;
            margin-bottom: 28px;
        }
        
        .container.has-result .header {
            grid-column: span 2;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 20px;
            margin-bottom: 10px;
        }
        
        h1 {
            font-size: 32px;
            font-weight: 800;
            background: linear-gradient(135deg, #a5b4fc, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        
        p.subtitle {
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.6;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .form-group {
            margin-bottom: 18px;
        }
        
        .form-group.full-width {
            grid-column: span 2;
        }
        
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #e2e8f0;
            letter-spacing: 0.5px;
        }
        
        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            font-size: 15px;
            color: var(--text-main);
            background-color: var(--input-bg);
            transition: all 0.25s ease;
            outline: none;
        }
        
        input:focus,
        textarea:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(129, 140, 248, 0.2);
            background-color: rgba(30, 41, 59, 0.9);
        }
        
        textarea {
            resize: vertical;
            min-height: 90px;
        }
        
        button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.4);
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.5);
            background: linear-gradient(135deg, var(--primary-hover), var(--primary));
        }
        
        button:active {
            transform: translateY(0);
        }
        
        /* Hasil Kalkulasi - Kolom Kanan */
        .result-container {
            display: flex;
            flex-direction: column;
            gap: 24px;
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        
        .result-card {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05));
            border: 1px solid rgba(52, 211, 153, 0.2);
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .result-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--success);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 10px;
        }
        
        .result-value {
            font-size: 56px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1;
            text-shadow: 0 0 20px rgba(52, 211, 153, 0.3);
        }
        
        .strategy-card {
            background: rgba(30, 41, 59, 0.9);
            border: 1px solid var(--border-color);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            flex-grow: 1;
        }
        
        .strategy-header {
            font-size: 16px;
            font-weight: 700;
            color: var(--primary-light);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 12px;
        }
        
        /* Style untuk output HTML dari AI */
        .strategy-content {
            font-size: 15px;
            line-height: 1.7;
            color: #e2e8f0;
        }
        
        .strategy-content p {
            margin-bottom: 16px;
        }
        
        .strategy-content b, 
        .strategy-content strong {
            color: #ffffff;
            font-weight: 700;
        }
        
        .strategy-content ul, 
        .strategy-content ol {
            margin-left: 20px;
            margin-bottom: 16px;
        }
        
        .strategy-content li {
            margin-bottom: 8px;
        }
        
        .error-box {
            grid-column: span 2;
            margin-top: 24px;
            padding: 16px 20px;
            border-radius: 14px;
            background-color: rgba(248, 113, 113, 0.1);
            border: 1px solid rgba(248, 113, 113, 0.2);
            color: var(--danger);
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideUp 0.4s ease forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideUp {
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive Breakpoints */
        @media (max-width: 900px) {
            .container.has-result {
                grid-template-columns: 1fr;
                max-width: 580px;
            }
            .container.has-result .header {
                grid-column: span 1;
            }
            .error-box {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <div class="container <?php echo $result ? 'has-result' : ''; ?>">
        <div class="header">
            <h1>Kalkulator Cumlaude</h1>
            <p class="subtitle">Sistem evaluasi akademik khusus politeknik (SKS Semester Tetap). Cari tahu target IPS Anda & dapatkan bimbingan strategi belajar dari AI.</p>
        </div>
        
        <form method="POST" action="" style="display: flex; flex-direction: column;">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nama">Nama (Opsional)</label>
                    <input type="text" id="nama" name="nama" placeholder="Contoh: Adam" value="<?php echo htmlspecialchars($nama); ?>">
                </div>
                
                <div class="form-group">
                    <label for="semester">Semester Saat Ini</label>
                    <input type="number" id="semester" name="semester" min="1" max="10" required placeholder="Contoh: 3" value="<?php echo htmlspecialchars($semester ?: ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="jurusan">Jurusan</label>
                    <input type="text" id="jurusan" name="jurusan" required placeholder="Contoh: Teknik Elektro" value="<?php echo htmlspecialchars($jurusan); ?>">
                </div>
                
                <div class="form-group">
                    <label for="prodi">Program Studi</label>
                    <input type="text" id="prodi" name="prodi" required placeholder="Contoh: D4 Teknik Informatika" value="<?php echo htmlspecialchars($prodi); ?>">
                </div>
                
                <div class="form-group">
                    <label for="ipk_lama">IPK Saat Ini</label>
                    <input type="number" id="ipk_lama" name="ipk_lama" step="0.01" min="0" max="4" required placeholder="Contoh: 3.25" value="<?php echo htmlspecialchars($ipk_lama ?: ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="ipk_target">Keinginan IPK Semester Depan</label>
                    <input type="number" id="ipk_target" name="ipk_target" step="0.01" min="0" max="4" required placeholder="Contoh: 3.55" value="<?php echo htmlspecialchars($ipk_target ?: ''); ?>">
                </div>
                
                <div class="form-group full-width">
                    <label for="kesulitan">Kesulitan yang Dihadapi Semester Ini</label>
                    <textarea id="kesulitan" name="kesulitan" required placeholder="Jelaskan hambatan belajarmu, misal: Banyak tugas praktikum, kesulitan manajemen waktu, kurang paham konsep X, dll." ><?php echo htmlspecialchars($kesulitan); ?></textarea>
                </div>
            </div>
            
            <button type="submit">Analisis & Hitung Target</button>
        </form>
        
        <?php if ($error): ?>
            <div class="error-box">
                <svg style="width:20px;height:20px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($result): ?>
            <div class="result-container">
                <div class="result-card">
                    <div class="result-title">Target IPS Semester Depan</div>
                    <div class="result-value"><?php echo htmlspecialchars($result['target_ips']); ?></div>
                </div>
                
                <div class="strategy-card">
                    <div class="strategy-header">
                        <svg style="width:20px;height:20px" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                        <span>Rekomendasi Belajar Personal (Gemini AI)</span>
                    </div>
                    <div class="strategy-content">
                        <?php echo $result['strategy']; // Ditampilkan langsung karena AI mengembalikan HTML terformat aman ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
