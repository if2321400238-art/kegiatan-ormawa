# ORMAWA AUDIT - QUICK REFERENCE GUIDE

## ALL OCCURRENCES BY FILE

### 1. app/Models/User.php

**Relationship 1: Singular (hasOne)**
- **Line 45-47:** Definition of `ormawa()` → `hasOne(Ormawa::class)`

**Relationship 2: Plural (belongsToMany)**
- **Line 70-76:** Definition of `ormawas()` → `belongsToMany(..., 'ormawa_users',...)`

**TYPE:** Model Definition  
**PATTERN:** BOTH singular and plural relationships defined on same model

---

### 2. app/Models/Ormawa.php

**Relationship 1: Inverse One-to-One**
- **Line 31-34:** Definition of `user()` → `belongsTo(User::class)`

**Relationship 2: Inverse Many-to-Many**
- **Line 40-48:** Definition of `users()` → `belongsToMany(..., 'ormawa_users',...)`

**TYPE:** Model Definition  
**PATTERN:** Mirrors both User relationships

---

### 3. app/Models/PengajuanKegiatan.php

**Line 40-42:** `belongsTo(Ormawa::class)` → defines which ormawa submitted pengajuan

**Line 265:** `$this->ormawa->user_id === $user->id` ⚠️ **CRITICAL AUTH CHECK**
- **Type:** Authorization check in `canBeEditedBy($user)` method
- **Assumes:** User owns the ormawa (singular relationship)
- **Risk:** Blocks co-admin scenarios

---

### 4. app/Http/Controllers/DashboardController.php

| Line | Type | Code | Pattern |
|------|------|------|---------|
| 43 | Get | `Auth::user()->ormawa` | Singular - gets authenticated ormawa user's org |

**Related Lines (stats filtered by ormawa_id):**
- 46, 47, 48, 51, 54, 55 → all use `$ormawa->id` for filtering

**PURPOSE:** Dashboard for role=ormawa users

---

### 5. app/Http/Controllers/PengajuanKegiatanController.php

| Line | Type | Code | Pattern |
|------|------|------|---------|
| 26 | Filter | `$user->ormawa->id` | Singular - filters pengajuan list |
| 66-71 | Count | `$user->ormawa->id` | Singular - statistics (6 occurrences) |
| 127 | Get | `auth()->user()->ormawa` | Singular - gets ormawa for creation |
| 185 | Check | `$pengajuan->ormawa->isFakultas()` | Check org type |
| 222 | Auth | `auth()->user()->ormawa->id` | ⚠️ **AUTHORIZATION** - Singular check |
| 358 | Query | `$user->ormawa->id` | Singular - edit pengajuan |
| 410 | Query | `$user->ormawa->id` | Singular - resubmit pengajuan |

**TOTAL:** 8+ occurrences  
**PATTERN:** Assumes user owns exactly ONE ormawa

---

### 6. app/Http/Controllers/ProfileController.php

| Line | Type | Code | Purpose |
|------|------|------|---------|
| 77 | Get | `$user->ormawa` | Fetch profile for editing |
| 81 | Set | `$ormawa->user_id = $user->id` | ⚠️ **SET OWNERSHIP** |
| 105 | Set | `$ormawa->pembina_user_id = $pembinaUser?->id` | Set advisor |

**PATTERN:** Treats `ormawa` as singular owned resource

---

### 7. app/Http/Controllers/MahasiswaDashboardController.php

| Line | Type | Code | Pattern |
|------|------|------|---------|
| 22 | Query | `$user->ormawas()` | Plural - gets all member orgs |
| 30 | Check | `$ormawas->isNotEmpty()` | Plural - check if any orgs |
| 31 | Get | `$ormawas->first()->id` | Plural - select first as default |
| 35 | Find | `$ormawas->firstWhere('id', $activeOrmawaId)` | Plural - find active |
| 64 | Verify | `$user->ormawas()->where(...)->exists()` | Plural - verify member |

**TOTAL:** 5 direct usages  
**PATTERN:** Uses many-to-many for mahasiswa org memberships

---

### 8. app/Http/Middleware/CheckOrmawaComplete.php

| Line | Type | Code | Purpose |
|------|------|------|---------|
| 16 | Get | `$user->ormawa` | Singular - fetch ormawa profile |
| 21-24 | Create | Creates ormawa with `user_id = $user->id` | Auto-create ownership |

**PATTERN:** Assumes role=ormawa has one ormawa

---

### 9. app/Http/Middleware/EnsureActiveOrmawa.php

| Line | Type | Code | Purpose |
|------|------|------|---------|
| 33 | Verify | `$user->ormawas()->where('ormawa_id',...)->exists()` | Plural - verify still member |

**PATTERN:** Validates session org still in user's memberships

---

### 10. app/Http/Controllers/VerifikasiDosenController.php

| Line | Type | Code | Purpose |
|------|------|------|---------|
| 50 | Get | `$pengajuan->ormawa->pembina_user_id` | Get advisor ID |
| 56 | Check | `$pengajuan->ormawa->pembina` (by name) | Check if is advisor |
| 67 | Get | `$pengajuan->ormawa->pembina_user_id` | Get advisor ID (duplicate) |
| 73 | Check | `$pengajuan->ormawa->pembina` (by name) | Check if is advisor (duplicate) |
| 97 | Check | `$pengajuan->ormawa->isFakultas()` | Determine workflow |
| 110 | Check | `$pengajuan->ormawa->isFakultas()` | Determine workflow |
| 147 | Access | `$pengajuan->ormawa->user` | Get owner for notification |
| 158 | Display | `$pengajuan->ormawa->nama_ormawa` | Show name in message |
| 160 | Get | `$pengajuan->ormawa->fakultas?->dekan` | Get dean |
| 182 | Display | `$pengajuan->ormawa->nama_ormawa` | Show name in message |

**TOTAL:** 10 occurrences

---

### 11. app/Http/Controllers/PersetujuanDekanController.php

| Line | Type | Code | Purpose |
|------|------|------|---------|
| 47 | Check | `$pengajuan->ormawa->fakultas_id` | Verify ownership |
| 60 | Check | `$pengajuan->ormawa->fakultas_id` | Verify ownership |
| 147 | Access | `$pengajuan->ormawa->user` | Get owner for notification |
| 164 | Display | `$pengajuan->ormawa->nama_ormawa` | Show name in message (×2) |

---

### 12. app/Http/Controllers/PersetujuanWarek3Controller.php

| Line | Type | Code | Purpose |
|------|------|------|---------|
| 255 | Access | `$pengajuan->ormawa->user` | Get owner for notification |

---

### 13. app/Http/Controllers/PersetujuanRektorController.php

| Line | Type | Code | Purpose |
|------|------|------|---------|
| 127 | Access | `$pengajuan->ormawa->user` | Get owner for notification |

---

### 14. app/Http/Controllers/VerifikasiBauakController.php

| Line | Type | Code | Purpose |
|------|------|------|---------|
| 132 | Access | `$pengajuan->ormawa->user` | Get owner for notification |

---

### 15. app/Http/Controllers/OrmawaAnggotaController.php

| Line | Type | Code | Purpose |
|------|------|------|---------|
| 17 | Get | `$ormawa->users()` | Get members (many-to-many) |
| 30 | Get | `$ormawa->users()->pluck('users.id')` | Get member IDs |
| 59 | Check | `$ormawa->users()->where(...)` | Check if already member |
| 64 | Attach | `$ormawa->users()->attach(...)` | Add member with role |
| 79 | Check | `$ormawa->users()->where(...)` | Verify member for edit |
| 101 | Check | `$ormawa->users()->where(...)` | Verify member for update |
| 111 | Update | `$ormawa->users()->updateExistingPivot(...)` | Update member position |
| 131 | Remove | `$ormawa->users()->detach(...)` | Remove member |

**TOTAL:** 8 occurrences  
**PATTERN:** Many-to-many member management

---

### 16. app/Services/SuratRekomendasiService.php

| Line | Type | Code | Purpose |
|------|------|------|---------|
| 19 | Pass | `'ormawa' => $pengajuan->ormawa` | Pass to view |
| 43 | Pass | `'ormawa' => $pengajuan->ormawa` | Pass to PDF |

**NOTE:** May access ormawa->user inside view/PDF

---

### 17. app/Http/Controllers/LaporanController.php

| Line | Type | Code | Purpose |
|------|------|------|---------|
| 36 | Get | `$items->first()->ormawa->nama_ormawa` | Group name |
| 152 | Get | `$item->ormawa->nama_ormawa` | CSV export |
| 73 | Count | `Ormawa::withCount('pengajuanKegiatan')` | Most active orgs |

---

### 18. resources/views/profile/edit.blade.php

| Line | Type | Code | Purpose |
|------|------|------|---------|
| 53 | Display | `$user->ormawa->nama_ormawa` | Org name input |
| 58 | Display | `$user->ormawa->ketua` | Chairman input |
| 66 | Select | `$user->ormawa->pembina` | Advisor select |
| 73 | Condition | `$user->ormawa && $user->ormawa->kop_surat` | Check kop_surat (×2) |
| 82 | Display | `$user->ormawa->deskripsi` | Description textarea |

**TOTAL:** 6 blade references

---

### 19. resources/views/mahasiswa/dashboard.blade.php

| Line | Type | Code | Purpose |
|------|------|------|---------|
| 13 | Condition | `@if ($ormawas->isNotEmpty())` | Plural - check any orgs |
| 21 | Condition | `@if ($ormawas->count() > 1)` | Plural - show if multiple |
| 25 | Loop | `@foreach ($ormawas as $ormawa)` | Plural - iterate orgs |
| 26 | Selected | `$ormawa->id == $activeOrmawaId` | Check if active |
| 34 | Display | `$activeOrmawa->nama_ormawa` | Show active name |
| 47 | Count | `$ormawas->count()` | Total membership count |
| 54 | CSS | `$ormawa->id == $activeOrmawaId` | Highlight active |
| 60 | Display | `$ormawa->nama_ormawa` | Show name in card |
| 62 | Condition | `$ormawa->kategori_organisasi === 'internal'` | Check type |
| 64 | Display | `$ormawa->tingkat_organisasi` | Show level |
| 77 | Badge | `$ormawa->pivot->jabatan` | Show position |
| 82 | Status | `!$ormawa->pivot->aktif` | Check if inactive |
| 91 | Condition | `$ormawa->id == $activeOrmawaId` | Show actions if active |
| 107, 112, 117, 122 | Count | Pivot-based counts | Various statistics |

**TOTAL:** 13+ blade references  
**PATTERN:** Many-to-many plural usage

---

### 20. Other Dashboard Views (dashboard/bauak.blade.php, etc.)

| File | Lines | Code |
|------|-------|------|
| dashboard/bauak.blade.php | 82, 123 | `{{ $item->ormawa->nama_ormawa }}` |
| dashboard/warek3.blade.php | 82, 123 | `{{ $item->ormawa->nama_ormawa }}` |
| dashboard/rektor.blade.php | 68, 125 | `{{ $pengajuan->ormawa->nama_ormawa ?? 'N/A' }}` |
| pengajuan/show.blade.php | 332, 337, 339 | `{{ $pengajuan->ormawa->nama_ormawa }}` |
| dekan/persetujuan/index.blade.php | 26 | `{{ $item->ormawa->nama_ormawa }}` |

---

## PATTERN DISTRIBUTION

### By Type:

**Singular `$user->ormawa` (One-to-One Assumption):**
- DashboardController: 1 main + 6 related = 7
- PengajuanKegiatanController: 8+
- ProfileController: 3
- CheckOrmawaComplete middleware: 2
- Views (profile/edit): 6
- **TOTAL: 26+ occurrences**

**Plural `$user->ormawas()` (Many-to-Many Assumption):**
- MahasiswaDashboardController: 5
- EnsureActiveOrmawa middleware: 1
- **TOTAL: 6 occurrences**

**Many-to-Many Member Access via `->users()`:**
- OrmawaAnggotaController: 8
- **TOTAL: 8 occurrences**

**Authorization/Ownership Checks:**
- PengajuanKegiatan line 265: `user_id ===` check
- ProfileController line 81: `user_id =` set
- PengajuanKegiatanController line 222: `user_id !==` check
- **TOTAL: 3 critical occurrences**

**Notification Access to `$ormawa->user`:**
- 5 controllers (Rektorik, Warek3, Dosen, Bauak, Dekan): 5 occurrences
- Service layer: 2 occurrences
- **TOTAL: 7+ occurrences**

### By Severity:

| Severity | Count | Pattern | Impact |
|----------|-------|---------|--------|
| CRITICAL | 29+ | Singular `$user->ormawa` | Authorization fails with multiple admins |
| CRITICAL | 3 | `user_id` checks | Blocks non-owner admins |
| HIGH | 6 | Plural inconsistency | Confusion between patterns |
| MEDIUM | 7+ | Owner notification | Assumes single owner |
| LOW | 8 | Member management | Working correctly |

---

## QUICK SEARCH REFERENCE

### Find all uses of singular ormawa:
```
grep -r "\->ormawa" app/Http/Controllers/ app/Models/ app/Http/Middleware/
```

### Find all uses of plural ormawas:
```
grep -r "\->ormawas()" app/Http/Controllers/ app/Http/Middleware/
```

### Find all user_id checks:
```
grep -r "ormawa.*user_id\|user_id.*ormawa" app/
```

### Find all ormawa->user accesses:
```
grep -r "ormawa->user" app/
```

---

## CRITICAL PATHS TO FIX

### Priority 1: Authorization Bug
- **File:** PengajuanKegiatanController.php
- **Lines:** 26, 222
- **Issue:** Only allows role=ormawa owner to edit
- **Fix:** Support multiple admins via role/position

### Priority 2: Model Consistency  
- **File:** User.php
- **Lines:** 45-47, 70-76
- **Issue:** Both singular and plural relationships
- **Fix:** Choose one relationship model; migrate other users

### Priority 3: Ownership Transfer
- **File:** ProfileController.php
- **Line:** 81
- **Issue:** Can only have one user_id owner
- **Fix:** Support multiple admins or transfer ownership

---

## AUDIT METADATA

**Total Occurrences:** 50+  
**Files Affected:** 20  
**Lines Analyzed:** 100+  
**Patterns Found:** 5  
**Critical Issues:** 3  
**High Severity:** 2  
**Medium Severity:** 3
