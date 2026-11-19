# CargoTrace — System Architecture & Engineering Guide

## 🏗 High-Level Architecture Overview

CargoTrace follows modern enterprise software design principles:
1. **Decoupled Architecture:** HTTP Controllers remain lean and delegate business logic to dedicated domain services (`app/Services`).
2. **Deterministic Domain Rules:** Business logic (such as status transitions and ETA calculations) is isolated and pure, making it directly testable via unit and feature tests.
3. **Resilient Integration:** Outbound calls to external geocoding and weather APIs use the Laravel HTTP Client with configurable timeouts and graceful degradation fallbacks.
4. **Security by Design:** Mass assignment protection, password hashing with bcrypt, CSRF protection, and server-side role validation.

```
┌────────────────────────────────────────────────────────┐
│                     Client Tier                        │
│   Web Browser (Blade + Tailwind + Leaflet + Chart.js)  │
│   Mobile App / Third-Party REST Consumer (JSON)        │
└──────────────────────────┬─────────────────────────────┘
                           │ HTTP / HTTPS
                           ▼
┌────────────────────────────────────────────────────────┐
│                   Transport Tier                       │
│   Route Handlers (routes/web.php, routes/api.php)      │
│   Middleware: Authenticate, RoleMiddleware, Sanctum   │
│   Controllers: Lean request validation and formatting  │
└──────────────────────────┬─────────────────────────────┘
                           │ Dependency Injection
                           ▼
┌────────────────────────────────────────────────────────┐
│                    Service Tier                        │
│   ShipmentService  ── Lifecycle state machine          │
│   TrackingService  ── Append-only timeline validation  │
│   EtaService       ── Deterministic delay analysis     │
│   ExceptionService ── Operational disruption triage    │
│   ExternalApiService─ Weather & geocoding integration  │
│   AuditService     ── Immutable audit logging          │
└──────────────────────────┬─────────────────────────────┘
                           │ Eloquent ORM
                           ▼
┌────────────────────────────────────────────────────────┐
│                  Persistence Tier                      │
│   MySQL 8.0+ Database / SQLite 3 Local Database        │
│   (Tables, Foreign Keys, Composite Indexes)            │
└────────────────────────────────────────────────────────┘
```

---

## 🎯 Code Ownership Interview — Key Technical Answers

When your lecturer evaluates your codebase during the UAS code interview, here are the core concepts and design decisions implemented in this repository:

### 1. "Why is the Controller not handling status transitions directly?"
- **Answer:** We adhere to the **Single Responsibility Principle (SRP)** and **Service Layer Architecture** (Slide 15). Placing lifecycle validation in `ShipmentService` ensures the identical business rules apply whether the request arrives from the Blade web interface, a REST API endpoint, or an automated console job.

### 2. "How do you prevent a shipment from skipping states or reverting from DELIVERED?"
- **Answer:** In `ShipmentService::transitionStatus()`, we define a strict transition map (`ALLOWED_TRANSITIONS`). If the current status is `DELIVERED`, it is identified as a terminal status and cannot move backwards. If an illegal transition is requested (e.g. `CREATED` $\to$ `IN_TRANSIT`), an `InvalidArgumentException` is thrown before touching the database.

### 3. "How do you ensure tracking events are not backdated or manipulated?"
- **Answer:** In `TrackingService::addEvent()`, we compare the incoming `event_time` against the timestamp of the latest recorded event for that shipment. If the new event timestamp precedes existing history, the system rejects it, maintaining an immutable, append-only chronological timeline.

### 4. "How is Customer data isolation enforced?"
- **Answer:** Authorization is checked on the server, not just hidden in the UI. In `ShipmentWebController` and `ShipmentApiController`, we check the user's role:
  - If `role === CUSTOMER`, the query automatically scopes to `WHERE customer_id = Auth::id()`.
  - If a customer attempts to query another user's shipment ID directly, the server returns an explicit `HTTP 403 Forbidden`.

### 5. "How does the system calculate ETA delays?"
- **Answer:** In `EtaService::recalculateDelay()`, the system calculates `delay_minutes = max(0, estimated_arrival - expected_arrival)`. If `delay_minutes > 0`, `is_delayed` becomes `true`. The severity (`LOW`, `MEDIUM`, `HIGH`) is categorized based on the threshold configured in `.env` (`CARGOTRACE_DELAY_THRESHOLD_HOURS`).
