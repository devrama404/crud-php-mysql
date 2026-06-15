# 🚀 Dockerized PHP-MySQL CRUD with Jenkins CI/CD Pipeline

![Docker](https://img.shields.io/badge/Docker-2CA5E0?style=for-the-badge&logo=docker&logoColor=white)
![Jenkins](https://img.shields.io/badge/Jenkins-D24939?style=for-the-badge&logo=Jenkins&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Nginx](https://img.shields.io/badge/Nginx-009639?style=for-the-badge&logo=nginx&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)

Proyek ini mendemonstrasikan implementasi **Kontainerisasi (Dockerization)** pada aplikasi CRUD berbasis PHP-MySQL menggunakan arsitektur *multi-container* (PHP-FPM, Nginx, MySQL) dan otomatisasi *deployment* secara terintegrasi penuh menggunakan **Jenkins CI/CD Pipeline** melalui integrasi **GitHub Webhook**.

---

## 📑 Daftar Isi
1. [Arsitektur Proyek](#-arsitektur-proyek)
2. [Prasyarat (Prerequisites)](#%EF%B8%8F-prasyarat-prerequisites)
3. [Struktur Direktori](#-struktur-direktori)
4. [Panduan Instalasi & Deployment](#-panduan-instalasi--deployment)
5. [Konfigurasi CI/CD & Webhook](#-konfigurasi-cicd--webhook)
6. [Alur Pipeline Jenkins](#-alur-pipeline-jenkins)

---

## 🏗️ Arsitektur Proyek

Proyek ini dipisahkan menjadi beberapa servis yang terisolasi untuk skalabilitas dan keamanan:
* **Web Server:** Nginx (Reverse Proxy)
* **Application:** PHP 8.2 FPM (berjalan sebagai non-root user `www-data`)
* **Database:** MySQL 8.0 (dengan inisialisasi skema otomatis)
* **CI/CD Server:** Jenkins (Berjalan dengan metode *Docker-outside-of-Docker* / DooD)

---

## 🛠️ Prasyarat (Prerequisites)

Sebelum memulai, pastikan sistem Anda telah memiliki:
* [Docker](https://docs.docker.com/get-docker/) terinstal.
* [Docker Compose](https://docs.docker.com/compose/install/) terinstal.
* Akun [GitHub](https://github.com/).
* Akun [Docker Hub](https://hub.docker.com/).

---

## 📂 Struktur Direktori

```text
.
├── nginx/
│   └── nginx.conf            # Konfigurasi Reverse Proxy Nginx
├── src/                      # Source code aplikasi PHP (opsional, sesuaikan dengan repo Anda)
├── database.sql              # Skema awal database untuk auto-import MySQL
├── docker-compose.yml        # Orkestrasi Multi-Container
├── Dockerfile                # Multi-stage build untuk image PHP-FPM
└── Jenkinsfile               # Declarative Pipeline CI/CD Script

```

---

## 🚀 Panduan Instalasi & Deployment

### 1. Persiapan Repositori

Lakukan *Fork* repositori ini, kemudian *clone* ke environment lokal/server Anda:

```bash
git clone [https://github.com/](https://github.com/)<username-anda>/crud-php-mysql.git
cd crud-php-mysql

```

### 2. Menjalankan Server Jenkins (DooD Setup)

Jalankan perintah berikut untuk membuat kontainer Jenkins yang memiliki akses ke daemon Docker Host:

```bash
docker run -d \
  --name jenkins-server \
  -p 8082:8080 \
  -p 50000:50000 \
  -v jenkins_home:/var/jenkins_home \
  -v /var/run/docker.sock:/var/run/docker.sock \
  -v $(which docker):/usr/bin/docker \
  --user root \
  --restart unless-stopped \
  jenkins/jenkins:lts

```

> **Catatan:** Port Jenkins dialokasikan ke `8082` agar tidak bentrok dengan port `8080` yang digunakan oleh aplikasi utama.

**Unlock Jenkins:**

1. Cek log kontainer untuk mendapatkan password admin awal:
```bash
docker logs jenkins-server

```


2. Akses `http://localhost:8082` (atau IP server), masukkan password, lalu selesaikan instalasi *Suggested Plugins*.

---

## ⚙️ Konfigurasi CI/CD & Webhook

### 1. Konfigurasi Kredensial di Jenkins

Agar Jenkins dapat melakukan *push* image ke Docker Hub, simpan kredensial Anda:

1. Navigasi ke: **Manage Jenkins** > **Credentials** > **System** > **Global credentials**.
2. Klik **Add Credentials** > Pilih **Username with password**.
3. Masukkan data Docker Hub Anda.
4. Set **ID** menjadi: `dockerhub-credentials-id` (harus sama dengan `REGISTRY_CRED` di dalam `Jenkinsfile`).

### 2. Install Plugin yang Dibutuhkan

Buka **Manage Jenkins** > **Plugins** > **Available Plugins** dan instal:

* `Docker Pipeline`
* `Credentials Binding`

### 3. Membuat Pipeline

1. Buat **New Item** > Pilih **Pipeline** > Beri nama proyek.
2. Centang **GitHub hook trigger for GITScm polling**.
3. Di bagian Pipeline, pilih **Pipeline script from SCM**.
4. Set SCM ke **Git**, masukkan URL repositori fork Anda, dan set branch ke `*/main` atau `*/master`.

### 4. Setup GitHub Webhook

1. Buka Repositori GitHub Anda > **Settings** > **Webhooks** > **Add webhook**.
2. **Payload URL:** `http://<IP_PUBLIK_SERVER_JENKINS>:8082/github-webhook/`
3. **Content type:** `application/json`
4. Simpan konfigurasi.

---

## 🔄 Alur Pipeline Jenkins

Ketika terjadi *push* ke repositori (via Webhook), Jenkins otomatis menjalankan tahap berikut (didefinisikan di `Jenkinsfile`):

1. **Checkout:** Mengambil kode terbaru dari GitHub.
2. **Lint Check:** Memvalidasi sintaks PHP.
3. **Build Docker Image:** Membangun image menggunakan `Dockerfile` (Multi-stage build).
4. **Push Image:** Mengunggah image ke Docker Hub (tagging spesifik & latest).
5. **Deploy Application:** Menghentikan kontainer lama dan merestart menggunakan versi terbaru via `docker-compose`.
6. **Post (Clean Up):** Membersihkan *dangling images* untuk menghemat penyimpanan.

---

## 🧪 Validasi Akhir

Setelah pipeline berhasil berjalan:

* Aplikasi CRUD dapat diakses di: `http://localhost:8080`
* Database berjalan di port `3306`

Untuk memicu otomatisasi, lakukan perubahan pada file dan jalankan:

```bash
git add .
git commit -m "Feat: Implementasi Docker multi-container dan otomatisasi Jenkinsfile CI/CD"
git push origin main

```
