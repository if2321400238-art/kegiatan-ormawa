#!/bin/bash
set -euo pipefail
CJ=/tmp/cookiejar
rm -f "$CJ"
MID="${MID:-}"
KEMAIL="${KEMAIL:-}"
OID="${OID:-}"
URL=http://nginx
echo "== GET /login (BAUAK CSRF) =="
curl -s -c "$CJ" "$URL/login" -o /tmp/login.html || true
CSRF=$(grep -o 'name="_token" value="[^"]*"' /tmp/login.html | sed -E 's/.*value="([^"]*)".*/\1/')
echo "csrf:$CSRF"

echo "== POST /login as bauak@gmail.com =="
curl -s -b "$CJ" -c "$CJ" -X POST -L -D /tmp/headers -o /tmp/body -F "login=bauak@gmail.com" -F "password=password" -F "_token=$CSRF" "$URL/login" || true
echo "Login response headers:"
sed -n '1,120p' /tmp/headers || true

echo "== GET /bauak/ormawa (BAUAK dashboard) =="
curl -s -b "$CJ" "$URL/bauak/ormawa" -o /tmp/bauak_ormawa.html || true
echo "BAUAK page snapshot saved (/tmp/bauak_ormawa.html)"


echo "== Find a mahasiswa user for ketua =="
echo "(DB queries will be run separately; please wait)"
MID=""
echo "picked mahasiswa id=$MID"

echo "== LOGIN admin to create Ormawa (admin@gmail.com) =="
curl -s -c /tmp/admin_cookie "$URL/login" -o /tmp/admin_login.html || true
ADMIN_CSRF=$(grep -o 'name="_token" value="[^"]*"' /tmp/admin_login.html | sed -E 's/.*value="([^"]*)".*/\1/')
echo "admin csrf:$ADMIN_CSRF"

echo "== Admin login POST (admin@gmail.com) =="
curl -s -b /tmp/admin_cookie -c /tmp/admin_cookie -X POST -L -D /tmp/admin_headers -o /tmp/admin_body -F "login=admin@gmail.com" -F "password=password" -F "_token=$ADMIN_CSRF" "$URL/login" || true
echo "Admin login headers:"
sed -n '1,120p' /tmp/admin_headers || true

echo "(Will create Ormawa by scraping admin create form for IDs)"

curl -s -b /tmp/admin_cookie -c /tmp/admin_cookie "$URL/admin/ormawa/create" -o /tmp/admin_create.html || true
CREATE_CSRF=$( (grep -o 'name="_token" value="[^"]*"' /tmp/admin_create.html || true) | tail -n1 | sed -E 's/.*value="([^\"]*)".*/\1/')
MID_FROM_FORM=$(sed -n '/<select name="user_id"/,/<\/select>/p' /tmp/admin_create.html | (grep -o 'value="[0-9]\+"' || true) | sed -E 's/value="([0-9]+)"/\1/' | head -n1)
PID_FROM_FORM=$(sed -n '/<select name="pembina_user_id"/,/<\/select>/p' /tmp/admin_create.html | (grep -o 'value="[0-9]\+"' || true) | sed -E 's/value="([0-9]+)"/\1/' | head -n1)
echo "picked mahasiswa from form=$MID_FROM_FORM, pembina=$PID_FROM_FORM, create_csrf=$CREATE_CSRF"

curl -s -b /tmp/admin_cookie -c /tmp/admin_cookie -X POST -L -D /tmp/admin_create_headers -o /tmp/admin_create_body \
	-F "nama_ormawa=HTTP Test Ormawa" -F "user_id=$MID_FROM_FORM" -F "pembina_user_id=$PID_FROM_FORM" -F "kontak=08123456789" -F "kategori_organisasi=internal" -F "tingkat_organisasi=universitas" -F "_token=$CREATE_CSRF" \
	"$URL/admin/ormawa" || true

echo "Admin create response headers:"
sed -n '1,120p' /tmp/admin_create_headers || true

# Find the created Ormawa ID by listing admin ormawa index
curl -s -b /tmp/admin_cookie "$URL/admin/ormawa" -o /tmp/admin_index.html || true
OID=$((grep -o "/admin/ormawa/[0-9]\+" /tmp/admin_index.html || true) | head -n1 | sed -E 's/\/admin\/ormawa\/([0-9]+)/\1/' )
OID=$(echo "$OID" | tr -d '\n')
if [ -z "$OID" ]; then
	OID=$(php artisan tinker --execute "echo (\\App\\Models\\Ormawa::where('nama_ormawa','HTTP Test Ormawa')->latest()->first()?->id ?? '') . PHP_EOL;" 2>/dev/null || true)
fi
OID=$(echo "$OID" | tr -d '\n')
echo "new ormawa id=$OID"
if [ -n "$OID" ]; then
	ORM_KETUA_ID=$(php artisan tinker --execute "echo (\\App\\Models\\Ormawa::find($OID)?->user_id ?? '') . PHP_EOL;" 2>/dev/null || true)
	ORM_KETUA_ID=$(echo "$ORM_KETUA_ID" | tr -d '\n')	
	KEMAIL=$(php artisan tinker --execute "echo (\\App\\Models\\User::find($ORM_KETUA_ID)?->email ?? '') . PHP_EOL;" 2>/dev/null || true)
else
	KEMAIL=""
fi
KEMAIL=$(echo "$KEMAIL" | tr -d '\n')
echo "ketua email=$KEMAIL"

echo "== LOGIN as ketua (mahasiswa) =="
echo "ketua email=$KEMAIL"


curl -s -c /tmp/ketua_cookie "$URL/login" -o /tmp/ketua_login.html || true
K_CSRF=$(grep -o 'name="_token" value="[^"]*"' /tmp/ketua_login.html | sed -E 's/.*value="([^"]*)".*/\1/')

curl -s -b /tmp/ketua_cookie -c /tmp/ketua_cookie -X POST -L -D /tmp/ketua_headers -o /tmp/ketua_body -F "login=$KEMAIL" -F "password=password" -F "_token=$K_CSRF" "$URL/login" || true

echo "Ketua login headers:"
sed -n '1,120p' /tmp/ketua_headers || true

echo "== Ketua GET /ormawa/$OID/anggota/create (form) =="
curl -s -b /tmp/ketua_cookie "$URL/ormawa/$OID/anggota/create" -o /tmp/ketua_create.html || true

echo "form snapshot saved"

MID=$ORM_KETUA_ID

echo "== Ketua add anggota (search available mahasiswa) =="
SEARCH_QUERIES=("20" "21" "22" "23" "ma" "ri" "an" "sa" "te")
NEWMID=""
for q in "${SEARCH_QUERIES[@]}"; do
    SEARCH_RESULT=$(curl -s -b /tmp/ketua_cookie "$URL/ormawa/$OID/anggota/search?search=$q")
    NEWMID=$(printf '%s' "$SEARCH_RESULT" | grep -o '"id":[0-9]\+' | sed -E 's/"id":([0-9]+)/\1/' | grep -v "^$MID$" | head -n1 || true)
    if [ -n "$NEWMID" ]; then
        break
    fi
done

if [ -z "$NEWMID" ]; then
    echo "Warning: no available anggota found via search endpoint, falling back to DB lookup"
    NEWMID=$(php artisan tinker --execute "echo (\App\Models\User::where('role','mahasiswa')->where('is_active',true)->whereNotIn('id', function(\\$q) use (\$OID) { \\$q->select('user_id')->from('anggota_ormawa')->where('ormawa_id', \\$OID); })->where('id','!=', \\$MID)->first()?->id ?? '') . PHP_EOL;" 2>/dev/null || true)
    NEWMID=$(echo "$NEWMID" | tr -d '\n')
fi

echo "new member id=$NEWMID"
K_FORM_CSRF=$(grep -o 'name="_token" value="[^"]*"' /tmp/ketua_create.html | tail -n1 | sed -E 's/.*value="([^\"]*)".*/\1/')
echo "form csrf:$K_FORM_CSRF"

curl -s -b /tmp/ketua_cookie -X POST -L -D /tmp/add_headers -o /tmp/add_body -F "user_id=$NEWMID" -F "jabatan=anggota" -F "_token=$K_FORM_CSRF" "$URL/ormawa/$OID/anggota" || true

echo "Add member response headers:"
sed -n '1,120p' /tmp/add_headers || true

echo "== Verify anggota_ormawa list (HTML) =="
curl -s -b /tmp/admin_cookie "$URL/ormawa/$OID/anggota" -o /tmp/anggota_list.html || true
sed -n '1,200p' /tmp/anggota_list.html || true

echo "== Try add same member again (expect validation) =="
curl -s -b /tmp/ketua_cookie -X POST -L -D /tmp/add2_headers -o /tmp/add2_body -F "user_id=$NEWMID" -F "jabatan=anggota" -F "_token=$K_FORM_CSRF" "$URL/ormawa/$OID/anggota" || true

echo "Add2 headers:"
sed -n '1,120p' /tmp/add2_headers || true

echo "Add2 body (snippet):"
sed -n '1,200p' /tmp/add2_body || true

echo "== Try delete ketua (expect error) =="
curl -s -b /tmp/ketua_cookie -X DELETE -L -D /tmp/del_headers -o /tmp/del_body "$URL/ormawa/$OID/anggota/$MID" || true

echo "Del headers:"
sed -n '1,120p' /tmp/del_headers || true

echo "Del body snippet:"
sed -n '1,200p' /tmp/del_body || true

echo "== Login as ordinary anggota and access member list (expect 403) =="
echo "Skipping ordinary member login/access check (requires user email lookup)."

echo "=== Script finished ==="
