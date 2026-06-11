import sys
import json
import os
import argparse
import warnings
warnings.filterwarnings("ignore")

try:
    import google.generativeai as genai
    from dotenv import load_dotenv
    DEPENDENCIES_INSTALLED = True
except ImportError:
    DEPENDENCIES_INSTALLED = False

def generate_learning_strategy(nama, jurusan, prodi, semester, ipk_lama, ipk_target, kesulitan, target_ips):
    """
    Menghasilkan strategi belajar personal menggunakan Gemini AI dengan format HTML.
    """
    if not DEPENDENCIES_INSTALLED:
        return "<p>Library AI belum terinstall. Jalankan perintah: <b>pip install -r requirements.txt</b></p>"

    # Ambil path absolut direktori script untuk memuat .env
    script_dir = os.path.dirname(os.path.abspath(__file__))
    load_dotenv(dotenv_path=os.path.join(script_dir, '.env'))
    api_key = os.getenv("GEMINI_API_KEY")
    
    if not api_key or api_key == "masukkan_api_key_gemini_anda_di_sini":
        return "<p>API Key Gemini tidak ditemukan atau belum valid. Harap isi <b>GEMINI_API_KEY</b> di file <b>.env</b> terlebih dahulu.</p>"

    try:
        genai.configure(api_key=api_key)
        model = genai.GenerativeModel('gemini-2.5-flash')
        
        nama_panggilan = nama if nama else "Teman"
        
        prompt = f"""
        Anda adalah seorang konsultan akademik / mentor mahasiswa yang santai, bersahabat, suportif, dan ramah (tidak kaku/formal seperti robot AI biasa).
        Tugas Anda adalah memberikan saran strategi belajar personal untuk mahasiswa berikut:
        
        - Nama: {nama_panggilan}
        - Jurusan/Prodi: {jurusan} / {prodi}
        - Semester Saat Ini: {semester}
        - IPK Saat Ini: {ipk_lama:.2f}
        - Target IPK Semester Depan: {ipk_target:.2f}
        - Kesulitan Semester Ini: {kesulitan}
        - Target IPS yang Harus Dicapai: {target_ips:.2f}
        
        Panduan Menulis Respon:
        1. Sapa mahasiswa dengan panggilannya secara akrab (contoh: "Halo {nama_panggilan}!").
        2. Berikan analisis singkat tentang target IPS {target_ips:.2f}.
           - Jika target IPS > 4.0: Beri tahu secara santai bahwa target ini secara matematis mustahil dalam satu semester (maksimal 4.0), sarankan untuk tetap berjuang maksimal atau bicarakan strategi jangka panjang.
           - Jika target IPS antara 3.51 - 4.0: Semangati mereka karena ini target tinggi, berikan tips fokus penuh.
           - Jika target IPS rendah (misal < 3.0): Katakan posisinya aman tapi jangan lengah.
        3. Hubungkan strategi belajar dengan Jurusan/Prodi ({prodi}) dan selesaikan masalah dari Kesulitan yang dihadapi ({kesulitan}).
        4. Tulis dalam 2-3 paragraf pendek saja agar tidak terlalu panjang. Gunakan bahasa Indonesia santai (seperti "kamu", "yuk", "aja", "nggak").
        5. PENTING: Gunakan tag HTML langsung seperti <p>, <b>teks tebal</b>, dan <ul> / <li> untuk membuat list/bullet points agar mudah dibaca di halaman web. JANGAN gunakan format markdown seperti '**' atau '#'.
        """
        
        response = model.generate_content(prompt)
        text_response = response.text
        
        # Bersihkan jika AI masih bandel menggunakan markdown bold atau kode blok html
        text_response = text_response.replace('```html', '').replace('```', '')
        text_response = text_response.replace('**', '') 
        
        return text_response.strip()
        
    except Exception as e:
        return f"<p>Gagal menghubungi AI Gemini: {str(e)}</p>"

def main():
    parser = argparse.ArgumentParser(description="Kalkulator Cumlaude AI Engine")
    parser.add_argument("--nama", default="")
    parser.add_argument("--jurusan", required=True)
    parser.add_argument("--prodi", required=True)
    parser.add_argument("--semester", type=int, required=True)
    parser.add_argument("--ipk-lama", type=float, required=True)
    parser.add_argument("--ipk-target", type=float, required=True)
    parser.add_argument("--kesulitan", required=True)
    
    try:
        args = parser.parse_args()
        
        # Validasi Input
        if args.ipk_lama < 0 or args.ipk_lama > 4.0:
            raise ValueError("IPK saat ini harus di antara 0.00 dan 4.00")
        if args.ipk_target < 0 or args.ipk_target > 4.0:
            raise ValueError("Target IPK harus di antara 0.00 dan 4.00")
        if args.semester < 1:
            raise ValueError("Semester harus minimal semester 1")
            
        # Perhitungan target IPS dengan SKS fix per semester
        # IPS = Target_IPK * Semester - IPK_Lama * (Semester - 1)
        target_ips = (args.ipk_target * args.semester) - (args.ipk_lama * (args.semester - 1))
        
        # Meminta AI menghasilkan strategi belajar personal
        strategy = generate_learning_strategy(
            args.nama, args.jurusan, args.prodi, args.semester,
            args.ipk_lama, args.ipk_target, args.kesulitan, target_ips
        )
        
        response = {
            "success": True,
            "target_ips": round(target_ips, 2) if target_ips >= 0 else 0.0,
            "strategy": strategy
        }
        print(json.dumps(response))
        
    except Exception as e:
        error_response = {
            "success": False,
            "error": str(e)
        }
        print(json.dumps(error_response))

if __name__ == "__main__":
    main()
