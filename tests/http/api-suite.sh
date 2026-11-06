#!/usr/bin/env bash
# API test suite (roles and permissions, IDOR, errors, uniqueness, notifications, reviews).
# Needs Apache + MySQL running and the dev seed. Run from Git Bash: bash tests/http/api-suite.sh
# Prints "PASS|FAIL label expected actual". Uses throwaway users suffixed with $RUN.
set -u
B=${STUDIUM_URL:-http://localhost/studium_reservation_management}
APP_DIR=$(cd "$(dirname "$0")/../.." && pwd)
DIR=$(mktemp -d); trap 'rm -rf "$DIR"' EXIT
PW=$(grep '^SEED_PASSWORD=' "$APP_DIR/.env" | tail -1 | cut -d= -f2-)
SQL() { "${MYSQL_BIN:-/c/xampp/mysql/bin/mysql.exe}" -uroot -N "${DB_NAME:-studium_reservation_management}" -e "$1"; }
RUN=$(date +%s | tail -c 6)
PASS=0; FAIL=0

token() { curl -s -b "$1" -c "$1" "$B/stadiums" | grep -o 'name="csrf-token" content="[^"]*"' | head -1 | sed 's/.*content="//; s/"$//'; }
req() { # jar method path [json] -> sets CODE and BODY
    local jar=$1 method=$2 path=$3 body=${4:-} t
    t=$(token "$jar")
    local out; out=$(curl -s -b "$jar" -c "$jar" -X "$method" -H "Content-Type: application/json" -H "Accept: application/json" \
        -H "X-CSRF-Token: $t" -w '\n%{http_code}' ${body:+--data "$body"} "$B$path")
    CODE=${out##*$'\n'}; BODY=${out%$'\n'*}
}
err() { echo "$BODY" | grep -o '"code":"[A-Z_]*"' | head -1 | cut -d'"' -f4; }
check() { # label expected actual
    if [ "$2" = "$3" ]; then PASS=$((PASS+1)); echo "PASS | $1 | $2"; else FAIL=$((FAIL+1)); echo "FAIL | $1 | expected $2 | got $3"; fi
}
expect() { # label jar method path body expectedStatus [expectedCode]
    req "$2" "$3" "$4" "${5:-}"
    local actual="$CODE"; [ -n "${7:-}" ] && actual="$CODE $(err)"
    check "$1" "$6${7:+ $7}" "$actual"
}
page() { curl -s -o /dev/null -w '%{http_code}' -b "$1" "$B$2"; }
login() { rm -f "$1"; req "$1" POST /api/auth/login "{\"identifier\":\"$2\",\"password\":\"$3\"}"; }

G=$DIR/guest.jar; C=$DIR/c.jar; C2=$DIR/c2.jar; M=$DIR/m.jar; M2=$DIR/m2.jar; A=$DIR/a.jar
STD=$(SQL "SELECT code FROM stadiums WHERE name='Seef Arena'")
PADEL=$(SQL "SELECT code FROM stadiums WHERE name='Riffa Padel Club'")

echo "=== 3. Errors / auth"
expect "login wrong password" $G POST /api/auth/login '{"identifier":"customer","password":"nope1234"}' 401 UNAUTHENTICATED
expect "login case-insensitive (CUSTOMER)" $C POST /api/auth/login "{\"identifier\":\"CUSTOMER\",\"password\":\"$PW\"}" 200
login $M manager "$PW"; login $A admin "$PW"
check "no CSRF token" 403 "$(curl -s -o /dev/null -w '%{http_code}' -X POST -H 'Accept: application/json' $B/api/auth/login)"
curl -s -o /dev/null -w '' -X POST "$B/api/auth/login" ; check "unauthenticated API -> 401" 401 "$(curl -s -o /dev/null -w '%{http_code}' -H 'Accept: application/json' "$B/api/notifications")"
check "unknown route 404" 404 "$(page $G /does-not-exist)"
check "guest /dashboard redirects" 302 "$(page $G /dashboard)"
expect "validation shape" $G POST /api/auth/register '{"fullName":"x"}' 422 VALIDATION_FAILED
check "no stack trace in 422 body" 0 "$(echo "$BODY" | grep -c 'Stack\|\.php')"

echo "=== Uniqueness (case-insensitive) + mass assignment"
expect "register customer2" $C2 POST /api/auth/register "{\"fullName\":\"Second Player\",\"username\":\"Player$RUN\",\"email\":\"Player$RUN@Studium.TEST\",\"phone\":\"+973 66$RUN 1\",\"password\":\"Passw0rd$RUN\",\"passwordConfirmation\":\"Passw0rd$RUN\",\"role\":\"admin\"}" 201
check "stored username lower-case" "player$RUN" "$(SQL "SELECT username FROM users WHERE email='player$RUN@studium.test'")"
check "stored phone without spaces" "+97366${RUN}1" "$(SQL "SELECT phone FROM users WHERE email='player$RUN@studium.test'")"
check "mass assignment: role stays customer" customer "$(SQL "SELECT role FROM users WHERE email='player$RUN@studium.test'")"
expect "duplicate username other case" $G POST /api/auth/register "{\"fullName\":\"Dup User\",\"username\":\"PLAYER$RUN\",\"email\":\"x$RUN@x.test\",\"phone\":\"+97377${RUN}2\",\"password\":\"Passw0rd1\",\"passwordConfirmation\":\"Passw0rd1\"}" 409 DUPLICATE_VALUE
expect "duplicate email other case" $G POST /api/auth/register "{\"fullName\":\"Dup User\",\"username\":\"dup$RUN\",\"email\":\"PLAYER$RUN@studium.test\",\"phone\":\"+97377${RUN}2\",\"password\":\"Passw0rd1\",\"passwordConfirmation\":\"Passw0rd1\"}" 409 DUPLICATE_VALUE
expect "duplicate phone other format" $G POST /api/auth/register "{\"fullName\":\"Dup User\",\"username\":\"dup$RUN\",\"email\":\"dup$RUN@x.test\",\"phone\":\"+973-66$RUN-1\",\"password\":\"Passw0rd1\",\"passwordConfirmation\":\"Passw0rd1\"}" 409 DUPLICATE_VALUE
check "DB rejects case-only duplicate username" 1 "$(SQL "INSERT INTO users (code,role,full_name,username,email,phone,password_hash,created_at,updated_at) VALUES ('USR-ZZZZ$RUN','customer','x','CUSTOMER','z$RUN@z.test','+9739${RUN}9','x',NOW(),NOW())" 2>&1 | grep -c 'Duplicate')"
check "code lookup case-insensitive" 200 "$(page $G /stadiums/$(echo $STD | tr 'A-Z' 'a-z'))"
expect "profile cannot change role" $C2 PUT /api/profile "{\"fullName\":\"Second Player\",\"email\":\"player$RUN@studium.test\",\"phone\":\"+97366${RUN}1\",\"role\":\"admin\"}" 200
check "  role still customer" customer "$(SQL "SELECT role FROM users WHERE email='player$RUN@studium.test'")"

echo "=== 9. Roles and permissions matrix"
for p in /settings /users /cities /manage/stadiums /manage/stadiums/create /accounting/expenses /accounting/reports; do check "customer GET $p" 403 "$(page $C $p)"; done
for p in /settings /users /cities /manage/stadiums/create; do check "manager GET $p" 403 "$(page $M $p)"; done
for p in /manage/stadiums /accounting/expenses /accounting/reports /notifications /reservations; do check "manager GET $p" 200 "$(page $M $p)"; done
for p in /settings /users /cities /manage/stadiums/create /notifications; do check "admin GET $p" 200 "$(page $A $p)"; done
expect "customer -> PUT settings" $C PUT /api/settings '{}' 403 FORBIDDEN
expect "manager -> POST city" $M POST /api/cities '{"name":"X","countryCode":"BH"}' 403 FORBIDDEN
expect "manager -> book" $M POST /api/reservations "{\"stadiumCode\":\"$STD\",\"date\":\"$(date -d '+9 day' +%F)\",\"startTime\":\"10:00\",\"durationMinutes\":60}" 403 FORBIDDEN
expect "customer -> review w/o playing" $C2 POST /api/stadiums/$STD/reviews '{"rating":5,"comment":"Great pitch"}' 403 REVIEW_NOT_ALLOWED
expect "manager -> write review" $M POST /api/stadiums/$STD/reviews '{"rating":5,"comment":"Great pitch"}' 403 FORBIDDEN
expect "guest -> read reviews API" $G GET /api/stadiums/$STD/reviews '' 401 UNAUTHENTICATED

echo "=== Second manager (scope) + IDOR"
expect "admin creates manager2" $A POST /api/users "{\"fullName\":\"Other Manager\",\"username\":\"mgr$RUN\",\"email\":\"mgr$RUN@studium.test\",\"phone\":\"+97355${RUN}3\",\"role\":\"manager\",\"isActive\":\"1\",\"password\":\"Passw0rd$RUN\",\"passwordConfirmation\":\"Passw0rd$RUN\"}" 201
login $M2 "mgr$RUN" "Passw0rd$RUN"
check "manager2 edit other's stadium page" 404 "$(page $M2 /manage/stadiums/$STD/edit)"
expect "manager2 toggle other's stadium" $M2 POST /api/stadiums/$STD/toggle '{}' 404 NOT_FOUND
D=$(date -d '+12 day' +%F)
expect "customer books (for IDOR)" $C POST /api/reservations "{\"stadiumCode\":\"$STD\",\"date\":\"$D\",\"startTime\":\"12:00\",\"durationMinutes\":60}" 201
RSV=$(SQL "SELECT r.code FROM reservations r JOIN users u ON u.id=r.customer_id WHERE u.username='customer' ORDER BY r.id DESC LIMIT 1")
check "customer2 views customer's booking" 404 "$(page $C2 /reservations/$RSV)"
expect "customer2 cancels customer's booking" $C2 POST /api/reservations/$RSV/cancel '{}' 404 NOT_FOUND
expect "manager2 confirms other stadium booking" $M2 POST /api/reservations/$RSV/confirm '{}' 404 NOT_FOUND
expect "manager2 records payment elsewhere" $M2 POST /api/reservations/$RSV/payments "{\"type\":\"payment\",\"method\":\"cash\",\"amount\":1,\"paidAt\":\"$(date '+%F %H:%M')\"}" 404 NOT_FOUND

echo "=== Notifications"
N_M=$(SQL "SELECT COUNT(*) FROM notifications n JOIN users u ON u.id=n.user_id WHERE u.username='manager' AND n.template='reservation.requested' AND n.related_code='$RSV'")
check "manager notified of request" 1 "$N_M"
expect "manager confirms" $M POST /api/reservations/$RSV/confirm '{}' 200
check "customer notified of confirm" 1 "$(SQL "SELECT COUNT(*) FROM notifications n JOIN users u ON u.id=n.user_id WHERE u.username='customer' AND n.template='reservation.confirmed' AND n.related_code='$RSV'")"
check "actor not notified of own action" 0 "$(SQL "SELECT COUNT(*) FROM notifications n JOIN users u ON u.id=n.user_id WHERE u.username='manager' AND n.template='reservation.confirmed' AND n.related_code='$RSV'")"
req $C GET /api/notifications; check "feed returns unreadCount" 1 "$(echo "$BODY" | grep -c '"unreadCount"')"
NCODE=$(SQL "SELECT n.code FROM notifications n JOIN users u ON u.id=n.user_id WHERE u.username='customer' ORDER BY n.id DESC LIMIT 1")
expect "customer2 marks customer's notification" $C2 POST /api/notifications/$NCODE/read '{}' 404 NOT_FOUND
expect "customer marks own notification" $C POST /api/notifications/$NCODE/read '{}' 200
check "  read_at set" 1 "$(SQL "SELECT COUNT(*) FROM notifications WHERE code='$NCODE' AND read_at IS NOT NULL")"
expect "mark all read" $C POST /api/notifications/read-all '{}' 200
check "  unread now 0" 0 "$(SQL "SELECT COUNT(*) FROM notifications n JOIN users u ON u.id=n.user_id WHERE u.username='customer' AND read_at IS NULL")"
expect "customer cancels" $C POST /api/reservations/$RSV/cancel '{"reason":"Rain"}' 200
check "manager notified of cancel w/ reason" 1 "$(SQL "SELECT COUNT(*) FROM notifications n JOIN users u ON u.id=n.user_id WHERE u.username='manager' AND n.template='reservation.cancelled' AND n.related_code='$RSV' AND n.body LIKE '%Reason: Rain%'")"

echo "=== Reviews (once per customer, only after playing)"
CID=$(SQL "SELECT id FROM users WHERE email='player$RUN@studium.test'"); SID=$(SQL "SELECT id FROM stadiums WHERE code='$STD'")
SQL "INSERT INTO reservations (code,stadium_id,customer_id,start_at,end_at,booking_unit,unit_count,unit_price,duration_minutes,total_amount,status,payment_status,created_at,updated_at) VALUES ('RSV-PLAY$RUN',$SID,$CID,DATE_SUB(NOW(),INTERVAL 3 DAY),DATE_SUB(NOW(),INTERVAL 3 DAY)+INTERVAL 1 HOUR,'hour',1,25,60,25,'completed','paid',NOW(),NOW())"
BEFORE=$(SQL "SELECT rating_count FROM stadiums WHERE id=$SID")
NOTE_BEFORE=$(SQL "SELECT COUNT(*) FROM notifications n JOIN users u ON u.id=n.user_id WHERE u.username='manager' AND n.template='review.created'")
expect "review after playing" $C2 POST /api/stadiums/$STD/reviews '{"rating":4,"comment":"Good  lights,   nice turf"}' 201
expect "second review blocked" $C2 POST /api/stadiums/$STD/reviews '{"rating":5,"comment":"Again"}' 409 REVIEW_ALREADY_EXISTS
check "rating_count +1" $((BEFORE+1)) "$(SQL "SELECT rating_count FROM stadiums WHERE id=$SID")"
check "comment whitespace normalized" "Good lights, nice turf" "$(SQL "SELECT comment FROM reviews WHERE customer_id=$CID")"
expect "invalid rating 6" $C POST /api/stadiums/$STD/reviews '{"rating":6,"comment":"x"}' 422 VALIDATION_FAILED
check "manager notified of review" $((NOTE_BEFORE+1)) "$(SQL "SELECT COUNT(*) FROM notifications n JOIN users u ON u.id=n.user_id WHERE u.username='manager' AND n.template='review.created'")"
REV=$(SQL "SELECT code FROM reviews WHERE customer_id=$CID")
expect "author cannot delete" $C2 DELETE /api/reviews/$REV '' 404 NOT_FOUND
expect "manager2 cannot moderate other stadium" $M2 DELETE /api/reviews/$REV '' 404 NOT_FOUND
req $C GET /api/stadiums/$STD/reviews; check "signed-in reads reviews" 200 "$CODE"
expect "manager moderates" $M DELETE /api/reviews/$REV '' 200
check "rating_count back" "$BEFORE" "$(SQL "SELECT rating_count FROM stadiums WHERE id=$SID")"
expect "re-review after removal still blocked" $C2 POST /api/stadiums/$STD/reviews '{"rating":5,"comment":"Again please"}' 409 REVIEW_ALREADY_EXISTS

echo "=== Guest UI locks"
HTML=$(curl -s "$B/stadiums/$STD")
check "guest sees booking lock" 1 "$(echo "$HTML" | grep -c 'You must register first')"
check "guest sees reviews lock" 1 "$(echo "$HTML" | grep -c 'Sign in to read reviews')"
check "guest sees no review text" 0 "$(echo "$HTML" | grep -c 'review__text')"
check "register keeps next" 1 "$(curl -s "$B/register?next=/stadiums/$STD" | grep -c "value=\"/stadiums/$STD\"")"
check "open redirect blocked" 1 "$(curl -s "$B/login?next=//evil.example" | grep -c 'value="/dashboard"')"

echo "=== Totals: $PASS passed, $FAIL failed"
exit $((FAIL > 0))
