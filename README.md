# ☕ CafeIn

### Web-Based Café Ordering & Management System

![Laravel](https://img.shields.io/badge/Laravel-10.x-red)
![PHP](https://img.shields.io/badge/PHP-8.x-blue)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Supabase-316192)
![Railway](https://img.shields.io/badge/Hosted%20on-Railway-purple)
![Status](https://img.shields.io/badge/Status-Production-success)
![License](https://img.shields.io/badge/License-Academic%20%26%20Portfolio-lightgrey)

---

CafeIn is a **cloud-deployed web-based café ordering and management system** built to streamline customer ordering, cashier workflows, and administrative control in a single platform.

🚀 **Live Production Website**
🔗 **[https://cafein.up.railway.app](https://cafein.up.railway.app)**

---

## 🌐 Hosting & Infrastructure

CafeIn is deployed using a **modern cloud-native architecture**:

* **Hosting Platform:** Railway
* **Database & Storage:** Supabase (PostgreSQL)
* **Backend Framework:** Laravel (PHP)

This infrastructure ensures scalability, clean separation of concerns, and production reliability.

---

## 🧱 System Architecture (Visual Diagram)

### 🔹 High-Level Architecture Flow

```
┌───────────────┐
│   Web Browser │
│ (User/Admin)  │
└───────┬───────┘
        │ HTTP / HTTPS
        ▼
┌─────────────────────┐
│   Laravel Backend   │
│  (CafeIn Web App)   │
│  - Auth             │
│  - Roles            │
│  - Business Logic   │
└───────┬─────────────┘
        │ Database Connection
        ▼
┌─────────────────────┐
│ Supabase PostgreSQL │
│  - Users            │
│  - Orders           │
│  - Menus            │
│  - Transactions     │
└─────────────────────┘
        ▲
        │ Managed Deployment
┌───────┴─────────────┐
│      Railway        │
│  - Hosting          │
│  - Build Pipeline   │
│  - Environment Vars│
└─────────────────────┘
```

---

### 🔹 Architecture Explanation

| Layer    | Description                                                              |
| -------- | ------------------------------------------------------------------------ |
| Client   | Web browser (Customer, Cashier, Admin)                                   |
| Backend  | Laravel handles routing, authentication, role access, and business logic |
| Database | Supabase PostgreSQL stores application data                              |
| Hosting  | Railway manages deployment, builds, and runtime environment              |

---

## 🎯 Project Objectives

* Digitize café ordering workflows
* Reduce cashier operational complexity
* Centralize menu and user management
* Deliver a clean and scalable web solution

---

## ✨ Core Features

### 👤 Customer

* Register & login
* Browse menu
* Place online orders
* Track order status

### 💼 Cashier

* View incoming orders
* Update order status
* Manage transactions

### 🛠 Administrator

* Menu management (CRUD)
* User & role management
* System monitoring

---

## 👥 User Roles

| Role    | Access Level                   |
| ------- | ------------------------------ |
| Admin   | Full system control            |
| Cashier | Order & transaction management |
| User    | Menu browsing & ordering       |

---

## 🛠 Technology Stack

* **Backend:** Laravel
* **Language:** PHP 8.x
* **Database:** PostgreSQL (Supabase)
* **Hosting:** Railway
* **Frontend:** Blade Template Engine
* **Version Control:** Git & GitHub

---

## ⚙️ Environment Configuration

```env
APP_NAME=CafeIn
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY
APP_DEBUG=false
APP_URL=https://cafein.up.railway.app

DB_CONNECTION=pgsql
DB_HOST=YOUR_SUPABASE_HOST
DB_PORT=5432
DB_DATABASE=YOUR_DATABASE_NAME
DB_USERNAME=YOUR_DATABASE_USER
DB_PASSWORD=YOUR_DATABASE_PASSWORD
```

---

## 📦 Local Development

```bash
git clone https://github.com/slabkim/CafeIn.git
cd CafeIn
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

---

## 📁 Project Structure

```
CafeIn/
├── app/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
└── README.md
```

---

## 📈 Project Status

✅ Production deployed
✅ Cloud database integrated
✅ Multi-role system implemented
✅ Portfolio-ready

---

## 📄 License

This project is developed for **academic and portfolio purposes**.
Forking is allowed with proper attribution.

---
