# Implementasi Uniform Cost Search (UCS) dengan Laravel

Aplikasi web kecil yang mencari jalur dengan biaya total terkecil pada graf berbobot memakai algoritma **Uniform Cost Search**.

## Cara kerja UCS
1. Masukkan node awal ke frontier dengan biaya 0.
2. Ambil node di frontier dengan biaya total g(n) paling kecil.
3. Jika node itu tujuan, selesai. Jalur ini pasti yang termurah.
4. Jika bukan, tandai sebagai sudah dieksplorasi, lalu masukkan tetangganya ke frontier dengan biaya `g(n) + biaya sisi`.
5. Ulangi sampai tujuan ditemukan atau frontier kosong (tidak ada jalur).

Syarat: biaya setiap sisi harus > 0. Uji tujuan dilakukan saat node diekspansi, bukan saat dimasukkan ke frontier, supaya hasilnya optimal.

## Struktur file
| File | Fungsi |
|---|---|
| `app/Services/UniformCostSearch.php` | Algoritma UCS |
| `app/Http/Controllers/UcsController.php` | Validasi input dan parsing graf |
| `routes/web.php` | Route `/` |
| `resources/views/ucs/index.blade.php` | Form dan tabel langkah |
| `tests/Unit/UniformCostSearchTest.php` | Unit test |

## Instalasi
```bash
composer create-project laravel/laravel ucs-laravel
cd ucs-laravel
```
Salin semua folder dari proyek ini ke dalam `ucs-laravel` (timpa `routes/web.php`), lalu:
```bash
php artisan serve
```
Buka http://127.0.0.1:8000. Jalankan test dengan `php artisan test`.

## Contoh
Jember → Surabaya pada graf bawaan menghasilkan
`Jember → Lumajang → Probolinggo → Pasuruan → Surabaya`, total 225.
