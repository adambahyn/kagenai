<?php
$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ipk_lama = filter_input(INPUT_POST, 'ipk_lama', FILTER_VALIDATE_FLOAT);
    $sks_lama = filter_input(INPUT_POST, 'sks_lama', FILTER_VALIDATE_FLOAT);
    $sks_baru = filter_input(INPUT_POST, 'sks_baru', FILTER_VALIDATE_FLOAT);

    if ($ipk_lama !== false && $sks_lama !== false && $sks_baru !== false) {
        // Eksekusi Python script via shell_exec
        $python_executable = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'python' : 'python3';
        $command = escapeshellcmd("$python_executable ai_engine.py " . escapeshellarg($ipk_lama) . " " . escapeshellarg($sks_lama) . " " . escapeshellarg($sks_baru));
        $output = shell_exec($command);
        
        if ($output) {
            $data = json_decode($output, true);
            if ($data && isset($data['success']) && $data['success'] === true) {
                $result = $data;
            } else {
                $error = isset($data['error']) ? $data['error'] : "Terjadi kesalahan saat parsing output AI Engine.";
            }
        } else {
            $error = "Gagal mengeksekusi script Python. Pastikan Python sudah terinstall dan masuk ke PATH Environment Variable.";
        }
    } else {
        $error = "Input tidak valid. Harap masukkan angka yang benar.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator Cumlaude</title>
    <!-- Menggunakan font Google 'Inter' untuk desain modern -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg-color: #f8fafc;
            --card-bg: rgba(255, 255, 255, 0.8);
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --danger: #ef4444;
            --success: #10b981;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background-color: var(--bg-color);
            background-image: radial-gradient(at 40% 20%, hsla(28,100%,74%,1) 0px, transparent 50%),
                              radial-gradient(at 80% 0%, hsla(189,100%,56%,1) 0px, transparent 50%),
                              radial-gradient(at 0% 50%, hsla(355,100%,93%,1) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 480px;
            padding: 40px;
            transition: all 0.3s ease;
        }
        
        .header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        h1 {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }
        
        p.subtitle {
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.5;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-main);
        }
        
        input[type="number"] {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            font-size: 16px;
            background-color: rgba(255, 255, 255, 0.9);
            transition: all 0.2s ease;
            outline: none;
        }
        
        input[type="number"]:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
            background-color: #ffffff;
        }
        
        button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary), #4f46e5);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .result-box {
            margin-top: 32px;
            padding: 24px;
            border-radius: 16px;
            background-color: rgba(16, 185, 129, 0.05);
            border: 1px solid rgba(16, 185, 129, 0.2);
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        
        .result-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--success);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        
        .result-value {
            font-size: 42px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 20px;
            line-height: 1;
        }
        
        .strategy {
            font-size: 14px;
            line-height: 1.6;
            color: var(--text-main);
            background-color: white;
            padding: 16px;
            border-radius: 12px;
            border-left: 4px solid var(--success);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .error-box {
            margin-top: 24px;
            padding: 16px;
            border-radius: 12px;
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--danger);
            font-size: 14px;
            font-weight: 500;
            animation: slideUp 0.4s ease forwards;
        }
        
        @keyframes slideUp {
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Kalkulator Cumlaude</h1>
            <p class="subtitle">Hitung target Indeks Prestasi Semester (IPS) Anda agar bisa mencapai IPK minimal 3.51 (Cumlaude)</p>
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="ipk_lama">IPK Saat Ini</label>
                <input type="number" id="ipk_lama" name="ipk_lama" step="0.01" min="0" max="4" required placeholder="Contoh: 3.25" value="<?php echo isset($_POST['ipk_lama']) ? htmlspecialchars($_POST['ipk_lama']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="sks_lama">Total SKS Saat Ini</label>
                <input type="number" id="sks_lama" name="sks_lama" step="1" min="1" required placeholder="Contoh: 100" value="<?php echo isset($_POST['sks_lama']) ? htmlspecialchars($_POST['sks_lama']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="sks_baru">Rencana SKS Semester Depan</label>
                <input type="number" id="sks_baru" name="sks_baru" step="1" min="1" max="24" required placeholder="Contoh: 20" value="<?php echo isset($_POST['sks_baru']) ? htmlspecialchars($_POST['sks_baru']) : ''; ?>">
            </div>
            
            <button type="submit">Hitung Target IPS</button>
        </form>
        
        <?php if ($error): ?>
            <div class="error-box">
                <svg style="width:20px;height:20px;vertical-align:middle;margin-right:8px" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($result): ?>
            <div class="result-box">
                <div class="result-title">Target IPS Semester Depan</div>
                <div class="result-value"><?php echo htmlspecialchars($result['target_ips']); ?></div>
                <div class="strategy">
                    <strong style="display:block;margin-bottom:4px;color:var(--text-muted)">Rekomendasi AI Engine:</strong>
                    <?php echo htmlspecialchars($result['strategy']); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
