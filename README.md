# 🏁 Tugas Akhir (TA) - Final Project

**Nama Mahasiswa**: Syomeron Ansell Widjaya <br>
**NRP**: 5025211250 <br>
**Judul TA**: Pengembangan *Website* Pemantauan dan Personalisasi gGizi pada Modul *Food Diary* dan Perhitungan *Total Daily Energy Expenditure* <br>
**Dosen Pembimbing**: Ir. Adhatus Solichah Ahmadiyah, S.Kom., M.Sc. <br>
**Dosen Ko-pembimbing**: Ratih Nur Esti Anggraini, S.Kom., M.Sc., Ph.D. <br>

---

## 📺 Demo Aplikasi  
Embed video demo di bawah ini (ganti `VIDEO_ID` dengan ID video YouTube Anda):  

[![Demo Aplikasi](https://github.com/Informatics-ITS/ta-Ansell10/blob/main/Tampilan%20aplikasi.png)](https://www.youtube.com/watch?v=VIDEO_ID)  
*Klik gambar di atas untuk menonton demo*

---

*Konten selanjutnya hanya merupakan contoh awalan yang baik. Anda dapat berimprovisasi bila diperlukan.*

## 🛠 Panduan Instalasi & Menjalankan Software  

### Prasyarat  
- Daftar dependensi :
  - PHP v8.2+
  - Node.js v18+
  - MySQL 8.0
  - Composer
  - Git

### Langkah-langkah
1. **Clone Repository**
   - Clone repositori dari GitHub dengan perintah berikut:
     ```bash
     git clone https://github.com/Informatics-ITS/ta-Ansell10.git
     cd ta-Ansell10
     ```

2. **Instalasi Dependensi & Konfigurasi Backend**
   - Arahkan ke direktori **backend** dan instal dependensi menggunakan **Composer**:
     ```bash
     cd backend
     composer install
     ```
   - Salin file `.env.example` menjadi `.env`:
     ```bash
     cp .env.example .env
     ```
   - **Buka file `.env`** dan sesuaikan pengaturan **`APP_URL`** untuk backend. Misalnya, jika frontend berjalan di port **3000** dan backend di **8000**:
     ```env
     APP_URL=http://localhost:8000
     FRONTEND_URL=http://localhost:3000
     ```
   - Jalankan perintah berikut untuk menghasilkan aplikasi key, migrasi database, dan menjalankan seeder:
     ```bash
     php artisan key:generate    # Menghasilkan aplikasi key
     php artisan migrate         # Menjalankan migrasi database
     php artisan db:seed         # Menjalankan seeder untuk mengisi database dengan data default
     ```

3. **Instalasi Dependensi & Konfigurasi Frontend**
   - Arahkan ke direktori **frontend** dan instal dependensi menggunakan **npm**:
     ```bash
     cd ../frontend
     npm install
     ```
   - Salin file `.env.example` menjadi `.env.local`:
     ```bash
     cp .env.example .env.local
     ```
   - **Buka file `.env.local`** dan sesuaikan URL API backend di **`NEXT_PUBLIC_API_URL`** untuk frontend. Misalnya, jika backend berjalan di port **8000**, atur **`NEXT_PUBLIC_API_URL`** di frontend seperti berikut:
     ```env
     NEXT_PUBLIC_API_URL=http://localhost:8000/api
     ```

4. **Jalankan Aplikasi**
   - **Jalankan Backend**:
     Setelah konfigurasi selesai di backend, jalankan server Laravel:
     ```bash
     cd ../backend
     php artisan serve
     ```
   - **Jalankan Frontend**:
     Untuk menjalankan aplikasi frontend, gunakan perintah berikut di direktori frontend:
     ```bash
     cd ../frontend
     npm start
     ```

5. **Buka Browser dan Kunjungi**
   - **Backend**: [http://localhost:8000](http://localhost:8000) (Untuk API dan aplikasi backend)
   - **Frontend**: [http://localhost:3000](http://localhost:3000) (Untuk aplikasi frontend)

---

## 📚 Dokumentasi Tambahan

- [![Dokumentasi API]](docs/api.md)
- [![Diagram Arsitektur]](docs/architecture.png)
- [![Struktur Basis Data]](docs/database_schema.sql)

---

## ⁉️ Pertanyaan?

Hubungi: 
- Penulis: syomeronansell@gmail.com
- Pembimbing Utama: adhatus@if.its.ac.id
