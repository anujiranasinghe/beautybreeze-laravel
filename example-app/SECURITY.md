# Security Notes — Beauty Breeze (Laravel 12)

This file lists each item as **Name of exploit**, **What it does**, and **How we protected it (with proof)**.  
I’ve kept it simple and limited to what exists in this codebase.

---

## Scope & Environment

- Laravel 12 with Jetstream/Fortify (auth) and Sanctum (API auth).
- Admin vs customer via `is_admin` flag + middleware.
- Local-only demo routes for SQL Injection and CSRF.
- MongoDB Atlas audit/analytics logging.
- Local demo routes are registered only when `app()->environment('local')` is true.
- Production expectations (on host): `APP_ENV=production`, `APP_DEBUG=false`, serve over HTTPS.

Paths to check: `routes/web.php`, `routes/api.php`, `app/Http/Middleware/AdminMiddleware.php`, `app/Http/Middleware/RequireApiToken.php`, `app/Models/User.php`, `app/Actions/Fortify/CreateNewUser.php`, `resources/views/pages/csrf-demo.blade.php`.

---

## 1) Broken Access Control (Admin pages/APIs)

**What it does**  
Lets guests or normal users access admin-only pages/APIs.

**How we protected it (with proof)**  
- **Web admin** routes use `['auth','admin']` so only logged-in admins can access.  
  *Files:* `routes/web.php`, `app/Http/Middleware/AdminMiddleware.php`
- **API admin** routes use `['auth:sanctum','require.apitoken','admin']`.  
  *Files:* `routes/api.php`, `app/Http/Middleware/RequireApiToken.php`

**Proof (text)**
# Logged-out user visiting an admin page → redirected to login (browser)

# API: no token → 401
curl -i http://127.0.0.1:8000/api/admin/products

# API: token for non-admin → 403
curl -i -H "Authorization: Bearer <USER_TOKEN>" http://127.0.0.1:8000/api/admin/products

# API: token for admin → 200
curl -i -H "Authorization: Bearer <ADMIN_TOKEN>" http://127.0.0.1:8000/api/admin/products

---

## 2) Unauthenticated API Access

**What it does**  
Calls protected APIs without a valid Sanctum token.

**How we protected it (with proof)**  
- Sanctum is enabled; protected groups use `auth:sanctum` + `RequireApiToken` to require a real token (not just a session).  
*Files:* `routes/api.php`, `config/sanctum.php`, `app/Http/Middleware/RequireApiToken.php`

**Proof (text)**
# no token → 401
curl -i http://127.0.0.1:8000/api/orders

# with a valid token → 200 (if route allows non-admin) or 403 (if admin-only)
curl -i -H "Authorization: Bearer <ANY_VALID_TOKEN>" http://127.0.0.1:8000/api/orders

---

## 3) SQL Injection — Boolean-Based (local demo)

**What it does**  
Payloads like `email=' OR '1'='1` alter the WHERE clause and return unintended rows.

**How we protected it (with proof)**  
- Real code uses Eloquent/Query Builder (bound parameters).  
- Local demo endpoints show the difference:
  - `/vuln-sql` (intentionally vulnerable for teaching)  
  - `/safe-sql` (validates input and uses bindings)  
  *File:* `routes/web.php` (inside a local-only block)

**Proof (text)**
# vulnerable demo (many rows may be returned)
http://127.0.0.1:8000/vuln-sql?email=' OR '1'='1

# safe demo (should reject or return no leak)
http://127.0.0.1:8000/safe-sql?email=' OR '1'='1
# Expect 422 (invalid email) or 0 safe results


---

## 4) SQL Injection — UNION-Based (local demo)

**What it does**  
Appends `UNION SELECT ...` to pull extra data from other columns/tables.

**How we protected it (with proof)**  
- Same approach: parameter binding + validation.  
- Local demo endpoint shows vulnerable vs safe behavior.  
*File:* `routes/web.php`

**Proof (text)**
# vulnerable demo (UNION on one line)
http://127.0.0.1:8000/vuln-sql?email=' UNION SELECT id,name,email FROM users --

# safe demo (should reject or return no leak)
http://127.0.0.1:8000/safe-sql?email=' UNION SELECT id,name,email FROM users --
# Expect 422 or no leak


---

## 5) Mass Assignment (Privilege Escalation)

**What it does**  
If the app mass-assigns raw input, attackers can set `is_admin=1`.

**How we protected it (with proof)**  
- `User` model restricts `$fillable` and **guards** sensitive fields like `is_admin`.  
*File:* `app/Models/User.php`

**Proof (text)**
 SELECT is_admin FROM users WHERE email = 'test@example.com';
-- Expect is_admin = 0 (unchanged)

---

## 6) CSRF (Cross-Site Request Forgery)

**What it does**  
Tricks a logged-in user’s browser into sending a state-changing request (POST/PUT/DELETE) without their intention.

**How we protected it (with proof)**  
- Laravel’s CSRF middleware is active on web routes; all forms include `@csrf`.  
- Local-only verification routes exist: `GET /csrf-demo` (form) and `POST /csrf-demo/submit` (handler).  
*Files:* `routes/web.php`, `resources/views/pages/csrf-demo.blade.php`

**Proof (text)**
**Proof (text)**
```bash
# without token (blocked)
curl -i -X POST http://127.0.0.1:8000/csrf-demo/submit
# Expect: HTTP/1.1 419 Page Expired
Submit the Blade form at http://127.0.0.1:8000/csrf-demo (contains @csrf) → expect normal success flow (200/302).

---

## 7) Plain-Text Password Storage

**What it does**  
If passwords are stored in plain text, a database leak exposes user credentials immediately.

**How we protected it (with proof)**  
- Jetstream/Fortify creates users with **hashed** passwords.  
- `User` model uses the hashed cast: `protected $casts = ['password' => 'hashed'];`  
*Files:* `app/Actions/Fortify/CreateNewUser.php`, `app/Models/User.php`

**Proof (text)**
```sql
SELECT email, password FROM users WHERE email = 'test@example.com';
-- Expect: password is a bcrypt/argon hash (e.g., starts with $2y$ or $argon2id$), not readable text.


# optional Laravel Tinker check
php artisan tinker
>>> \Illuminate\Support\Facades\Hash::needsRehash(\App\Models\User::first()->password)
# Expect: false (already a secure hash)
