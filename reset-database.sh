#!/bin/sh
# Resets the project database: drop, recreate, import the dump, then migrate.
#
# Behaviour depends on APP_ENV (read from .env):
#   - local            : runs through Laravel Sail, no confirmation
#   - any other env    : native mysql/php binaries + explicit confirmation prompt
#
# Usage: sh reset-database.sh [--test [--from-dump]] [--no-test-clone] [--force]
#
# Locally, every user's password is replaced by "password" after the import, so
# you can log in as anyone to reproduce what they see. This never runs outside
# APP_ENV=local.
#
# Locally, a normal reset ends by cloning the freshly rebuilt database into
# "<DB_DATABASE>_testing" — the database phpunit.xml points at. The suite
# therefore runs on real data without ever touching the database you develop
# against, and can never go stale behind a reset. The clone runs entirely
# inside the MySQL container.
#
#   --test           Only refresh the test database, by cloning the current dev
#                    database. Use it when dev changed without a full reset.
#   --from-dump      With --test: rebuild the test database from db-dump.sql +
#                    migrations instead of cloning. Slower, but does not assume
#                    the dev database is in a good state.
#   --no-test-clone  Skip the test database clone at the end of a normal reset.
#   --force          Skip the confirmation prompt on a non-local environment.
#                    Never use it to save yourself a keystroke in a terminal.
#
# The test database is created as root: the `sail` MySQL user is only granted
# rights on databases it already owns, so it cannot create a new one.

set -e

DUMP_FILE="db-dump.sql"
LOCAL_PASSWORD="password"
TEST_MODE=0
FROM_DUMP=0
TEST_CLONE=1
FORCE=0

for arg in "$@"; do
    case "$arg" in
        --test) TEST_MODE=1 ;;
        --from-dump) FROM_DUMP=1 ;;
        --no-test-clone) TEST_CLONE=0 ;;
        --force) FORCE=1 ;;
        *) echo "Error: unknown option '$arg'." >&2; exit 1 ;;
    esac
done

if [ "$FROM_DUMP" = 1 ] && [ "$TEST_MODE" = 0 ]; then
    echo "Error: --from-dump only makes sense with --test (a normal reset always uses the dump)." >&2
    exit 1
fi

# --- Sanity checks ---------------------------------------------------------
if [ ! -f .env ]; then
    echo "Error: .env not found. Run this from the project root." >&2
    exit 1
fi

# Cloning reads the dev database, not the dump file.
if [ "$TEST_MODE" = 0 ] || [ "$FROM_DUMP" = 1 ]; then
    if [ ! -f "$DUMP_FILE" ]; then
        echo "Error: $DUMP_FILE not found in the current directory." >&2
        exit 1
    fi
fi

# --- Read values from .env -------------------------------------------------
env_get() {
    grep -E "^$1=" .env | head -n 1 | cut -d '=' -f 2- | tr -d '"' | tr -d "'"
}

APP_ENV=$(env_get APP_ENV)
DB_DATABASE=$(env_get DB_DATABASE)
DB_USERNAME=$(env_get DB_USERNAME)
DB_PASSWORD=$(env_get DB_PASSWORD)
DB_HOST=$(env_get DB_HOST)
[ -z "$DB_HOST" ] && DB_HOST="127.0.0.1"
BCRYPT_ROUNDS=$(env_get BCRYPT_ROUNDS)
[ -z "$BCRYPT_ROUNDS" ] && BCRYPT_ROUNDS=12

SOURCE_DATABASE="$DB_DATABASE"
TEST_DATABASE="${DB_DATABASE}_testing"

# A test database only makes sense where the suite runs: locally.
if [ "$APP_ENV" != "local" ]; then
    TEST_CLONE=0
    if [ "$TEST_MODE" = 1 ]; then
        echo "Error: --test is only supported when APP_ENV=local." >&2
        exit 1
    fi
fi

TARGET_DATABASE="$DB_DATABASE"
if [ "$TEST_MODE" = 1 ]; then
    TARGET_DATABASE="$TEST_DATABASE"
fi

if [ "$APP_ENV" = "production" ]; then
    echo "Error: refusing to reset a production database." >&2
    exit 1
fi

# --- Pick runners + guard non-local environments ---------------------------
if [ "$APP_ENV" = "local" ]; then
    # Sail exports these itself; plain `docker compose` calls warn without them.
    export WWWUSER="${WWWUSER:-$(id -u)}" WWWGROUP="${WWWGROUP:-$(id -g)}"
    MYSQL_ROOT="docker compose exec -T mysql mysql -uroot -p$DB_PASSWORD"
    SHELL_IN_MYSQL="docker compose exec -T mysql sh -c"
    PHP="docker compose exec -T laravel.test php"
    if [ "$TEST_MODE" = 1 ]; then
        MYSQL="$MYSQL_ROOT"
        ARTISAN="docker compose exec -T -u sail -e DB_DATABASE=$TARGET_DATABASE laravel.test php artisan"
    else
        MYSQL="vendor/bin/sail mysql"
        ARTISAN="vendor/bin/sail artisan"
    fi
else
    export MYSQL_PWD="$DB_PASSWORD"
    MYSQL="mysql -h $DB_HOST -u $DB_USERNAME"
    ARTISAN="php artisan"

    echo "############################################################"
    echo "#  WARNING - you are about to RESET a NON-LOCAL database"
    echo "#"
    echo "#  Environment : $APP_ENV"
    echo "#  Database    : $TARGET_DATABASE  (host: $DB_HOST)"
    echo "#"
    echo "#  This will DROP every table and re-import $DUMP_FILE."
    echo "#  This action is IRREVERSIBLE."
    echo "############################################################"

    if [ "$FORCE" = 1 ]; then
        echo "Confirmation skipped (--force)."
    else
        printf "Type the database name (%s) to confirm: " "$TARGET_DATABASE"
        read -r CONFIRM
        if [ "$CONFIRM" != "$TARGET_DATABASE" ]; then
            echo "Aborted - confirmation did not match."
            exit 1
        fi
    fi
fi

# Recreates the test database and streams the dev database into it, without
# leaving the MySQL container. `sail` cannot CREATE a database it does not own,
# hence root for the create and the grant.
clone_into_test_database() {
    echo "==> Refreshing '$TEST_DATABASE' from '$SOURCE_DATABASE'..."
    printf 'DROP DATABASE IF EXISTS `%s`; CREATE DATABASE `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n' "$TEST_DATABASE" "$TEST_DATABASE" | $MYSQL_ROOT
    printf "GRANT ALL PRIVILEGES ON \`%s\`.* TO '%s'@'%%'; FLUSH PRIVILEGES;\n" "$TEST_DATABASE" "$DB_USERNAME" | $MYSQL_ROOT
    $SHELL_IN_MYSQL "mysqldump -uroot -p$DB_PASSWORD --single-transaction --routines --triggers --no-tablespaces $SOURCE_DATABASE | mysql -uroot -p$DB_PASSWORD $TEST_DATABASE"
}

if [ "$TEST_MODE" = 1 ] && [ "$FROM_DUMP" = 0 ]; then
    clone_into_test_database
    echo "==> Done."
    exit 0
fi

# --- Reset -----------------------------------------------------------------
echo "==> Dropping and recreating '$TARGET_DATABASE'..."
printf 'DROP DATABASE IF EXISTS `%s`; CREATE DATABASE `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n' "$TARGET_DATABASE" "$TARGET_DATABASE" | $MYSQL

if [ "$TEST_MODE" = 1 ]; then
    printf "GRANT ALL PRIVILEGES ON \`%s\`.* TO '%s'@'%%'; FLUSH PRIVILEGES;\n" "$TARGET_DATABASE" "$DB_USERNAME" | $MYSQL
fi

# The dump is a phpMyAdmin export from OVH: its views and procedures carry
# DEFINER=`<ovh user>`@`%`. That user does not exist here, and creating an
# object on behalf of another user needs SET_USER_ID, which `sail` lacks. With
# the clause stripped, objects are owned by whoever imports them.
echo "==> Importing $DUMP_FILE..."
sed -E 's/DEFINER=`[^`]+`@`[^`]+`//g' "$DUMP_FILE" | $MYSQL "$TARGET_DATABASE"

echo "==> Running migrations..."
$ARTISAN migrate --force

# Only ever locally: every account gets the same known password, so you can log
# in as any user. The hash is computed once and written in a single UPDATE.
# failed_logins is cleared too, so no account stays locked from production.
if [ "$APP_ENV" = "local" ]; then
    echo "==> Setting every user's password to '$LOCAL_PASSWORD'..."
    PASSWORD_HASH=$($PHP -r "echo password_hash('$LOCAL_PASSWORD', PASSWORD_BCRYPT, ['cost' => $BCRYPT_ROUNDS]);")
    printf "UPDATE users SET password = '%s', remember_token = NULL, failed_logins = 0;\n" "$PASSWORD_HASH" | $MYSQL "$TARGET_DATABASE"

    # Production jobs left in the dump would run on the next `queue:work` and
    # send real SMS/emails to real people; production sessions are useless here.
    echo "==> Emptying production jobs and sessions..."
    printf 'TRUNCATE TABLE jobs; TRUNCATE TABLE failed_jobs; TRUNCATE TABLE sessions;\n' | $MYSQL "$TARGET_DATABASE"
fi

# The dump carries the production `cache` table with it, and the workers still
# hold a connection to the database that was just dropped.
echo "==> Clearing caches and restarting workers..."
$ARTISAN cache:clear
$ARTISAN queue:restart

if [ "$TEST_CLONE" = 1 ]; then
    clone_into_test_database
fi

echo "==> Done."
