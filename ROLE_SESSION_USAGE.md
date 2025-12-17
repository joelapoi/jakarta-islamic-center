# Panduan Penggunaan Role Session Storage

Role pengguna sekarang disimpan dalam session storage dan dapat diakses dari mana saja dalam aplikasi.

## Cara Menggunakan

### 1. Mendapatkan Semua Role User

```php
use App\Helpers\RoleHelper;

// Menggunakan helper
$roles = RoleHelper::getRoles();
// Contoh output: ['admin', 'staff']

// Atau menggunakan alias
$roles = \RoleHelper::getRoles();
```

### 2. Cek Apakah User Memiliki Role Tertentu

```php
// Cek satu role
if (\RoleHelper::hasRole('admin')) {
    // User adalah admin
}

// Cek jika user memiliki salah satu dari beberapa role
if (\RoleHelper::hasAnyRole(['admin', 'supervisor'])) {
    // User adalah admin atau supervisor
}

// Cek jika user memiliki semua role
if (\RoleHelper::hasAllRoles(['admin', 'staff'])) {
    // User adalah admin dan staff
}
```

### 3. Mendapatkan User ID dari Session

```php
$userId = \RoleHelper::getUserId();
```

## Contoh Penggunaan di Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\AnggaranKegiatan;

class AnggaranController extends Controller
{
    public function index()
    {
        // Cek apakah user adalah admin
        if (!\RoleHelper::hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke halaman ini'
            ], 403);
        }

        $anggaran = AnggaranKegiatan::all();
        
        return response()->json([
            'success' => true,
            'data' => $anggaran,
            'user_roles' => \RoleHelper::getRoles()
        ]);
    }

    public function store()
    {
        // Cek apakah user memiliki permission untuk membuat anggaran
        if (!\RoleHelper::hasAnyRole(['admin', 'staff'])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki permission untuk membuat anggaran'
            ], 403);
        }

        // Lakukan proses pembuatan anggaran
        $anggaran = AnggaranKegiatan::create([
            'created_by' => \RoleHelper::getUserId(),
            // ... field lainnya
        ]);

        return response()->json([
            'success' => true,
            'data' => $anggaran
        ]);
    }
}
```

## Contoh Penggunaan di Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next)
    {
        if (!\RoleHelper::hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required'
            ], 403);
        }

        return $next($request);
    }
}
```

## Contoh Penggunaan di Service

```php
<?php

namespace App\Services;

use RoleHelper;

class ReportService
{
    public function generateReport()
    {
        if (\RoleHelper::hasRole('admin')) {
            // Generate full report untuk admin
            return $this->generateFullReport();
        } elseif (\RoleHelper::hasRole('staff')) {
            // Generate limited report untuk staff
            return $this->generateLimitedReport();
        }

        return null;
    }
}
```

## Flow Saat Login

1. User melakukan login dengan email dan password
2. AuthService memvalidasi credentials
3. JWT token dibuat
4. **Role disimpan ke session** dengan key `user_roles`
5. **User ID disimpan ke session** dengan key `user_id`
6. Token dan user data dikembalikan ke client

## Flow Saat Logout

1. JWT token di-invalidate
2. **Session roles dihapus**
3. **User ID dihapus dari session**
4. Client di-redirect ke login page

## Flow Saat Refresh Token

1. Token di-refresh
2. Session roles tetap tersimpan (tidak berubah)
3. Token baru dikembalikan

## Testing

Untuk testing, Anda bisa menggunakan session mock:

```php
use Illuminate\Support\Facades\Session;

// Test case
public function testUserCanAccessAdminPage()
{
    Session::put('user_roles', ['admin']);
    Session::put('user_id', 1);

    // Test logic...
}
```

---

**Catatan:** Pastikan session sudah dikonfigurasi dengan benar di `.env` dan `config/session.php`
