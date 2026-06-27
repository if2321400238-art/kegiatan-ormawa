# BROWSER TESTING GUIDE: Active Ormawa Session Persistence

**Objective:** Verify that active ormawa selection persists across page refreshes, navigation, and logout/login cycles.

**Prerequisites:**
- Application running at http://localhost:8000 (or your configured URL)
- Test user created with multiple ormawa memberships
- Browser with developer console access

---

## TEST DATA SETUP

### Step 1: Create Test Data

Use Laravel Tinker to create test user with multiple ormawas:

```bash
docker exec kegiatan-app php artisan tinker

# Inside tinker:
$user = User::factory()->create(['role' => 'mahasiswa', 'nama' => 'Test Mahasiswa', 'nim' => '12345']);
$ormawa1 = Ormawa::factory()->create(['nama_ormawa' => 'BEM FT']);
$ormawa2 = Ormawa::factory()->create(['nama_ormawa' => 'PMII']);
$ormawa3 = Ormawa::factory()->create(['nama_ormawa' => 'HMI']);

# Add memberships
$user->ormawas()->attach($ormawa1->id, ['jabatan' => 'ketua', 'aktif' => true]);
$user->ormawas()->attach($ormawa2->id, ['jabatan' => 'anggota', 'aktif' => true]);
$user->ormawas()->attach($ormawa3->id, ['jabatan' => 'anggota', 'aktif' => true]);

exit
```

**Credentials:**
- NIM: `12345`
- Password: `password` (default from factory)

---

## TEST SCENARIOS

### TEST 1: First Login - Auto-Select First Ormawa

**Expected Result:**
- User logs in and is redirected to mahasiswa dashboard
- Dashboard shows 3 ormawas
- First ormawa (BEM FT) is pre-selected as active
- Session stores `active_ormawa_id` with BEM FT's ID

**Steps:**
1. Navigate to login page
2. Login with NIM: `12345`, Password: `password`
3. Should see Mahasiswa Dashboard with 3 organizations
4. Verify "BEM FT" is marked with "Active" badge/indicator
5. Open browser DevTools → Application → Cookies
6. Check `PHPSESSID` cookie and use Laravel session to inspect session data

**Pass Criteria:** ✅
- Dashboard displays all 3 ormawas
- First ormawa marked as active
- Can see organization cards

---

### TEST 2: Session Persistence - Page Refresh

**Expected Result:**
- Active ormawa selection persists when page is refreshed
- Same ormawa stays selected after F5/Cmd+R

**Steps:**
1. Start from Test 1 (logged in with BEM FT selected)
2. Press F5 or Cmd+R to refresh page
3. Wait for page to reload completely
4. Check active ormawa indicator

**Pass Criteria:** ✅
- BEM FT is still marked as "Active"
- Dashboard doesn't redirect or reset
- No flash message about invalid session

---

### TEST 3: Change Active Ormawa

**Expected Result:**
- User can click on different ormawa and change selection
- New selection becomes active immediately
- Session updates with new `active_ormawa_id`

**Steps:**
1. Start from Test 2 (logged in with BEM FT active)
2. Locate PMII ormawa card or selector
3. Click on PMII to set it as active
4. Verify PMII now shows "Active" badge
5. BEM FT no longer shows "Active" badge

**Pass Criteria:** ✅
- PMII marked as "Active"
- BEM FT no longer marked as "Active"
- Dropdown/selector updated if shown as dropdown

---

### TEST 4: Session Persistence After Change

**Expected Result:**
- Changed selection persists across page refreshes
- PMII remains active after F5

**Steps:**
1. Start from Test 3 (PMII now selected as active)
2. Press F5 or Cmd+R to refresh
3. Check active ormawa

**Pass Criteria:** ✅
- PMII still marked as "Active"
- No reset to BEM FT
- Session maintained

---

### TEST 5: Navigation to Protected Routes

**Expected Result:**
- User can navigate to routes that require active ormawa (e.g., pengajuan creation)
- Active ormawa middleware allows access
- Active ormawa context is available in those routes

**Steps:**
1. Start from Test 4 (PMII active)
2. Navigate to "Create Pengajuan" or another protected route
3. Route should load successfully
4. Active ormawa should be PMII (check if form pre-fills ormawa field)

**Pass Criteria:** ✅
- No 403 Forbidden error
- Route loads successfully
- Active ormawa context available

---

### TEST 6: Logout and Login Again

**Expected Result:**
- After logout, active ormawa session is cleared
- On login, first ormawa auto-selected again
- No cross-session contamination

**Steps:**
1. Start from Test 5 (PMII active)
2. Click "Logout" button
3. Confirm logout (should be redirected to login page)
4. Login again with same credentials
5. Check which ormawa is active

**Pass Criteria:** ✅
- First ormawa (BEM FT) selected automatically on re-login
- Not PMII (the previously active one)
- No session leakage between logins

---

### TEST 7: Multiple Tabs - Session Sync

**Expected Result:**
- If user has 2 browser tabs open with same session
- Changing active ormawa in Tab A
- Might not reflect immediately in Tab B (normal browser behavior)
- But after refresh in Tab B, should show new selection

**Steps:**
1. Open mahasiswa dashboard in Tab A (BEM FT active)
2. Open mahasiswa dashboard in Tab B (should also show BEM FT active)
3. In Tab A: Change to PMII (set PMII as active)
4. In Tab B: Refresh page
5. Check which ormawa is active in Tab B

**Pass Criteria:** ✅
- After refresh, Tab B shows PMII as active
- Not BEM FT

---

### TEST 8: Membership Removal - Session Validation

**Expected Result:**
- If user's membership is removed while session is active
- EnsureActiveOrmawa middleware detects this
- User is redirected to dashboard with error message
- Session is cleared

**Steps:**
1. Start from Test 6 (logged in with PMII active)
2. In another terminal or admin panel, remove user's PMII membership
3. In browser: Refresh page or navigate to protected route
4. Check if redirected to dashboard with error message

**Pass Criteria:** ✅
- Redirected to mahasiswa dashboard
- Error message: "Keanggotaan Anda di organisasi tersebut telah dihapus" or similar
- Session cleared, no active ormawa shown
- Next login auto-selects first membership

---

### TEST 9: Invalid Session Cookie

**Expected Result:**
- If session cookie is manually deleted/corrupted
- System handles gracefully
- User redirected or shown appropriate message

**Steps:**
1. Start from Test 8 (logged in)
2. Open DevTools → Application → Cookies
3. Delete the `PHPSESSID` cookie
4. Refresh page or navigate
5. Observe behavior

**Pass Criteria:** ✅
- User redirected to login
- No 500 error
- Graceful error handling

---

---

## VERIFICATION CHECKLIST

### Session Data Verification

**Using Laravel Tinker during tests:**
```bash
docker exec kegiatan-app php artisan tinker

# Inside tinker:
session(['active_ormawa_id' => 5])  # Set
session('active_ormawa_id')          # Get
```

**Using Browser DevTools:**
1. F12 → Application tab
2. Cookies → Select localhost:8000
3. Look for `PHPSESSID` cookie
4. Session data stored server-side, but PHPSESSID proves session exists

---

### Dashboard Visual Indicators

**What to look for:**
- ✅ Active ormawa card has badge "Active" or highlighted
- ✅ Inactive ormawas show normal appearance
- ✅ Dropdown selector shows "Currently Active: [Name]"
- ✅ Statistics shown for active ormawa only (if implemented)

**Example UI:**
```
┌─────────────────────────────────┐
│  My Organizations              │
│─────────────────────────────────│
│                                  │
│  Ormawa Aktif: BEM FT           │ ← Session active
│  ┌──────────────────────────┐    │
│  │ BEM FT  [Active] ✓       │    │ ← First is active
│  │ Role: Ketua              │    │
│  │ Status: Active           │    │
│  │ Set as Active [button]   │    │
│  └──────────────────────────┘    │
│                                  │
│  ┌──────────────────────────┐    │
│  │ PMII                     │    │ ← Inactive
│  │ Role: Anggota            │    │
│  │ Status: Active           │    │
│  │ Set as Active [button]   │    │
│  └──────────────────────────┘    │
│                                  │
│  ┌──────────────────────────┐    │
│  │ HMI                      │    │ ← Inactive
│  │ Role: Anggota            │    │
│  │ Status: Active           │    │
│  │ Set as Active [button]   │    │
│  └──────────────────────────┘    │
└─────────────────────────────────┘
```

---

## TROUBLESHOOTING

### Issue: Active Ormawa Not Showing

**Possible Causes:**
1. User doesn't have 3 memberships (check test data setup)
2. Dashboard view not rendering active indicator correctly
3. Session not being set by setActiveOrmawa endpoint

**Debug Steps:**
1. Check user memberships: `$user->ormawas()->pluck('nama_ormawa')`
2. Check session: `session('active_ormawa_id')`
3. Check browser console for JavaScript errors

---

### Issue: Session Not Persisting After Refresh

**Possible Causes:**
1. Session driver not configured correctly (should be 'file' or 'database')
2. Session middleware not active on routes
3. Browser cookies disabled

**Debug Steps:**
1. Check `config/session.php` - should be `DRIVER=file` or `database`
2. Check middleware in `bootstrap/app.php`
3. Verify cookies not blocked in browser settings

---

### Issue: Middleware Blocking Valid Session

**Possible Causes:**
1. EnsureActiveOrmawa middleware too strict
2. User membership actually removed
3. Session data corrupted

**Debug Steps:**
1. Check middleware logic in `app/Http/Middleware/EnsureActiveOrmawa.php`
2. Verify user still member: `$user->ormawas()->where('ormawa_id', session('active_ormawa_id'))->exists()`

---

## SUCCESS CRITERIA

✅ **All tests pass if:**
1. First login auto-selects first ormawa
2. Selection persists across page refreshes
3. User can manually change active ormawa
4. Changed selection persists
5. Logout clears session
6. Re-login starts fresh with first ormawa
7. Removed membership is detected and handled gracefully
8. Invalid sessions fail gracefully
9. Protected routes accept valid sessions
10. No 500 errors or PHP warnings

---

## NOTES

- **Performance:** Session checks should be fast (<10ms)
- **Scalability:** Session storage should handle many concurrent users
- **Security:** Session hijacking protection via CSRF tokens (already in Laravel Breeze)
- **UX:** User should never see "session expired" within same browser session

---

## NEXT STEPS AFTER TESTING

After browser testing validation:
1. ✅ Audit trail implementation complete
2. ✅ Active ormawa session working
3. ✅ Middleware protecting routes
4. 📋 Begin Phase 4: Pengajuan integration with active_ormawa_id
   - Fix authorization (canBeEditedBy)
   - Update controller logic to use session
   - Add integration tests
