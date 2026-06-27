# ARCHITECTURE REVIEW & MIGRATION PLAN
**Date:** 2026-06-25  
**Status:** ANALYSIS ONLY - NO CODE MODIFICATIONS YET  
**Scope:** Data model review based on new business rules

---

## EXECUTIVE SUMMARY

**Current State:** Hybrid system with conflicting design patterns  
**New Business Rules:**
1. Users login as mahasiswa using NIM
2. One mahasiswa → many ormawas (membership model)
3. Ormawas do NOT have login accounts (no role=ormawa users needed)
4. Every pengajuan tracks: ormawa_id, created_by_user_id, updated_by_user_id
5. Approval workflow unchanged

**Impact Assessment:** 
- **⚠️ HIGH RISK:** 26+ code locations assume one-to-one ormawa-user relationship
- **✅ GOOD NEWS:** Core audit trail already in place (created_by_user_id, updated_by_user_id)
- **🚨 BLOCKING ISSUE:** Authorization logic breaks with multiple ormawaas per user

---

## SECTION 1: SCHEMA ANALYSIS

### 1.1 Current Table Relationships

```
users (1) ────────────────────────────────────── (many) ormawa
         (via user_id FK)                    (via SINGULAR hasOne)
         
users (N) ────── (N) ormawa
         (via ormawa_users pivot table)     (via PLURAL belongsToMany)
         
ormawa (1) ──── (many) pengajuan_kegiatan
          (via ormawa_id FK)
          
pengajuan_kegiatan records:
  - ormawa_id                 ← Which org submitted
  - created_by_user_id (NEW)  ← Who created
  - updated_by_user_id (NEW)  ← Who last modified
```

### 1.2 REDUNDANT/AMBIGUOUS COLUMNS

#### ❌ PROBLEM #1: `ormawa.user_id` - NOW MEANINGLESS

**Current Semantics:** "The user account that owns/manages this ormawa"

**Why Redundant in New Model:**
- In new model, ormawas do NOT have login accounts
- Ormawas are organizational units, NOT user accounts
- Multiple mahasiswa can manage same ormawa (via ormawa_users pivot)
- User account concept moves to pengajuan level (created_by_user_id, updated_by_user_id)

**Current Usage (MUST BE FIXED):**
1. **PengajuanKegiatan.php:265** - Authorization: `$this->ormawa->user_id === $user->id`
   - ❌ BLOCKS: Assumes ONE user "owns" the ormawa
   - ❌ BREAKS: Multiple admins scenario
   - ❌ BREAKS: Mahasiswa with multiple ormawa memberships

2. **DashboardController.php:43** - Gets authenticated user's ormawa
   - ❌ BREAKS: Mahasiswa shouldn't have $user->ormawa (singular)
   - ✅ WORKS: Only if role=ormawa (old pattern)

3. **PengajuanKegiatanController.php** - Multiple uses:
   - Line 26: Filters pengajuan by $user->ormawa->id
   - Lines 66-71: Statistics queries
   - Line 127: Gets $user->ormawa for creation
   - Line 222: Authorization check
   - Lines 358, 410: Edit/resubmit queries
   - ❌ ALL BREAK: With mahasiswa multi-ormawa model

4. **ProfileController.php** - Sets ownership:
   - Line 81: `$ormawa->user_id = $user->id`
   - ❌ BREAKS: Cannot have multiple ormawa admins
   - ❌ WRONG: For mahasiswa managing multiple orgs

5. **Notifications/Workflows** - Access `$pengajuan->ormawa->user`:
   - 9 locations in approval controllers
   - ❌ BREAKS: No meaningful "owner user" for notifications
   - Need: Track who created/last updated pengajuan instead

---

#### ❌ PROBLEM #2: Enum `users.role` MISSING 'mahasiswa'

**Current Values:**
```
'ormawa', 'bauak', 'warek3', 'admin', 'dosen', 'dekan', 'rektor', 'pp'
```

**Issue:** No 'mahasiswa' role in enum  
**Status:** ✅ ALREADY ADDED (migration 2026_06_25_125630)  
**Migration Exists:** YES - `add_mahasiswa_to_role_enum.php`

---

#### ✅ GOOD: `users.nim` FIELD EXISTS

**Status:** Already added via migration 2026_06_25_000005  
**Type:** `varchar` nullable unique  
**Purpose:** NIM-based login for mahasiswa  
**Migration Exists:** YES

---

#### ✅ GOOD: Audit Trail ALREADY IN pengajuan_kegiatan

**Fields Added:** Via migration 2026_06_25_140000
- `created_by_user_id` (FK → users, nullable)
- `updated_by_user_id` (FK → users, nullable)

**Relationships Already Defined:**
- `creator()` → belongsTo User via created_by_user_id
- `updater()` → belongsTo User via updated_by_user_id

---

### 1.3 AMBIGUOUS RELATIONSHIPS

#### Relationship Type #1: Singular `$user->ormawa` (hasOne)

**Current Use:** Assumes user "owns" exactly ONE ormawa  
**Files Affected:** 5 controllers + 1 middleware  
**Occurrences:** 26+ locations

**Why Ambiguous:**
- Only works if user has ONE ormawa
- Breaks for mahasiswa with 3+ ormawas
- Conflicts with plural `$user->ormawas()` pattern
- Creates authorization logic that assumes single ownership

**Contexts:**
1. **DashboardController** - Ormawa dashboard (only valid for role=ormawa)
2. **PengajuanKegiatanController** - Pengajuan operations (assumes user owns one org)
3. **ProfileController** - Profile editing (assumes user owns one org)
4. **CheckOrmawaComplete middleware** - Auto-creates ormawa for user
5. **Notification passing** - Assumes `$pengajuan->ormawa->user` is meaningful

---

#### Relationship Type #2: Plural `$user->ormawas()` (belongsToMany)

**Current Use:** Mahasiswa dashboard shows all org memberships  
**Files Affected:** 2 controllers + 1 middleware  
**Occurrences:** 8 locations (NEW PATTERN)

**Why Ambiguous:**
- Coexists with singular pattern in same model
- MahasiswaDashboardController uses plural
- Other controllers use singular
- Both use same underlying data but interpret differently

**Contexts:**
1. **MahasiswaDashboardController** - Shows all orgs user is member of
2. **EnsureActiveOrmawa middleware** - Validates session ormawa is still valid membership

---

### 1.4 AUTHORIZATION BUG - CRITICAL

**Location:** `PengajuanKegiatan.php::canBeEditedBy()` line 265

**Current Code Logic:**
```
if ($user->isOrmawa()) {
    return ... && $this->ormawa->user_id === $user->id
}
```

**Why Broken:**
1. Assumes ormawa HAS ONE owner user
2. Only that ONE user can edit pengajuan
3. Blocks all other admins/members
4. Breaks completely with mahasiswa multi-ormawa model

**Scenarios That Fail:**
- Mahasiswa A is member of BEM + PMII
- Creates pengajuan as PMII
- Mahasiswa B is also member of PMII
- Mahasiswa B CANNOT edit the pengajuan (because `ormawa.user_id ≠ B's id`)
- ❌ FAILS: Should allow any PMII member to edit

---

---

## SECTION 2: CODE ARCHITECTURE ISSUES

### 2.1 Files Still Assuming "Ormawa Account Login"

| File | Lines | Issue | Type |
|------|-------|-------|------|
| **DashboardController.php** | 43-71 | Uses $user->ormawa (singular) | Critical |
| **PengajuanKegiatanController.php** | 26, 66-71, 127, 222, 358, 410 | Uses $user->ormawa for authorization | Critical |
| **ProfileController.php** | 77, 81, 105 | Sets $ormawa->user_id = $user->id | Critical |
| **CheckOrmawaComplete.php** | 16, 21-24 | Auto-creates ormawa for user | Critical |
| **profile/edit.blade.php** | 53, 58, 66, 73, 82 | Expects $user->ormawa to exist | Medium |
| **VerifikasiDosenController.php** | 50, 56, 67, 73 | Accesses ormawa owner/advisor | Medium |
| **PersetujuanDekanController.php** | 147 | Notifies $pengajuan->ormawa->user | Medium |
| **PersetujuanWarek3Controller.php** | 255 | Notifies $pengajuan->ormawa->user | Medium |
| **VerifikasiBauakController.php** | 132 | Notifies $pengajuan->ormawa->user | Medium |

---

### 2.2 Controllers That NEED REFACTORING

#### TIER 1 - CRITICAL (Block Phase 4 Implementation):

1. **PengajuanKegiatanController.php**
   - **Problem:** Assumes role=ormawa users have one ormawa
   - **Lines to Fix:** 26, 66-71, 127, 222, 358, 410
   - **Pattern:** `$user->ormawa->id` → must change to use `session('active_ormawa_id')`
   - **Authorization:** Line 222 authorization check completely broken
   - **Impact:** Blocks Phase 4 (Pengajuan integration)

2. **PengajuanKegiatan.php Model**
   - **Problem:** `canBeEditedBy()` authorization method assumes one owner
   - **Line:** 265
   - **Pattern:** `$this->ormawa->user_id === $user->id`
   - **Fix Strategy:** Change to check if user is member of ormawa via ormawa_users
   - **Impact:** Blocks Phase 4, breaks security model

---

#### TIER 2 - HIGH PRIORITY (Still Assume Ormawa Account):

3. **DashboardController.php**
   - **Problem:** Assumes authenticated user owns ONE ormawa
   - **Line:** 43
   - **Pattern:** `Auth::user()->ormawa`
   - **Usage:** Only valid for old role=ormawa users
   - **Fix Strategy:** Either retire or conditional on role check

4. **CheckOrmawaComplete Middleware**
   - **Problem:** Auto-creates ormawa for ANY user with role=ormawa
   - **Lines:** 16, 21-24
   - **Pattern:** Creates if not exists and sets user_id
   - **Impact:** No longer needed in new model (ormawaas created admin-side)

5. **ProfileController.php**
   - **Problem:** Sets ormawa ownership to authenticated user
   - **Lines:** 77, 81, 105
   - **Pattern:** Treats ormawa as user's singular resource
   - **Impact:** Profile editing for ormawa account holders (legacy)

---

#### TIER 3 - MEDIUM PRIORITY (Notification/Workflows):

6. **Approval Controllers** (Dosen, Dekan, Warek3, BAUAK)
   - **Problem:** Send notifications to `$pengajuan->ormawa->user`
   - **Lines:** Multiple (see audit)
   - **Pattern:** Assumes ormawa has ONE "user" to notify
   - **Fix Strategy:** Notify creator (created_by_user_id) instead, or track stakeholders

7. **VerifikasiDosenController.php**
   - **Problem:** Checks if current user is advisor via name matching
   - **Lines:** 56, 73
   - **Pattern:** `$pengajuan->ormawa->pembina` (string name) vs auth()->user()->nama
   - **Better Approach:** Use pembina_user_id instead of string matching

---

### 2.3 MODEL RELATIONSHIP REDUNDANCY

#### Current User.php Has BOTH:
```php
// One-to-one (assumes user owns one ormawa)
public function ormawa() { hasOne(Ormawa::class) }

// Many-to-many (user is member of multiple ormawas)
public function ormawas() { belongsToMany(Ormawa::class, 'ormawa_users') }
```

**Problem:** Both coexist but represent conflicting patterns
- Controllers using one pattern conflict with controllers using other
- Role=ormawa should use singular (legacy)
- Role=mahasiswa should use plural (new)
- Both defined on same model causing confusion

**Naming Conflict:**
- `ormawa()` suggests "my ormawa" (ownership)
- `ormawas()` suggests "my memberships" (participation)
- Both are technically correct but semantically conflicting

---

### 2.4 VIEW LAYER ASSUMPTIONS

**profile/edit.blade.php:**
- Assumes `$user->ormawa` exists
- Displays fields: nama_ormawa, ketua, pembina, kop_surat, deskripsi
- Used by role=ormawa users for profile editing
- ❌ Would break if user has no singular ormawa

---

---

## SECTION 3: FUTURE MIGRATION REQUIREMENTS

### Phase 4 Implementation (Pengajuan Integration)

#### 3.1 REQUIRED CHANGES - BLOCKING ISSUES

**Change #1: Fix Authorization Logic**
- **File:** PengajuanKegiatan.php
- **Method:** canBeEditedBy($user)
- **Current:** `$this->ormawa->user_id === $user->id`
- **New:** Check if user is member of pengajuan's ormawa via ormawa_users pivot
- **Logic:** `$user->ormawas()->where('ormawa_id', $this->ormawa_id)->exists()`

**Change #2: Use Session Active Ormawa**
- **File:** PengajuanKegiatanController.php
- **Lines:** 26, 66-71, 127, 222, 358, 410
- **Current:** `$user->ormawa->id`
- **New:** `session('active_ormawa_id')`
- **Rationale:** Mahasiswa doesn't have singular ormawa, uses session context

**Change #3: Auto-Fill Audit Trail on Create**
- **File:** PengajuanKegiatanController.php::store()
- **Current:** Leaves created_by_user_id as NULL
- **New:** Auto-set `created_by_user_id = auth()->id()`
- **Audit:** Already has fields, just need to populate

**Change #4: Track Updates**
- **File:** Workflow approval controllers (Dosen, Dekan, BAUAK, Warek3, Rektor)
- **Current:** Doesn't track who updated status
- **New:** Set `updated_by_user_id = auth()->id()` on each status change

---

#### 3.2 OPTIONAL IMPROVEMENTS - NOT BLOCKING

**Improvement #1: Deprecate Singular `$user->ormawa`**
- Create new relationship name for role=ormawa users: `$user->ownedOrmawa()`
- Gradually migrate DashboardController, ProfileController to use new name
- Keep both for backward compatibility during transition
- Timeline: Post-Phase 4

**Improvement #2: Fix Advisor Name Matching**
- **File:** VerifikasiDosenController.php lines 56, 73
- **Current:** String comparison `$pengajuan->ormawa->pembina === auth()->user()->nama`
- **Better:** Use `$pengajuan->ormawa->pembina_user_id === auth()->id()`
- **Benefit:** More reliable, resistant to name changes
- **Timeline:** Phase 5 (if needed)

**Improvement #3: Retire CheckOrmawaComplete Middleware**
- Only needed for role=ormawa users
- In new model, ormawas created admin-side, not auto-created
- Can be kept for legacy support but not needed for mahasiswa
- Timeline: Post-Phase 4 cleanup

**Improvement #4: Refactor Notifications**
- Current: Sends to `$pengajuan->ormawa->user` (vague)
- Better: Send to `$pengajuan->creator` (clear who gets notified)
- Also notify: All members of ormawa? Advisors? (configurable)
- Timeline: Phase 5+

---

### 3.3 DATA MIGRATION REQUIREMENTS

**Migration #0 - ALREADY DONE:**
- ✅ Add `users.role = 'mahasiswa'` to enum
- ✅ Add `users.nim` field for NIM-based login
- ✅ Add `pengajuan_kegiatan.created_by_user_id` audit field
- ✅ Add `pengajuan_kegiatan.updated_by_user_id` audit field
- ✅ Create `ormawa_users` pivot table for memberships

**Migration #1 - PHASE 4 PREP:**
- Backfill existing pengajuan's `created_by_user_id`:
  - Match to `ormawa.user_id` (the user who "owned" the ormawa that submitted)
  - Assumption: That's who created the pengajuan
  - SELECT pengajuan.id, ormawa.user_id FROM pengajuan_kegiatan 
    LEFT JOIN ormawa ON pengajuan_kegiatan.ormawa_id = ormawa.id
  - UPDATE pengajuan SET created_by_user_id = matched user_id

**Migration #2 - OPTIONAL CLEANUP (Post-Phase 4):**
- Deprecate `ormawa.user_id` (but keep for now - data retention)
- Or rename to `created_by_user_id` for consistency with pengajuan

---

---

## SECTION 4: REDUNDANT COLUMNS DETAILED ANALYSIS

### 4.1 `ormawa.user_id` - REDUNDANT IN NEW MODEL

#### Current Purpose:
"Single user account that owns/manages this ormawa"

#### Why Redundant Now:
1. **Ormawas have NO login accounts** (new business rule #3)
2. **Multiple mahasiswa manage each ormawa** (new business rule #2)
3. **Ownership encoded in ormawa_users pivot** (via role/jabatan)
4. **Audit trail on pengajuan** (created_by_user_id is WHO not WHERE)

#### Current Data It Holds:
- ID of user with role=ormawa who "registered" this organization
- Now meaningless because ormawas don't need registration accounts

#### What Should Track Ownership Instead:
- **In ormawa_users:** jabatan='ketua' indicates leadership
- **In pengajuan_kegiatan:** created_by_user_id + updated_by_user_id track actors
- **Workflow tables:** Already track which users approved/verified

#### Risk of Keeping It:
- Authorization logic breaks (PengajuanKegiatan.php:265)
- Notifications target wrong user (9 locations in approval controllers)
- Blocks multi-admin scenarios
- Confuses developers: "Is this user the owner or just a member?"

#### Risk of Removing It:
- Loss of historical "who registered the org" data
- Breaking change for existing ormawa account holders
- Some admin queries might depend on it

#### Recommendation:
**Deprecate but keep** (for backward compatibility)
- Mark column as @deprecated in code
- Use pengajuan audit fields for all new tracking
- Document that ownership is now in ormawa_users.jabatan='ketua'

---

### 4.2 `ormawa.pembina` (String) vs `ormawa.pembina_user_id` (FK)

#### Current State:
- BOTH exist (redundant!)
- `pembina` = string name of advisor
- `pembina_user_id` = foreign key to users.id

#### Why Redundant:
- pembina_user_id is sufficient to look up the name
- Storing name AND ID violates normalization
- String name can get out of sync with user record

#### Where Stored:
- pembina (string): Used in views, notifications, PDFs
- pembina_user_id (FK): Used in authorization checks (VerifikasiDosenController)

#### Issue Found:
**VerifikasiDosenController.php lines 56, 73:**
```
if ((($pengajuan->ormawa->pembina ?? null) !== auth()->user()->nama))
```
Compares STRING name instead of using pembina_user_id FK
- ❌ Breaks if user changes their name
- ❌ Inefficient string comparison
- ✅ Should use `pembina_user_id === auth()->id()` instead

#### Risk of Removing String:
- Views/PDFs would need to load User relationship first
- Slightly more complex queries

#### Recommendation:
**Deprecate pembina string column**
- Mark as @deprecated
- Keep for now (data retention)
- All new code uses pembina_user_id
- Queries eager-load user via pembina_user_id for display

---

### 4.3 SUMMARY: Redundant Columns

| Column | Table | Type | Issue | Action |
|--------|-------|------|-------|--------|
| `user_id` | ormawa | FK | No longer meaningful (ormawa not an account) | Deprecate, keep data |
| `pembina` | ormawa | string | Duplicates pembina_user_id data | Deprecate, use FK instead |
| ~~none~~ | users | - | No redundancy found | ✅ OK |
| ~~none~~ | ormawa_users | - | No redundancy found | ✅ OK |
| ~~none~~ | pengajuan_kegiatan | - | No redundancy found (audit fields needed) | ✅ OK |

---

---

## SECTION 5: FUTURE ARCHITECTURE RECOMMENDATION

### 5.1 Ideal Future State (Post-Phase 4)

```
AUTHENTICATION LAYER:
- User login by: NIM + password (mahasiswa)
- All users have role in ['mahasiswa', 'bauak', 'dosen', 'dekan', 'rektor', 'warek3', 'pp', 'admin']
- NO MORE: role='ormawa' (ormawas are NOT accounts)

ORGANIZATIONAL MEMBERSHIP:
- User.ormawas() → belongs to many ormawas via ormawa_users pivot
- Ormawa.users() → has many members via ormawa_users pivot
- ormawa_users.jabatan → indicates role (ketua, sekretaris, etc.)

PENGAJUAN WORKFLOW:
- pengajuan.created_by_user_id → WHO submitted it
- pengajuan.updated_by_user_id → WHO last modified it
- pengajuan.ormawa_id → WHICH org it belongs to
- Approval tables → Track WHO approved/verified (via user_id FK)

NOTIFICATIONS:
- Send to: pengajuan.creator (user_id from created_by_user_id)
- Send to: All ormawa members? (configurable)
- Send to: Advisors? (via pembina_user_id)
```

### 5.2 Data Model Simplification

**Remove:**
- `users.role = 'ormawa'` (enum value can be kept for backward compatibility)
- Singular `User.ormawa()` relationship (confusing, replaced by plural)
- Auto-creation logic in CheckOrmawaComplete middleware

**Keep:**
- `ormawa.user_id` (legacy reference, but not used for auth)
- `User.ormawa()` hasOne for backward compatibility queries
- All audit fields in pengajuan_kegiatan

**Add:**
- API endpoint for mahasiswa to select active ormawa (already have: route 'mahasiswa.setActiveOrmawa')
- Middleware to ensure active ormawa selected (already have: EnsureActiveOrmawa)

---

---

## SECTION 6: IMPLEMENTATION PHASE BREAKDOWN

### Phase 4: Pengajuan Integration (NEXT)
**Estimated Changes:** 8-10 files  
**Risk Level:** MEDIUM (authorization refactoring)

**Must Fix:**
1. PengajuanKegiatan.php::canBeEditedBy() - authorization check
2. PengajuanKegiatanController - use session active ormawa
3. Auto-populate created_by_user_id on store()

**Should Fix:**
4. Approval controllers - track updated_by_user_id on status changes
5. Notifications - send to correct recipients

---

### Phase 5: Legacy Pattern Cleanup (LATER)
**Estimated Changes:** 5-7 files  
**Risk Level:** LOW (refactoring, not new features)

**Can Fix:**
1. Deprecate CheckOrmawaComplete middleware
2. Fix pembina name matching in VerifikasiDosenController
3. Rename singular ormawa() to ownedOrmawa() for clarity

---

### Phase 6+: Data Cleanup (POST-MVP)
**Estimated Changes:** Migrations  
**Risk Level:** LOW (if done properly)

**Can Do:**
1. Archive/remove role=ormawa accounts
2. Remove pembina string column
3. Deprecate ormawa.user_id column

---

---

## SECTION 7: BLOCKERS & RISKS

### BLOCKER: Authorization Breaks With Mahasiswa Multi-Ormawa

**Location:** PengajuanKegiatan.php::canBeEditedBy(line 265)  
**Severity:** 🚨 CRITICAL - BLOCKS PHASE 4

**Current Code:**
```php
public function canBeEditedBy($user): bool
{
    if ($user->isOrmawa()) {
        return ... && $this->ormawa->user_id === $user->id;
    }
    return false;
}
```

**Why Blocks Phase 4:**
- Only allows edit if user IS the ormawa owner
- With mahasiswa model, NO ownership concept
- Breaks for ALL mahasiswa trying to edit their pengajuan
- Must be fixed BEFORE Phase 4 goes live

**Solution:**
```php
// NEW authorization check
$isMember = $user->ormawas()
    ->where('ormawa_id', $this->ormawa_id)
    ->exists();
```

---

### RISK: Backward Compatibility With Existing Ormawa Accounts

**Issue:** Current system has role=ormawa users who "own" ormawas  
**Question:** Will these users continue to exist in new model?

**Options:**
1. **Migrate all role=ormawa → role=mahasiswa** with memberships
   - Pro: Consistent model
   - Con: Change in user roles
   - Timeline: Future cleanup

2. **Keep both role=ormawa and role=mahasiswa**
   - Pro: No user migration needed
   - Con: Code complexity increases
   - Timeline: Support both, deprecate ormawa role later

3. **Lock existing ormawa accounts, use mahasiswa only for new**
   - Pro: Minimal disruption
   - Con: Two separate user types

**Recommendation:** Option 2 (keep both) for Phase 4, then deprecate in Phase 6+

---

### RISK: Session State Loss

**Issue:** If user's ormawa_users record deleted, session becomes invalid  
**Mitigation:** Already implemented - EnsureActiveOrmawa middleware validates and clears

**No Additional Work Needed:** ✅

---

---

## SECTION 8: SUMMARY TABLE - ACTION ITEMS

| Item | Type | File | Lines | Phase | Priority |
|------|------|------|-------|-------|----------|
| Fix canBeEditedBy() auth | Code | PengajuanKegiatan.php | 265 | 4 | 🚨 CRITICAL |
| Use session active ormawa | Code | PengajuanKegiatanController.php | 26,66-71,127,222,358,410 | 4 | 🚨 CRITICAL |
| Populate created_by_user_id | Code | PengajuanKegiatanController.php::store | 127+ | 4 | 🔴 HIGH |
| Deprecate $user->ormawa() | Doc | User.php | 45-47 | 5 | 🟡 MEDIUM |
| Fix pembina name check | Code | VerifikasiDosenController.php | 56, 73 | 5 | 🟡 MEDIUM |
| Deprecate ormawa.user_id | Doc | ormawa.user_id | Schema | 5 | 🟡 MEDIUM |
| Update notifications | Code | Approval controllers | Multiple | 5 | 🟡 MEDIUM |
| Data migration - backfill created_by | Migration | - | - | 4 | 🔴 HIGH |

---

## CONCLUSION

**Current State Assessment:**
✅ Good: Audit trail fields exist, relationships defined  
❌ Bad: Authorization broken, 26+ locations assume one-to-one ormawa-user  
⚠️ Risk: Conflicting design patterns (singular vs plural)

**Phase 4 Readiness:** 
- ✅ Database schema ready (audit fields in place)
- ❌ Application logic NOT ready (authorization bug)
- ⚠️ Middleware ready, controller logic needs refactoring

**Go/No-Go for Phase 4:**
- **NO-GO until:** PengajuanKegiatan.canBeEditedBy() authorization is fixed
- **Timeline:** 3-4 hours to fix blocking issues
- **Testing:** Must verify mahasiswa can create/edit pengajuan with multiple ormawas

---

**Next Step:** Get approval to proceed with Phase 4 implementation using this plan as blueprint.
