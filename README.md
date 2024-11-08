## Backend
- run php artisan serve --port=9100
- or create index.php at root page

## Tech Specs
- Laravel            8.4
- Laravel Passport   10.2
- Laravel Dompdf     1.0
- Maatwebsite Excel  3.1
- Sentry Laravel     3.1
- intervention Image 2.7

## Design Pattern
- use Service & Repository Pattern
- use base class & interface

## Package Used
- composer require laravel/passport
    - php artisan passport:install

## Useful Command
- php artisan make:migration create_project_table
- php artisan make:controller API/ProjectController --api --model=Project
- php artisan storage:link
The [C:\xampp\htdocs\yudha_apps_boilerplate\laravel_backend_boilerplate\public\storage] link has been connected to [C:\xampp\htdocs\yudha_apps_boilerplate\laravel_backend_boilerplate\storage\app/public].
The links have been created.
- php artisan api:generator Models
- php artisan make:import OrderReferencesImport --model=OrderReference

## Flow
1. Bills
   - Admin/Draft
   - Open (Closing)
   - Verified (Pay)

## Notes
- data transaksi order sebanyak 50.000 data, query sudah mulai lama (9-10 detik) & berat
- import data referensi order via excel sebanyak 1.000 data masih cepat (5-6) detik

## Todo(s)
1. pasang google analytic di compro
2. pasang captcha untuk proteksi dari spam (cek tarif, tracking, contact us)
3. pasang package laravel backup
4. log semua error menggunakan log bawaan laravel

## Load Testing
up to 164 request/second with 10 concurrent users at shared hosting

## Feature(s)
A. Aplikasi Web Internal
   1. Dashboard
   2. Modul Transaksi
   3. Modul Master Data
   4. Modul Gudang
   5. Modul Marketing
   6. Modul Akunting (AP, AR, GL)
   7. Modul Approval
   8. Setting User & Departemen
   9. Modul Profile

B. Company Profile
   1. Slider Utama
   2. Banner Promosi
   3. Artikel
   4. Gallery
   5. Tracking
   6. Cek Tarif
   7. Layanan
   8. Contact Us
   8. About Us

C. Aplikasi Web Customer
   1. Dashboard
   2. Modul Transaksi
   3. Modul Invoice
   4. Modul Laporan

D. Aplikasi Web Agent
   1. Dashboard
   2. Modul Transaksi
   3. Modul Invoice
   4. Modul Laporan
   