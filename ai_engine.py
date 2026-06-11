import sys
import json
import os
import warnings
warnings.filterwarnings("ignore")


try:
    import google.generativeai as genai
    from dotenv import load_dotenv
    DEPENDENCIES_INSTALLED = True
except ImportError:
    DEPENDENCIES_INSTALLED = False

def generate_learning_strategy(ipk_lama, sks_lama, sks_baru, target_ips):
    """
    Fungsi untuk menghasilkan strategi belajar menggunakan Gemini AI.
    """
    if not DEPENDENCIES_INSTALLED:
        return "Library AI belum terinstall. Jalankan perintah: pip install -r requirements.txt"

    # Gunakan path absolut file ini untuk memuat .env
    script_dir = os.path.dirname(os.path.abspath(__file__))
    load_dotenv(dotenv_path=os.path.join(script_dir, '.env'))
    api_key = os.getenv("GEMINI_API_KEY")
    
    if not api_key or api_key == "masukkan_api_key_gemini_anda_di_sini":
        return "API Key Gemini tidak ditemukan atau belum valid. Harap isi GEMINI_API_KEY di file .env"

    try:
        genai.configure(api_key=api_key)
        # Menggunakan gemini-2.5-flash yang didukung di lingkungan Anda
        model = genai.GenerativeModel('gemini-2.5-flash')
        
        prompt = f"""
        Seorang mahasiswa memiliki IPK saat ini {ipk_lama} dengan total {sks_lama} SKS.
        Semester depan, mahasiswa ini akan mengambil {sks_baru} SKS.
        Untuk mencapai atau mempertahankan target IPK Cumlaude (3.51), mahasiswa ini memerlukan target IPS sebesar {target_ips:.2f}.
        
        Tugas Anda:
        Berikan analisis singkat, kata-kata motivasi, dan 3 strategi belajar spesifik yang realistis untuk mahasiswa ini agar bisa mencapai target IPS tersebut.
        - Jika target IPS > 4.0: Beri tahu dengan sopan bahwa target itu mustahil dalam satu semester, dan sarankan untuk mengambil lebih banyak SKS atau mengejar cumlaude di semester berikutnya.
        - Jika target IPS antara 3.5 - 4.0: Berikan strategi belajar yang cukup intens dan ketat.
        - Jika target IPS < 3.0: Beritahu bahwa posisinya relatif aman tapi ingatkan untuk tetap konsisten dan jangan terlena.
        
        Gunakan bahasa Indonesia yang santai tapi profesional, format dengan list/bullet point agar rapi, dan maksimal 3 paragraf. Jangan gunakan markdown yang terlalu kompleks karena akan di render di HTML biasa, gunakan line break biasa atau teks biasa (html bisa render text biasa).
        """
        
        response = model.generate_content(prompt)
        
        # Konversi markdown sederhana dari AI (jika ada) ke format yang lebih aman dibaca di html tanpa library markdown. 
        # Kita kembalikan text mentahnya, UI di PHP sudah menampilkan di div/paragraf, jadi akan terbaca.
        # Atau sekedar biarkan saja karena browser umumnya merender teks dari JSON dengan baik.
        text_response = response.text
        # Bersihkan sedikit jika ada backticks atau format aneh
        text_response = text_response.replace('**', '') # Hapus bold markdown agar tidak mengganggu jika tidak di render dengan library khusus
        
        return text_response
    except Exception as e:
        return f"Gagal menghubungi AI Gemini: {str(e)}"

def main():
    try:
        if len(sys.argv) != 4:
            raise ValueError("Format argumen salah. Gunakan: python ai_engine.py [ipk_lama] [sks_lama] [sks_baru]")
        
        ipk_lama = float(sys.argv[1])
        sks_lama = float(sys.argv[2])
        sks_baru = float(sys.argv[3])
        
        if ipk_lama < 0 or ipk_lama > 4.0:
            raise ValueError("IPK saat ini harus antara 0 dan 4.00.")
        if sks_lama <= 0:
            raise ValueError("Total SKS saat ini harus lebih dari 0.")
        if sks_baru <= 0:
            raise ValueError("Rencana SKS baru harus lebih dari 0.")
            
        TARGET_IPK = 3.51
        
        target_ips = (TARGET_IPK * (sks_lama + sks_baru) - (ipk_lama * sks_lama)) / sks_baru
        
        # Meminta Gemini AI menghasilkan strategi
        strategy = generate_learning_strategy(ipk_lama, sks_lama, sks_baru, target_ips)
        
        response = {
            "success": True,
            "target_ips": round(target_ips, 2),
            "strategy": strategy
        }
        
        print(json.dumps(response))
        
    except ValueError as ve:
        error_response = {
            "success": False,
            "error": str(ve)
        }
        print(json.dumps(error_response))
    except Exception as e:
        error_response = {
            "success": False,
            "error": f"Terjadi kesalahan internal: {str(e)}"
        }
        print(json.dumps(error_response))

if __name__ == "__main__":
    main()
