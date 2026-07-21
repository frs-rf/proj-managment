# Prompt: Project Management Tracker (Laravel)

Bangun **Project Management Tracker** web application yang production-ready menggunakan **Laravel 13**, **PostgreSQL 18.4**, Bootstrap 5, JavaScript, AJAX, dan Chart.js.

Ikuti keputusan arsitektur di bawah **secara ketat** — jangan mengganti dengan pola lain.

---

## Keputusan arsitektur (wajib, jangan diubah)

- **Framework:** Laravel 13. Gunakan cara Laravel, bukan PDO mentah.
- **ORM:** Eloquent (bukan query builder mentah kecuali untuk reporting berat).
- **Database:** PostgreSQL 18.4. Driver `pgsql` di config, extension `pdo_pgsql` wajib. Manfaatkan fitur Postgres, bukan sekadar ganti nama: window function untuk kurva kumulatif S-Curve, CTE untuk agregasi reporting, tipe `jsonb` untuk metadata fleksibel (mis. payload notifikasi, snapshot baseline), constraint `CHECK` untuk enum status, dan index parsial untuk baris aktif (`WHERE deleted_at IS NULL`).
- **Auth:** Laravel Sanctum untuk API token + session guard untuk web. Bukan auth manual.
- **RBAC:** package `spatie/laravel-permission`. Role: `administrator`, `project_manager`, `team_member`.
- **Validasi:** Form Request classes. Jangan validasi inline di controller.
- **Otorisasi:** Policy classes per model. Middleware `role:` dan `permission:` dari Spatie.
- **Frontend:** Blade + Bootstrap 5 + Vite. DataTables server-side. Chart.js untuk grafik.
- **API:** API Resource classes untuk serialisasi. Bukan `return $model` mentah.
- **UI reference tunggal:** gaya **Jira / Linear** — rapat, padat informasi, monokrom dengan aksen tunggal, sidebar kiri collapsible, whitespace disiplin. JANGAN campur dengan Monday/Primavera.

---

## Forecast S-Curve — pakai Earned Value Management (EVM)

Spesifikasi lama tidak mendefinisikan rumus forecast. Gunakan EVM. Definisi eksplisit:

- **PV (Planned Value):** progress % rencana pada tanggal tertentu (dari baseline terkunci).
- **EV (Earned Value):** progress % aktual yang tercapai.
- **AC (Actual Cost):** biaya aktual terpakai (opsional bila budget dilacak).
- **SPI = EV / PV** (Schedule Performance Index).
- **CPI = EV / AC** (Cost Performance Index, bila AC tersedia).
- **Forecast (EAC durasi) = Total Duration / SPI** → proyeksi tanggal selesai.
- **Forecast curve** = ekstrapolasi kurva EV memakai SPI hingga 100%.

**Baseline versioning wajib.** Tabel `project_baselines` menyimpan snapshot PV terkunci (kolom `jsonb`). Bila scope berubah, buat baseline versi baru — jangan menimpa yang lama. S-Curve selalu dibandingkan terhadap baseline aktif.

**Kurva kumulatif** (PV/EV berjalan seiring waktu) dihitung dengan window function Postgres (`SUM(...) OVER (ORDER BY date)`), bukan loop di PHP. Ini alasan utama pindah ke Postgres — pakai.

## Project Health — pakai SPI, bukan selisih persen mentah

Ganti logika `actual - planned` yang naif dengan threshold SPI:

- **SPI ≥ 0.95** → On Track (hijau)
- **0.85 ≤ SPI < 0.95** → At Risk (kuning)
- **SPI < 0.85** → Delayed (merah)

Alasan: selisih persen mentah tidak sadar fase proyek. SPI menormalkan terhadap progress rencana, jadi bermakna di awal maupun akhir proyek.

---

## User roles & permission

Definisikan permission granular via Spatie, lalu assign ke role:

- **Administrator:** semua permission.
- **Project Manager:** `project.create`, `project.update`, `task.assign`, `progress.view`, `report.view`, `member.manage`.
- **Team Member:** `task.view.assigned`, `progress.update`, `attachment.upload`, `notification.view`.

---

## Modul inti

1. **Auth** — login, logout, forgot/reset password (Laravel built-in), session management, RBAC. Keamanan: bcrypt (default Laravel), CSRF (default), XSS escaping Blade, Form Request validation, secure session config.
2. **Dashboard** — KPI cards (total projects, total tasks, completed, overdue, active resources, delayed projects). S-Curve chart (PV/EV/Forecast). Health indicator berbasis SPI.
3. **Project** — field: code, name, description, PM, planned/actual start & end, budget, status (Planning/On Going/Completed/Cancelled). CRUD, detail, search, pagination, assign members, baseline management.
4. **Task** — field: code, project, name, description, assigned_to, priority (High/Medium/Low), start/due date, progress %, attachment, status (Open/In Progress/Pending/Completed/Cancelled). CRUD, update progress, upload file, history log, activity timeline, komentar.
5. **User** — field: employee_id, name, email, department, role, status. CRUD, search, reset password.
6. **Notification** — generate otomatis: task due dalam 3 hari, task overdue, deadline proyek dekat, penugasan baru, permintaan update progress. Badge di navbar. Gunakan Laravel Notifications (database channel) + scheduled command untuk cek harian.

---

## Database

Migrasi Laravel untuk: `users`, `projects`, `tasks`, `project_progress`, `project_baselines`, `task_logs`, `project_members`, `project_risks`, `notifications`. Sertakan primary key, foreign key, index pada kolom filter/join, dan constraint. **Wajib:** soft deletes (`deleted_at`) pada projects, tasks, users. Timestamp `timestamptz` (timezone-aware, native Postgres). Seeder untuk data contoh + roles/permissions.

Spesifik PostgreSQL:
- Gunakan `bigIncrements`/`bigInteger` untuk PK/FK, atau UUID (`uuid` + `gen_random_uuid()`) bila ingin ID non-sekuensial — pilih salah satu konsisten.
- Enum status: gunakan constraint `CHECK` (via `$table->string()` + raw check), bukan tipe `ENUM` MySQL yang tidak ada di Postgres.
- Index parsial untuk query baris aktif: `CREATE INDEX ... WHERE deleted_at IS NULL`.
- Kolom `jsonb` (bukan `json`) untuk metadata task_logs, payload notifikasi, dan snapshot PV di `project_baselines` — `jsonb` bisa di-index dengan GIN.
- Foreign key dengan `ON DELETE` eksplisit (`cascade` atau `restrict`) sesuai relasi.

## API (Sanctum, JSON, API Resource)

`/api/auth`, `/api/projects`, `/api/tasks`, `/api/users`, `/api/progress`, `/api/reports`, `/api/notifications`. Dukung GET/POST/PUT/DELETE. Rate limiting via `throttle` middleware. Paginasi cursor untuk endpoint list besar.

## Keamanan attachment (jangan diabaikan)

Attachment adalah vektor serangan. Validasi MIME type + ekstensi whitelist, batasi ukuran, simpan di luar public root (private disk), serve via controller dengan otorisasi. Jangan simpan nama file asli langsung — generate ulang.

## UI/UX

Gaya Jira/Linear: responsive, sidebar collapsible, top navbar, breadcrumb, DataTables server-side, modal form, dark mode (satu implementasi konsisten), mobile-friendly. Skema warna monokrom + satu aksen. Disiplin whitespace, kepadatan informasi tinggi.

---

## Kualitas kode (wajib, bukan opsional)

- Feature test (Pest atau PHPUnit) untuk tiap endpoint API dan flow auth. "Production-ready" tanpa test bukan production-ready.
- PSR-12, jalankan Laravel Pint.
- Service class untuk logika EVM/S-Curve — jangan di controller.
- Audit trail via `task_logs` + soft delete.
- README: instalasi, `.env` setup, migrate+seed, deployment.

---

## Deliverables

Source code lengkap, struktur folder Laravel standar, migrasi, ERD, endpoint API, dashboard + S-Curve, seeder, panduan instalasi & deployment, middleware/policy RBAC, CRUD lengkap, test suite.

**Bangun bertahap dalam urutan ini:** (1) setup + auth + RBAC, (2) user management, (3) project + baseline, (4) task + progress, (5) service EVM + S-Curve dashboard, (6) notifikasi, (7) API + test. Selesaikan tiap tahap sebelum lanjut.
