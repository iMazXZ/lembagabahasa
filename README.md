# 🏛️ Web Lembaga Bahasa UM Metro

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-3.x-F59E0B?style=for-the-badge&logo=php&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

**Sistem Informasi Modern untuk Lembaga Bahasa Universitas Muhammadiyah Metro**

[Demo](#demo) • [Instalasi](#instalasi) • [Fitur](#fitur) • [Dokumentasi](#dokumentasi) • [Kontribusi](#kontribusi)

</div>

---

## 📋 Tentang Project

Web Lembaga Bahasa UM Metro adalah sistem informasi berbasis web yang dibangun dengan teknologi modern untuk mendukung operasional dan manajemen Lembaga Bahasa Universitas Muhammadiyah Metro. Sistem ini menggabungkan kemudahan penggunaan dengan fitur-fitur canggih untuk memberikan pengalaman terbaik bagi administrator, instruktur, dan peserta kursus.

### ✨ Mengapa Project Ini Istimewa?

-   🚀 **Modern Tech Stack** - Dibangun dengan Laravel 11 dan Filament 3.x
-   🎨 **UI/UX Elegan** - Interface yang clean dan responsive
-   🔐 **Keamanan Tinggi** - Implementasi security best practices
-   📱 **Mobile Friendly** - Akses mudah dari berbagai device
-   ⚡ **Performa Optimal** - Optimized untuk kecepatan dan efisiensi

## 🎯 Fitur Utama

### 🏢 **Manajemen Institusi**

-   Dashboard admin yang komprehensif
-   Manajemen pengguna dengan role-based access
-   Sistem monitoring dan reporting
-   Integrasi dengan sistem akademik UM Metro

### 📚 **Program Kursus**

-   Manajemen kelas bahasa (Inggris, Arab, dll.)
-   Penjadwalan otomatis
-   Tracking progress peserta
-   Sertifikat digital

### 👥 **Manajemen Peserta**

-   Registrasi online yang mudah
-   Profil peserta yang lengkap
-   History kursus dan pencapaian
-   Notifikasi real-time

### 📊 **Analytics & Reporting**

-   Dashboard analytics yang powerful
-   Export data dalam berbagai format
-   Grafik performa dan statistik
-   Laporan keuangan terintegrasi

## 🛠️ Tech Stack

| Kategori           | Teknologi                                |
| ------------------ | ---------------------------------------- |
| **Backend**        | Laravel 11.x, PHP 8.2+                   |
| **Admin Panel**    | Filament 3.x                             |
| **Database**       | MySQL 8.0+                               |
| **Frontend**       | Blade Templates, Alpine.js, Tailwind CSS |
| **Authentication** | Laravel Sanctum                          |
| **File Storage**   | Laravel Storage                          |
| **Queue**          | Redis/Database                           |

## 🚀 Instalasi

### Prasyarat

-   PHP 8.2 atau lebih tinggi
-   Composer
-   Node.js & NPM
-   MySQL 8.0+
-   Redis (opsional, untuk queue)

### Langkah Instalasi

1. **Clone Repository**

    ```bash
    git clone https://github.com/username/web-lembaga-bahasa-um-metro.git
    cd web-lembaga-bahasa-um-metro
    ```

2. **Install Dependencies**

    ```bash
    composer install
    npm install
    ```

3. **Environment Setup**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Database Configuration**
   Edit file `.env` dan sesuaikan konfigurasi database:

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=lembaga_bahasa_um
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

5. **Migration & Seeding**

    ```bash
    php artisan migrate
    php artisan db:seed
    ```

6. **Build Assets**

    ```bash
    npm run build
    ```

7. **Storage Link**

    ```bash
    php artisan storage:link
    ```

8. **Run Server**
    ```bash
    php artisan serve
    ```

Akses aplikasi di `http://localhost:8000`

### 🔑 Default Login

-   **Admin**: admin@ummetro.ac.id / password
-   **Instruktur**: instruktur@ummetro.ac.id / password

## 📱 Screenshots

<details>
<summary>Klik untuk melihat screenshots</summary>

### Dashboard Admin

![Dashboard](screenshots/dashboard.png)

### Management Kursus

![Kursus](screenshots/kursus.png)

### Profile Peserta

![Profile](screenshots/profile.png)

</details>

## 🔧 Konfigurasi

### Mail Configuration

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
```

### File Upload Configuration

```env
FILESYSTEM_DISK=public
MAX_UPLOAD_SIZE=10240  # 10MB
```

## 🧪 Testing

Jalankan test suite:

```bash
php artisan test
```

Dengan coverage:

```bash
php artisan test --coverage
```

## 📚 Dokumentasi API

API documentation tersedia di `/docs` setelah aplikasi running.

Atau akses Postman Collection: [Link Collection]

## 🤝 Kontribusi

Kami sangat menghargai kontribusi dari komunitas! Berikut cara berkontribusi:

1. Fork repository ini
2. Buat branch fitur (`git checkout -b feature/amazing-feature`)
3. Commit perubahan (`git commit -m 'Add some amazing feature'`)
4. Push ke branch (`git push origin feature/amazing-feature`)
5. Buat Pull Request

### 📋 Guidelines

-   Ikuti PSR-12 coding standards
-   Tulis test untuk fitur baru
-   Update dokumentasi jika diperlukan
-   Gunakan commit message yang descriptive

## 📄 License

Project ini dilisensikan under MIT License - lihat file [LICENSE](LICENSE) untuk detail.

## 👨‍💻 Tim Pengembang

<table>
  <tr>
    <td align="center">
      <a href="https://github.com/yourusername">
        <img src="https://github.com/yourusername.png" width="100px;" alt=""/>
        <br />
        <sub><b>Your Name</b></sub>
      </a>
      <br />
      <sub>Full Stack Developer</sub>
    </td>
    <!-- Tambahkan anggota tim lainnya -->
  </tr>
</table>

## 🙏 Acknowledgments

-   [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
-   [Filament](https://filamentphp.com) - Accelerated Laravel Admin Panel
-   [Universitas Muhammadiyah Metro](https://ummetro.ac.id) - Institusi yang luar biasa
-   Semua kontributor yang telah membantu pengembangan project ini

## 📞 Support

Jika Anda memiliki pertanyaan atau membutuhkan bantuan:

-   📧 Email: lembaga.bahasa@ummetro.ac.id
-   🌐 Website: [ummetro.ac.id](https://ummetro.ac.id)
-   📱 WhatsApp: +62-xxx-xxxx-xxxx

---

<div align="center">

**Dibuat dengan ❤️ untuk kemajuan pendidikan bahasa di Indonesia**

⭐ Jangan lupa berikan star jika project ini bermanfaat!

[⬆ Back to Top](#-web-lembaga-bahasa-um-metro)

</div>
