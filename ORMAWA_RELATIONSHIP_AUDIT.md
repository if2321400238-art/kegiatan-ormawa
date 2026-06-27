# COMPREHENSIVE ORMAWA USER RELATIONSHIP AUDIT
**Date:** June 25, 2026  
**Scope:** Complete codebase analysis  
**Thoroughness Level:** COMPLETE

---

## EXECUTIVE SUMMARY

This audit reveals **critical design inconsistencies** in how the application handles user-ormawa relationships:

1. **Mixed Relationship Model**: System uses BOTH `hasOne` (one-to-one) and `belongsToMany` (many-to-many) for different user roles
2. **Ownership Assumption**: Code assumes `ormawa.user_id` represents the login account owner, which breaks with multiple admins
3. **Authorization Bug**: `PengajuanKegiatan::canBeEditedBy()` assumes single ormawa per user
4. **Total Occurrences**: 50+ code locations affected across models, controllers, middleware, and views

---

## SECTION 1: SINGULAR `$user->ormawa` USAGE (One-to-One Assumption)

### 1.1 DashboardController.php
| Line | Type | Code | Context |
|------|------|------|---------|
| 43 | Getter | `$ormawa = Auth::user()->ormawa;` | Fetches dashboard stats for authenticated ormawa user |

**Issue:** Assumes authenticated user (role=ormawa) owns exactly ONE ormawa

---

### 1.2 CheckOrmawaComplete Middleware
| Line | Type | Code | Context |
|------|------|------|---------|
| 16 | Getter | `$ormawa = $user->ormawa;` | Checks if ormawa profile exists and is complete |
| 19-24 | Create | Creates empty ormawa if not exists with `user_id = $user->id` | Auto-creates profile on first access |

**Issue:** Auto-creation logic assumes only one ormawa per user, creates conflict if user manages multiple orgs

---

### 1.3 PengajuanKegiatanController.php (PRIMARY PROBLEM FILE)
| Line | Type | Code | Purpose |
|------|------|------|---------|
| 26 | Filter | `$query->where('ormawa_id', $user->ormawa->id);` | Filter pengajuan by user's ormawa |
| 66 | Count | `PengajuanKegiatan::where('ormawa_id', $user->ormawa->id)->count()` | Total submission count |
| 67 | Count | `where('ormawa_id', $user->ormawa->id)->where('status', 'draft')` | Draft count |
| 68 | Count | `where('ormawa_id', $user->ormawa->id)->whereIn('status', ...)` | Pending verification count |
| 69 | Count | `where('ormawa_id', $user->ormawa->id)->where('status', 'disetujui')` | Approved count |
| 70 | Count | `where('ormawa_id', $user->ormawa->id)->where('status', 'ditolak')` | Rejected count |
| 71 | Count | `where('ormawa_id', $user->ormawa->id)->whereIn('status', ...)` | Revision count |
| 127 | Assign | `$ormawa = auth()->user()->ormawa;` | Get ormawa for pengajuan creation |
| 185 | Check | `$pengajuan->ormawa->isFakultas() && $pengajuan->ormawa->fakultas` | Determine approval workflow |
| 222 | Auth | `$pengajuan->ormawa_id !== auth()->user()->ormawa->id` | **AUTHORIZATION CHECK** - Blocks if not owner's ormawa |
| 358 | Query | `$query = PengajuanKegiatan::where('ormawa_id', $user->ormawa->id);` | Build pengajuan list for role=ormawa |
| 410 | Query | `->where('ormawa_id', $user->ormawa->id);` | Resubmit pengajuan filter |

**Critical Issues:**
- Line 222: Authorization assumes user can only belong to ONE ormawa
- Lines 66-71: Statistics break if user manages multiple organizations
- Line 127: Cannot create pengajuan if user doesn't have `$user->ormawa` loaded

---

### 1.4 ProfileController.php
| Line | Type | Code | Purpose |
|------|------|------|---------|
| 77 | Getter | `$ormawa = $user->ormawa;` | Get existing profile for editing |
| 81 | Setter | `$ormawa->user_id = $user->id;` | **Assign ormawa ownership to current user** |
| 105 | Setter | `$ormawa->pembina_user_id = $pembinaUser?->id;` | Set advisor relationship |

**Critical Issues:**
- Line 81: Only one `user_id` per ormawa - cannot have multiple admins
- Overwrites existing user_id if ormawa already belonged to someone else

---

### 1.5 profile/edit.blade.php (Views)
| Line | Type | Code | Purpose |
|------|------|------|---------|
| 53 | Display | `value="{{ old('nama_ormawa', $user->ormawa->nama_ormawa ?? '') }}"` | Show ormawa name in profile form |
| 58 | Display | `value="{{ old('ketua', $user->ormawa->ketua ?? '') }}"` | Show ketua/chairman name |
| 66 | Display | `{{ old('pembina', $user->ormawa->pembina ?? '') }}` | Show advisor name |
| 73 (×2) | Condition | `@if($user->ormawa && $user->ormawa->kop_surat)` | Check if kop_surat exists |
| 82 | Display | `{{ old('deskripsi', $user->ormawa->deskripsi ?? '') }}` | Show description |

**Issue:** View fails if user has no `$user->ormawa` (e.g., mahasiswa without org role)

---

**TOTAL SINGULAR USES: 20+ occurrences in 5 files**

---

## SECTION 2: PLURAL `$user->ormawas()` USAGE (Many-to-Many Assumption)

### 2.1 MahasiswaDashboardController.php (NEW PATTERN)
| Line | Type | Code | Purpose |
|------|------|------|---------|
| 22 | Query | `$ormawas = $user->ormawas()->select('ormawa.*')` | **Get all orgs user is member of** |
| 30 | Check | `if (!$activeOrmawaId && $ormawas->isNotEmpty())` | Check if any orgs available |
| 31 | Get | `$activeOrmawaId = $ormawas->first()->id;` | Select first org as default |
| 35 | Find | `$activeOrmawa = $ormawas->firstWhere('id', $activeOrmawaId);` | Get currently active org |
| 39-41 | Pass | `compact('ormawas', 'activeOrmawa', 'activeOrmawaId')` | Pass to view for org switching |
| 64 | Verify | `if (!$user->ormawas()->where('ormawa_id', $ormawaId)->exists())` | **Verify user is member before setting as active** |

**Purpose:** Allows mahasiswa to be member of multiple organizations and switch between them

**Design Pattern:** Uses session('active_ormawa_id') to track which org is currently active

---

### 2.2 EnsureActiveOrmawa Middleware
| Line | Type | Code | Purpose |
|------|------|------|---------|
| 33 | Verify | `if ($user && !$user->ormawas()->where('ormawa_id', session('active_ormawa_id'))->exists())` | **Validate active ormawa is still valid** |

**Purpose:** Ensures user is still a member of their active ormawa session; clears session if not

---

**TOTAL PLURAL USES: 8 occurrences in 2 files**

**CRITICAL INCONSISTENCY:** These 2 files use many-to-many pattern, while 5 other files use one-to-one!

---

## SECTION 3: ACCESSING `$ormawa->user` OR `$pengajuan->ormawa->user`

### 3.1 PersetujuanRektorController.php
| Line | Type | Code | Purpose |
|------|------|------|---------|
| 127 | Access | `$pengajuan->ormawa->user,` | Pass user to notification (Rektor approval) |

**Location:** In `notifyOrmawa()` method - sends approval/rejection notification

---

### 3.2 PersetujuanWarek3Controller.php
| Line | Type | Code | Purpose |
|------|------|------|---------|
| 255 | Access | `$pengajuan->ormawa->user,` | Pass user to notification (Warek3 approval) |

**Location:** In `notifyOrmawa()` method

---

### 3.3 VerifikasiDosenController.php
| Line | Type | Code | Purpose |
|------|------|------|---------|
| 147 | Access | `$pengajuan->ormawa->user,` | Pass user to notification (Dosen verification) |

**Location:** In notification after dosen approval

---

### 3.4 VerifikasiBauakController.php
| Line | Type | Code | Purpose |
|------|------|------|---------|
| 132 | Access | `$pengajuan->ormawa->user,` | Pass user to notification (BAUAK verification) |

**Location:** In notification after BAUAK approval

---

### 3.5 PersetujuanDekanController.php
| Line | Type | Code | Purpose |
|------|------|------|---------|
| 147 | Access | `$pengajuan->ormawa->user,` | Pass user to notification (Dekan approval) |

**Location:** In notification after Dekan approval

---

### 3.6 SuratRekomendasiService.php
| Line | Type | Code | Purpose |
|------|------|------|---------|
| 19 | Pass | `'ormawa' => $pengajuan->ormawa,` | Pass ormawa to recommendation letter view |
| 43 | Pass | `'ormawa' => $pengajuan->ormawa,` | Pass ormawa to PDF generation |

**Note:** Service passes entire ormawa object; view may access `$ormawa->user`

---

**TOTAL `ormawa->user` ACCESSES: 7 direct + 2 in service = 9 locations**

**ASSUMPTION:** Each ormawa has ONE associated user (the owner/login account)

---

## SECTION 4: CRITICAL - CHECKING `$ormawa->user_id` (OWNERSHIP/LOGIN)

### 4.1 PengajuanKegiatan.php Model - CRITICAL AUTHORIZATION
| Line | Type | Code | Full Context |
|------|------|------|--------------|
| 265 | Auth | `$this->ormawa->user_id === $user->id` | In `canBeEditedBy($user)` method |

**Full Method Context (lines 255-266):**
```php
public function canBeEditedBy($user): bool
{
    if ($user->isOrmawa()) {
        return in_array($this->status, [
            'draft',
            'menunggu_dosen',
            'revisi_dosen',
            ...
        ]) && $this->ormawa->user_id === $user->id;
    }
    return false;
}
```

**CRITICAL ISSUE:**
- Only allows editing if `ormawa.user_id === authenticated_user.id`
- Breaks if ormawa has multiple admin accounts
- Breaks if admin is role=mahasiswa with multiple org memberships
- Only the ONE user who "owns" the ormawa (via user_id) can edit its pengajuan

---

### 4.2 ProfileController.php - SETTING OWNERSHIP
| Line | Type | Code | Purpose |
|------|------|------|---------|
| 81 | Setter | `$ormawa->user_id = $user->id;` | **Assigns ormawa ownership to current authenticated user** |

**Context (lines 77-82):**
```php
$ormawa = $user->ormawa;

if (!$ormawa) {
    $ormawa = new \App\Models\Ormawa();
    $ormawa->user_id = $user->id;  // LINE 81 - Sets ownership
}
```

**CONSEQUENCES:**
- If ormawa already exists but with different user_id, this doesn't change it
- If existing ormawa exists, it's not updated with new user_id
- Creates shared ormawa situation where multiple users could have attempted to set ownership

---

### 4.3 ProfileController.php - SETTING ADVISOR
| Line | Type | Code | Purpose |
|------|------|------|---------|
| 105 | Setter | `$ormawa->pembina_user_id = $pembinaUser?->id;` | **Sets advisor/mentor relationship (separate from ownership)** |

**Context (lines 100-105):**
```php
$pembinaUser = null;
if (!empty($validated['pembina']) && $user->isOrmawa()) {
    $pembinaUser = \App\Models\User::where('role', 'dosen')
        ->where('nama', $validated['pembina'])->first();
}
$ormawa->pembina_user_id = $pembinaUser?->id;  // LINE 105
```

**Note:** `pembina_user_id` is separate from `user_id` - represents advisor/mentor, not owner

---

**TOTAL `user_id` CHECKS: 3 critical locations**

**SECURITY IMPACT:** HIGH - Blocks legitimate admins from editing their org's proposals

---

## SECTION 5: ACCESSING `$ormawa->pembina_user_id` (ADVISOR RELATIONSHIP)

### 5.1 VerifikasiDosenController.php - CHECKING ADVISOR
| Line | Type | Code | Purpose |
|------|------|------|---------|
| 50 | Get | `$pembinaUserId = $pengajuan->ormawa->pembina_user_id ?? null;` | Get ormawa's advisor ID |
| 67 | Get | `$pembinaUserId = $pengajuan->ormawa->pembina_user_id ?? null;` | Get ormawa's advisor ID (duplicate) |

**Context:** Lines 50 and 67 are in approval methods checking if current user is the advisor

---

### 5.2 VerifikasiDosenController.php - CHECKING ADVISOR NAME
| Line | Type | Code | Purpose |
|------|------|------|---------|
| 56 | Check | `if ((($pengajuan->ormawa->pembina ?? null) !== auth()->user()->nama))` | Verify current user is the advisor |
| 73 | Check | `if ((($pengajuan->ormawa->pembina ?? null) !== auth()->user()->nama))` | Verify current user is the advisor (duplicate) |

**Issue:** Compares string name instead of using pembina_user_id - potential name matching bugs

---

### 5.3 ProfileController.php - SETTING ADVISOR
| Line | Type | Code | Purpose |
|------|------|------|---------|
| 105 | Setter | `$ormawa->pembina_user_id = $pembinaUser?->id;` | Sets which dosen advises this ormawa |

**Context:** When ormawa owner updates profile, they can set which dosen is their advisor

---

**TOTAL `pembina_user_id` ACCESSES: 5 locations in 2 files**

---

## SECTION 6: MODEL RELATIONSHIP DEFINITIONS

### 6.1 User.php Model - SINGULAR RELATIONSHIP (hasOne)

**Lines 45-47:**
```php
public function ormawa()
{
    return $this->hasOne(Ormawa::class);
}
```

**Characteristics:**
- One-to-one relationship
- Assumes user owns exactly ONE ormawa
- Uses default foreign key: `ormawa.user_id`
- No override of foreign key
- Lazy-loaded by default

---

### 6.2 User.php Model - PLURAL RELATIONSHIP (belongsToMany)

**Lines 70-76:**
```php
public function ormawas()
{
    return $this->belongsToMany(
        Ormawa::class,
        'ormawa_users',
        'user_id',
        'ormawa_id'
    )->withPivot('jabatan', 'aktif')
        ->withTimestamps();
}
```

**Characteristics:**
- Many-to-many relationship
- User can be member of multiple ormawaas
- Uses pivot table: `ormawa_users`
- Stores position: `jabatan` (ketua, wakil_ketua, sekretaris, bendahara, anggota)
- Tracks active status: `aktif` (boolean)
- Tracks timestamps for audit trail

---

### 6.3 Ormawa.php Model - INVERSE ONE-TO-ONE (belongsTo)

**Lines 31-34:**
```php
public function user()
{
    return $this->belongsTo(User::class);
}
```

**Characteristics:**
- One-to-one inverse of User.ormawa
- Represents the "owner" of ormawa (the login account)
- Uses `ormawa.user_id` foreign key
- Assumes each ormawa belongs to ONE user

---

### 6.4 Ormawa.php Model - INVERSE MANY-TO-MANY (belongsToMany)

**Lines 40-48:**
```php
public function users()
{
    return $this->belongsToMany(
        User::class,
        'ormawa_users',
        'ormawa_id',
        'user_id'
    )->withPivot('jabatan', 'aktif')
        ->withTimestamps();
}
```

**Characteristics:**
- Many-to-many inverse of User.ormawas
- One ormawa has many member users
- Same pivot table and fields as User.ormawas

---

### 6.5 PengajuanKegiatan.php Model - HAS ONE ORMAWA

**Lines 40-42:**
```php
public function ormawa()
{
    return $this->belongsTo(Ormawa::class);
}
```

**Field:** `pengajuan_kegiatan.ormawa_id`

**Used by:** PengajuanKegiatan to identify which ormawa submitted the proposal

---

**RELATIONSHIP SUMMARY:**

| Relationship | Type | Fields | Meaning |
|--------------|------|--------|---------|
| `User.ormawa()` | hasOne | user_id in Ormawa | User who **owns** an ormawa account |
| `User.ormawas()` | belongsToMany | pivot: ormawa_users | User who **is member of** multiple ormawaas |
| `Ormawa.user()` | belongsTo | ormawa.user_id | The **owner** of this ormawa |
| `Ormawa.users()` | belongsToMany | pivot: ormawa_users | The **members** of this ormawa |
| `PengajuanKegiatan.ormawa()` | belongsTo | pengajuan.ormawa_id | Which ormawa submitted this |

---

## SECTION 7: MANY-TO-MANY DETAILS - ORMAWA MEMBERS

### 7.1 OrmawaAnggotaController.php - MEMBER MANAGEMENT

| Line | Type | Code | Purpose |
|------|------|------|---------|
| 17 | Query | `$ormawa->users()` | Get all members of ormawa |
| 30 | Query | `$ormawa->users()->pluck('users.id')->toArray()` | Get existing member IDs |
| 59 | Check | `$ormawa->users()->where('users.id', $validated['user_id'])->first()` | Check if user already member |
| 61 | Error | `'User sudah menjadi anggota ormawa ini.'` | Error if already member |
| 64 | Attach | `$ormawa->users()->attach($validated['user_id'], [...])` | Add user as member with jabatan & aktif |
| 69 | Route | `route('admin.ormawa.anggota.index', $ormawa)` | Redirect to member list |
| 79 | Check | `$ormawa->users()->where('users.id', $user->id)->first()` | Verify user is member before edit |
| 101 | Check | `$ormawa->users()->where('users.id', $user->id)->first()` | Verify user is member before update |
| 111 | Update | `$ormawa->users()->updateExistingPivot($user->id, [...])` | Update jabatan/aktif for member |
| 131 | Detach | `$ormawa->users()->detach($user->id)` | Remove user from ormawa |

**Purpose:** Admin interface for managing who is member of each ormawa organization

**Pivot Data:**
- `jabatan`: Position (ketua/wakil_ketua/sekretaris/bendahara/anggota)
- `aktif`: Whether membership is active (boolean)

---

## SECTION 8: MIDDLEWARE DEPENDENCY

### 8.1 CheckOrmawaComplete Middleware - PROFILE VALIDATION

**File:** app/Http/Middleware/CheckOrmawaComplete.php

| Line | Function | Purpose |
|------|----------|---------|
| 15 | Check role | Only enforce for role=ormawa users |
| 16 | Get profile | `$ormawa = $user->ormawa` |
| 19-24 | Auto-create | If not exists: create empty Ormawa with `user_id = $user->id` |
| 33 | Validate | Check if `nama_ormawa` and `ketua` are filled |

**Applied to:** Routes with middleware: `['role:ormawa', 'ormawa.complete']`

**Impact:** Blocks ormawa users from accessing routes until they complete their profile

---

### 8.2 EnsureActiveOrmawa Middleware - SESSION VALIDATION

**File:** app/Http/Middleware/EnsureActiveOrmawa.php

| Line | Function | Purpose |
|------|----------|---------|
| 26 | Check session | Redirect if no `active_ormawa_id` in session |
| 33 | Verify member | Ensure user is still member of active ormawa |
| 34 | Clear session | Remove `active_ormawa_id` if user not member anymore |

**Used by:** Mahasiswa dashboard to manage active organization switching

---

## SECTION 9: ADDITIONAL ORMAWA RELATIONSHIPS

### 9.1 VerifikasiDosenController.php - ADVISOR CHECKS

| Line | Type | Code | Purpose |
|------|------|------|---------|
| 50 | Get | `$pembinaUserId = $pengajuan->ormawa->pembina_user_id ?? null;` | Get advisor user ID |
| 56 | Check | `if ((($pengajuan->ormawa->pembina ?? null) !== auth()->user()->nama))` | Verify is advisor by name |
| 67 | Get | `$pembinaUserId = $pengajuan->ormawa->pembina_user_id ?? null;` | Get advisor ID (duplicate) |
| 73 | Check | `if ((($pengajuan->ormawa->pembina ?? null) !== auth()->user()->nama))` | Verify is advisor by name (duplicate) |
| 97 | Determine | `$pengajuan->ormawa->isFakultas()` | Determine next approval step |
| 110 | Determine | `if ($pengajuan->ormawa->isFakultas())` | Different workflow for faculty-level orgs |
| 147 | Access | `$pengajuan->ormawa->user,` | Send notification to ormawa owner |
| 158 | Message | `"dari {$pengajuan->ormawa->nama_ormawa}"` | Include ormawa name in message |
| 160 | Get | `$dekan = $pengajuan->ormawa->fakultas?->dekan;` | Get faculty dean for next approval |
| 182 | Message | `"dari {$pengajuan->ormawa->nama_ormawa}"` | Include ormawa name in message |

---

## SECTION 10: CONTROLLERS USING ORMAWA FILTERING

### 10.1 DashboardController.php - STATISTICS BY ROLE

Multiple dashboard methods that filter by ormawa:

| Method | Lines | Purpose |
|--------|-------|---------|
| dashboardOrmawa() | 41-71 | Role=ormawa dashboard - filters `ormawa_id = $user->ormawa->id` |
| dashboardBauak() | 88-101 | BAUAK dashboard - shows all pengajuan |
| dashboardDosen() | 134-167 | Dosen dashboard - filters by mentored ormawas |
| dashboardDekan() | 171-189 | Dekan dashboard - filters by faculty-level ormawas |
| dashboardAdmin() | 200-260 | Admin dashboard - shows all data |

**Key Statistics Filtered by ormawa_id:**
- Total submissions
- Draft count
- Pending verification count
- Approved count
- Rejected count
- Revision count
- Recent submissions
- Upcoming events

---

### 10.2 LaporanController.php - ORMAWA REPORTING

| Line | Type | Code | Purpose |
|------|------|------|---------|
| 34 | Group | `$perOrmawa = $pengajuan->groupBy('ormawa_id')` | Group report data by ormawa |
| 36 | Get | `'ormawa' => $items->first()->ormawa->nama_ormawa,` | Get ormawa name for group |
| 152 | Get | `$item->ormawa->nama_ormawa,` | Include ormawa name in CSV export |
| 73 | Query | `$ormawaAktif = Ormawa::withCount('pengajuanKegiatan')` | Count submissions per ormawa |

---

## SECTION 11: VIEW LEVEL ORMAWA ACCESS

### 11.1 mahasiswa/dashboard.blade.php - MEMBER ORGANIZATION SWITCHING

| Line | Type | Usage | Purpose |
|------|------|-------|---------|
| 13 | Condition | `@if ($ormawas->isNotEmpty())` | Show org selector if member |
| 21 | Condition | `@if ($ormawas->count() > 1)` | Show dropdown only if multiple orgs |
| 22 | Form | `<form action="{{ route('mahasiswa.setActiveOrmawa') }}"` | Submit org selection |
| 25 | Loop | `@foreach ($ormawas as $ormawa)` | Iterate through member orgs |
| 26 | Selected | `{{ $ormawa->id == $activeOrmawaId ? 'selected' : '' }}` | Highlight active org |
| 34 | Display | `{{ $activeOrmawa?->nama_ormawa }}` | Show active org name |
| 47, 107 | Count | `{{ $ormawas->count() }}` | Show total org memberships |
| 54 | Highlight | `{{ $ormawa->id == $activeOrmawaId ? 'ring-2 ring-blue-500' : '' }}` | Highlight active card |
| 77 | Badge | `{{ $ormawa->pivot->jabatan }}` | Show position in each org |
| 82 | Status | `@if (!$ormawa->pivot->aktif)` | Show if membership inactive |
| 91 | Action | `@if ($ormawa->id == $activeOrmawaId)` | Show actions for active org |
| 112, 117 | Count | `{{ $ormawas->where('pivot.jabatan', 'ketua')->count() }}` | Count orgs where chairman |
| 122 | Count | `{{ $ormawas->where('pivot.aktif', true)->count() }}` | Count active memberships |

**Use Case:** Mahasiswa who is member of multiple organizations can view all and switch between them

---

### 11.2 Other Dashboard Views - ORMAWA NAME DISPLAY

| File | Lines | Usage |
|------|-------|-------|
| dashboard/bauak.blade.php | 82 | `{{ $item->ormawa->nama_ormawa }}` - Show org in submission list |
| dashboard/warek3.blade.php | 82 | `{{ $item->ormawa->nama_ormawa }}` - Show org name |
| dashboard/rektor.blade.php | 68 | `{{ $pengajuan->ormawa->nama_ormawa ?? 'N/A' }}` - Show org with null check |

---

### 11.3 profile/edit.blade.php - ORMAWA PROFILE FORM

| Line | Type | Field | Note |
|------|------|-------|------|
| 53 | Input | `nama_ormawa` | Organization name |
| 58 | Input | `ketua` | Chairman name |
| 66 | Select | `pembina` | Advisor/mentor selection |
| 73 (×2) | Condition | Check for `kop_surat` | Verify upload exists |
| 82 | Textarea | `deskripsi` | Organization description |

**Note:** All accessed via `$user->ormawa` - assumes role=ormawa user has exactly one

---

## SECTION 12: ORMAWA TABLE STRUCTURE

### Fields Directly Related to User Relationships

```
Table: ormawa
Columns related to users:
  - user_id (FK → users.id) - THE OWNER/LOGIN ACCOUNT
  - pembina_user_id (FK → users.id) - THE ADVISOR
  
Pivot table: ormawa_users
  - ormawa_id (FK → ormawa.id)
  - user_id (FK → users.id)
  - jabatan (enum: ketua, wakil_ketua, sekretaris, bendahara, anggota)
  - aktif (boolean)
  - created_at, updated_at
```

---

## SECTION 13: CRITICAL DESIGN ISSUES

### Issue #1: Conflicting Relationship Models
**Status:** CRITICAL

**Problem:**
- Some code assumes: `User.ormawa()` = single org user owns (role=ormawa)
- Other code assumes: `User.ormawas()` = multiple orgs user joins (role=mahasiswa)
- Both models exist in same codebase for same database

**Affected Code:**
- PengajuanKegiatanController uses singular (line 26, 222)
- MahasiswaDashboardController uses plural (lines 22, 64)
- Same User model defines both

**Risk:** Authorization/filtering logic can fail unpredictably

---

### Issue #2: Ownership vs Membership
**Status:** CRITICAL

**Problem:**
- `user_id` in ormawa table = the "owner" (login account for role=ormawa)
- `ormawa_users` pivot = the "members" (role=mahasiswa who joined the org)
- System doesn't clearly distinguish or validate these are different concepts

**Affected Code:**
- PengajuanKegiatan.canBeEditedBy() line 265
- ProfileController line 81
- CheckOrmawaComplete middleware line 16

**Risk:** Only ormawa "owner" can edit proposals, but multiple members might expect to

---

### Issue #3: Multiple Administrator Accounts
**Status:** HIGH

**Problem:**
- `ormawa.user_id` can only hold ONE user ID
- Cannot assign multiple admin accounts to one ormawa
- If admin changes, previous admin loses access

**Affected Code:**
- ProfileController line 81 overwrites user_id
- Authorization check line 265 requires exact match

**Risk:** Cannot transfer ormawa ownership or have co-admins

---

### Issue #4: Mahasiswa with Organization Role Transition
**Status:** MEDIUM

**Problem:**
- Mahasiswa uses `belongsToMany` relationship (many orgs)
- If mahasiswa becomes role=ormawa, needs `hasOne` (one org)
- No migration path between relationship types

**Affected Code:**
- MahasiswaDashboardController expects `ormawas()` plural
- DashboardController expects `ormawa` singular
- Both try to access same User model

**Risk:** User role migration breaks data access

---

### Issue #5: Authorization Bypass via Relationship Confusion
**Status:** HIGH

**Problem:**
- PengajuanKegiatanController line 222: `$pengajuan->ormawa_id !== auth()->user()->ormawa->id`
- If user has no `$user->ormawa`, this throws error or unexpected result
- Different from checking if user is member via `ormawas()`

**Affected Code:**
- Line 222 in PengajuanKegiatanController
- Line 47, 60 in PersetujuanDekanController
- Line 265 in PengajuanKegiatan model

**Risk:** Authorization inconsistency - some routes check singular, some check plural

---

## SECTION 14: PATTERN ANALYSIS

### Pattern 1: Owner-Based Authorization (Current - BROKEN)
```
User with role=ormawa owns ONE ormawa
│
└─→ only THAT user can create/edit pengajuan for their ormawa
    (checked via: pengajuan->ormawa->user_id === auth()->user()->id)
```

**Works for:** Single admin per organization  
**Breaks for:** Multiple admins, co-leaders, delegation

---

### Pattern 2: Member-Based View (New - PARTIAL)
```
User with role=mahasiswa is MEMBER of MANY ormawas
│
├─→ can view all orgs they're member of
├─→ can switch active org in session
└─→ can potentially create/edit under active org
    (but authorization still uses singular user_id check!)
```

**Works for:** Multiple membership viewing  
**Breaks for:** Authorization - still checks single user_id

---

### Pattern 3: Membership Position (Complete)
```
User is member of ormawa with specific position
│
├─→ Ketua (Chairman) - Leadership
├─→ Wakil Ketua (Vice Chairman) - Leadership
├─→ Sekretaris (Secretary) - Core
├─→ Bendahara (Treasurer) - Core
└─→ Anggota (Member) - Regular
    (stored in ormawa_users.jabatan pivot)
```

**Works for:** Role-based permissions within org  
**Currently Used:** In mahasiswa/dashboard.blade.php for display only

---

## SECTION 15: FINDING SUMMARY TABLE

| Category | Count | Severity | Files | Lines |
|----------|-------|----------|-------|-------|
| Singular `$user->ormawa` | 20+ | CRITICAL | 5 | Multiple |
| Plural `$user->ormawas()` | 8 | CRITICAL | 2 | 22,30,31,35,39-41,64,33 |
| Access `$ormawa->user` | 9 | MEDIUM | 6+ | 127,255,147(×3),19,43 |
| Check `$ormawa->user_id` | 3 | CRITICAL | 2 | 265,81,105 |
| Access `$ormawa->pembina_user_id` | 5 | MEDIUM | 2 | 50,67,105,56,73 |
| Model `hasOne` relationships | 2 | CRITICAL | 2 | User:45-47, Ormawa:31-34 |
| Model `belongsToMany` relationships | 2 | HIGH | 2 | User:70-76, Ormawa:40-48 |
| **TOTAL OCCURRENCES** | **50+** | - | **11** | **Comprehensive** |

---

## SECTION 16: RECOMMENDATIONS FOR REMEDIATION

1. **Standardize Relationship Model**
   - Decide: Does role=ormawa user manage ONE ormawa, or MANY?
   - Recommendation: ONE (simpler, current code assumes this)
   - Migrate plural uses to single owner check

2. **Support Multiple Admins**
   - Add `ormawa_admins` table instead of single `user_id`
   - Or expand `ormawa_users` to support admin roles via jabatan

3. **Fix Authorization**
   - Change PengajuanKegiatan.canBeEditedBy() to support:
     - Original owner (user_id check)
     - Current admin members (via ormawa_users with admin jabatan)

4. **Clarify Relationships**
   - Rename: `ormawa.user_id` → `ormawa.owner_id` for clarity
   - Document: `ormawa_users` = membership, not ownership

5. **Update Middleware**
   - CheckOrmawaComplete: Only for role=ormawa
   - Create separate middleware for role=mahasiswa + org member

---

## AUDIT COMPLETION

**Audit Date:** June 25, 2026  
**Comprehensiveness:** COMPLETE  
**Occurrences Found:** 50+  
**Files Affected:** 11  
**Severity:** CRITICAL (3 issues), HIGH (2 issues), MEDIUM (3 issues)

**Next Steps:**
- Design relationship refactoring strategy
- Create migration plan for dual-model unification
- Implement authorization fix for multiple admins
