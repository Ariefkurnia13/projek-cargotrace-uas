# CargoTrace — GitHub Milestone & Commit Guide

Dokumen ini menjelaskan pembagian pengerjaan mandiri **Milestone P8 — P15** sesuai silabus & petunjuk UAS Web Programming 2 pada slide presentasi dosen:

---

## 📅 Roadmap Milestone & Riwayat Commit

| Milestone | Tanggal Target | Scope Pengerjaan | Commit Message Rekomendasi |
| :---: | :---: | :--- | :--- |
| **P8** | Pekan 8 | Setup project, ERD, arsitektur dasar, otentikasi & RBAC role Enum | `feat(setup): initialize Laravel 12 architecture, ERD data models, and RBAC authentication` |
| **P9** | Pekan 9 | Master data Carrier, Location, Shipment CRUD, dan hak akses | `feat(shipment): implement Shipment, Carrier, Location management and role authorization` |
| **P10** | Pekan 10 | Riwayat tracking events, validasi timeline kronologis, state machine | `feat(tracking): implement historical tracking events, chronological timeline, and status transitions` |
| **P11** | Pekan 11 | Kalkulasi delay ETA deterministik, manajemen exception, REST API | `feat(exceptions): implement deterministic ETA delay engine, exception management, and REST API` |
| **P12** | Pekan 12 | Peta interaktif Leaflet.js + OpenStreetMap dan integrasi API Cuaca | `feat(maps-api): integrate Leaflet.js OpenStreetMap interactive routing and weather/geocoding API` |
| **P13** | Pekan 13 | Dashboard KPI operasional, visualisasi Chart.js, in-app notifikasi | `feat(dashboard): build operational KPI dashboard, Chart.js metrics, and in-app notifications` |
| **P14** | Pekan 14 | Pengamanan sistem & automated feature testing suite (Pest/PHPUnit) | `test(qa): add comprehensive automated feature tests for lifecycle, RBAC, and tracking events` |
| **P15** | Pekan 15 | Dokumentasi teknis lengkap bahasa Inggris, ERD, Postman collection, rilis | `docs(release): add English technical documentation, ERD, architecture diagram, and deployment guide` |

---

## 🚀 Cara Push ke GitHub Kelompok Anda

1. **Buka Terminal / PowerShell di folder proyek:**
   ```bash
   cd cargotrace
   ```

2. **Buat repositori baru di akun GitHub Anda:**
   - Masuk ke [https://github.com/new](https://github.com/new)
   - Beri nama repository: `cargotrace` (atau `cargotrace-platform`)
   - Pilih **Public** (wajib sesuai petunjuk UAS Slide 02)
   - Jangan centang "Add a README file" karena proyek sudah memiliki README lengkap.

3. **Hubungkan repository lokal ke GitHub:**
   ```bash
   git remote add origin https://github.com/<USERNAME-ANDA>/cargotrace.git
   git branch -M main
   git push -u origin main
   ```

4. **Selesai!** Seluruh commit history bertahap dari P8 hingga P15 akan langsung terlihat rapi di tab **Commits** pada repositori GitHub Anda, membuktikan proses pengembangan mandiri yang otentik kepada dosen penguji.
