# CargoTrace — Global Shipment Visibility & Tracking Platform

[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP 8.4](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Leaflet.js](https://img.shields.io/badge/Leaflet-1.9.4-199900?style=for-the-badge&logo=leaflet&logoColor=white)](https://leafletjs.com)
[![MySQL 8+](https://img.shields.io/badge/MySQL-8.x-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)

> **Web Programming 2 — Final Project (UAS)**  
> *A production-grade, domain-driven software portfolio project demonstrating enterprise software architecture, deterministic business logic, RESTful API contracts, and real-time shipment visibility.*

---

## 📌 Executive Summary & Problem Domain

International logistics companies and industrial consignees face significant visibility gaps:
- **Disparate data silos:** Cargo status updates are fragmented across carrier portals, emails, and manifests.
- **Opacity of delays & exceptions:** Customers only discover delays when shipments miss SLA deadlines without root-cause clarity.
- **Lack of deterministic delay analysis:** Delays are often tracked subjectively rather than evaluated against rigorous SLA rules.
- **Operational blind spots:** Control tower operators struggle to prioritize critical shipments in transit.

### The CargoTrace Solution
**CargoTrace** is an end-to-end global shipment visibility and tracking platform. It models the entire physical lifecycle of ocean and air freight shipments:
1. **Deterministic Lifecycle State Machine:** Strict validation prevents illegal status skips (e.g., jumping from `CREATED` directly to `IN_TRANSIT`) and protects terminal states (`DELIVERED`).
2. **Historical Tracking Timeline:** An immutable, append-only chronological log of physical transit checkpoints.
3. **Automated ETA & Delay Engine:** Compares SLA expected arrival against calculated estimated arrival with categorized severity thresholds.
4. **Exception Management Control Tower:** Classifies operational exceptions (`CUSTOMS_HOLD`, `WEATHER_DELAY`, `PORT_CONGESTION`) with severity tiers and tracking resolution workflows.
5. **Interactive Geographic Tracking:** Integrates **Leaflet.js** and **OpenStreetMap** with polyline routing, coordinates, and live ambient weather indicators via **Open-Meteo API**.
6. **Role-Based Access Control (RBAC):** Server-side authorization ensuring Customers strictly inspect their own cargo while Operators and Admins manage logistics pipelines.

---

## 🏛 Software Architecture & Clean Design Pattern

CargoTrace strictly decouples HTTP transport layers from domain business rules following the **Service Layer Pattern**:

```
Browser / REST Client (JSON / HTML)
        │
        ▼
   Routes & Middleware (auth, role:ADMIN,OPERATOR, sanctum)
        │
        ▼
   Controllers (Web & Api Controllers — Lean Request Orchestration)
        │
        ▼
   Service Layer (Domain Logic, State Transitions, Deterministic Rules)
    ├── ShipmentService      (Lifecycle state machine, updates, validations)
    ├── TrackingService      (Append-only chronological timeline, current loc)
    ├── EtaService           (Delay calculation, severity thresholds)
    ├── ExceptionService     (Exception ticketing, resolution workflow)
    ├── ExternalApiService   (Resilient HTTP client for Weather & Geocoding)
    └── AuditService         (Audit trails for compliance)
        │
        ▼
   Data Layer & Eloquent ORM (Models, Relationships, Query Scopes)
        │
        ▼
   Database (MySQL 8+ / SQLite 3)
```

---

## 👥 User Roles & Access Control Matrix

All routes, Blade actions, and REST endpoints enforce backend authorization checks (`RoleMiddleware` and Eloquent user scoping):

| Capability / Resource | Administrator | Logistics Operator | Customer |
| :--- | :---: | :---: | :---: |
| Access KPI Dashboard | ✅ Full Enterprise | ✅ Operational | ✅ Customer Scoped |
| View Shipments | ✅ All Shipments | ✅ All Shipments | ✅ Own Consignments Only |
| Create / Edit Shipments | ✅ Yes | ✅ Yes | ❌ Forbidden (403) |
| Transition Lifecycle Status | ✅ Yes | ✅ Yes | ❌ Forbidden (403) |
| Log Tracking Events | ✅ Yes | ✅ Yes | ❌ Forbidden (403) |
| File / Resolve Exceptions | ✅ Yes | ✅ Yes | ❌ Forbidden (403) |
| Manage Carriers & Locations | ✅ Full CRUD | ❌ Read Only | ❌ Read Only |
| Audit Trail Logs | ✅ Full System Audit | ❌ Forbidden (403) | ❌ Forbidden (403) |

---

## 📦 Default Demo Credentials

Pre-seeded with realistic global freight scenarios (Group A / B / C):

| Role | Email | Password | Organization / Notes |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@cargotrace.test` | `Password123!` | CargoTrace Global HQ |
| **Logistics Operator**| `operator@cargotrace.test`| `Password123!` | CargoTrace Control Tower |
| **Customer 1** | `customer1@cargotrace.test`| `Password123!` | PT Nusantara Manufacturing |
| **Customer 2** | `customer2@cargotrace.test`| `Password123!` | Indo Global Exporters |

---

## ⚙️ Business Rules & Milestone Implementation

### 1. Shipment Lifecycle Transitions
Shipment progression follows a finite state machine:
$$\text{CREATED} \longrightarrow \text{BOOKED} \longrightarrow \text{PICKED\_UP} \longrightarrow \text{IN\_TRANSIT} \longrightarrow \text{DELIVERED}$$
- **Terminal State Lock:** Once marked `DELIVERED`, a shipment cannot revert to an earlier stage.
- **Automated Timestamps:** Departure (`actual_departure`) is stamped upon entering `IN_TRANSIT`; Arrival (`actual_arrival`) is stamped upon entering `DELIVERED`.

### 2. ETA & Delay Severity Rules
- $\text{Delay Duration} = \max(0, \text{Estimated Arrival} - \text{Expected Arrival})$
- Configurable scenario threshold (`CARGOTRACE_DELAY_THRESHOLD_HOURS=12` for Group A benchmark):
  - **`NONE`**: Estimated $\le$ Expected
  - **`LOW`**: Delay $\le 6 \text{ hours}$
  - **`MEDIUM`**: $6 \text{ hours} < \text{Delay} \le 24 \text{ hours}$
  - **`HIGH`**: Delay $> 24 \text{ hours}$

### 3. Append-Only Tracking Events
- Tracking events are strictly chronological; events with backdated timestamps before the latest recorded checkpoint are rejected.
- Adding a valid tracking event dynamically updates the shipment's `current_location_id`.

---

## 🔌 RESTful API Documentation

All API endpoints return standardized JSON structures. Protected endpoints use **Laravel Sanctum** Bearer tokens.

### Authentication Endpoints
- `POST /api/register` — Register a customer account
- `POST /api/login` — Authenticate and receive API Bearer token
- `POST /api/logout` — Revoke active Bearer token
- `GET /api/user` — Retrieve authenticated user profile

### Shipment Endpoints
- `GET /api/shipments` — List shipments (filtered & paginated; scoped by role)
- `POST /api/shipments` — Create a new shipment (Operator/Admin)
- `GET /api/shipments/{id}` — Shipment details, items, timeline, and exceptions
- `PUT /api/shipments/{id}` — Update shipment master data
- `POST /api/shipments/{id}/status` — Perform validated status transition
- `DELETE /api/shipments/{id}` — Soft delete shipment (Admin only)

### Tracking & Exception Endpoints
- `GET /api/shipments/{id}/tracking` — Chronological timeline of tracking events
- `POST /api/shipments/{id}/tracking` — Append new tracking event (Operator/Admin)
- `GET /api/shipments/{id}/exceptions` — List exceptions tied to shipment
- `POST /api/shipments/{id}/exceptions` — Log new exception (Operator/Admin)
- `PATCH /api/exceptions/{id}` — Update exception status (`OPEN`, `INVESTIGATING`, `RESOLVED`)

*(A complete, importable Postman collection is available at `docs/CargoTrace_Postman_Collection.json`)*.

---

## 🚀 Local Installation & Quickstart

### Prerequisites
- **PHP 8.2+ or 8.4+** with extensions: `pdo`, `pdo_mysql` / `pdo_sqlite`, `curl`, `mbstring`, `openssl`, `fileinfo`.
- **Composer 2.x**
- **MySQL 8+** (or SQLite for instantaneous zero-config startup)

### 1. Clone & Setup Environment
```bash
# Navigate to project directory
cd cargotrace

# Copy environment template
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 2. Configure Database
In your `.env` file, configure either MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cargotrace_db
DB_USERNAME=root
DB_PASSWORD=
```
Or use zero-config SQLite:
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```
*(If using SQLite, ensure `database/database.sqlite` file exists: `touch database/database.sqlite`)*.

### 3. Run Migrations & Seed Demo Data
```bash
php artisan migrate:fresh --seed
```

### 4. Start the Application Server
```bash
php artisan serve
```
Open your browser at **`http://127.0.0.1:8000`** and log in with any of the demo credentials above.

---

## 🧪 Automated Testing Suite

CargoTrace features automated feature test coverage verifying:
- Authentication & guest redirects
- Role-based authorization isolation (Customer vs. Operator/Admin)
- Sequential status transitions & terminal lock enforcement
- Append-only chronological tracking order

To execute the test suite:
```bash
php artisan test
# Or run PHPUnit directly
vendor/bin/phpunit
```

---

## 📚 External Services & Attribution

In compliance with the project guidelines, external components and libraries are explicitly attributed:
- **Leaflet.js (v1.9.4):** Open-source JavaScript library for mobile-friendly interactive maps ([https://leafletjs.com](https://leafletjs.com))
- **OpenStreetMap Tile Service:** Map data © [OpenStreetMap contributors](https://www.openstreetmap.org/copyright)
- **Open-Meteo Weather API:** Free Open Weather Forecast API for waypoint ambient condition visualization ([https://open-meteo.com](https://open-meteo.com))
- **Nominatim Geocoding API:** OpenStreetMap search engine for reverse geocoding ([https://nominatim.org](https://nominatim.org))
- **Chart.js (v4.4.1):** Simple yet flexible JavaScript charting library ([https://www.chartjs.org](https://www.chartjs.org))
- **Tailwind CSS:** Utility-first CSS framework ([https://tailwindcss.com](https://tailwindcss.com))

---

## 👥 Academic Project Information
- **Course:** Web Programming 2 — Final Project (UAS)
- **Architecture:** Laravel 12 + Service-Oriented Architecture + Blade + REST API
- **Scenario:** Group A (Southeast Asia $\to$ Europe, 12h Delay Threshold, Customs Hold Benchmark)
