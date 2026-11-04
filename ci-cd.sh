#!/bin/sh
# Lance le pipeline local de maintenance CI/CD via Laravel Sail :
#   1. composer update + npm update   (met tout à jour dans les contraintes)
#   2. Liste ce qui reste obsolète (versions majeures bloquées par les
#      contraintes) dans un menu shell ; on choisit quoi monter (numéros,
#      'a' = tout, vide = rien). Les paquets choisis sont montés avec
#      `composer require <pkg>:^latest` / `npm install <pkg>@latest`
#      (require/require-dev est conservé), ce qui modifie composer.json/package.json.
#   3. artisan boost:update           (rafraîchit les guidelines Laravel Boost)
#   4. artisan ide-helper:models      (régénère les PHPDocs des modèles)
#   5. pint                           (formatage PHP)
#   6. vite build                     (vérification front - informatif)
#   7. phpstan + pest                 (vérification back - informatif)
#   8. composer audit + npm audit     (failles connues - informatif)
#
# Sécurité : composer.json, composer.lock, package.json et package-lock.json sont
# sauvegardés avant toute modification des dépendances. Si une mise à jour échoue,
# ils sont restaurés et les dépendances réinstallées : le projet revient à son
# état d'avant le lancement. Les vérifications (étapes 6 à 8) ne restaurent
# jamais rien : elles se contentent de signaler.
#
# DÉVELOPPEMENT LOCAL UNIQUEMENT - refuse de tourner si APP_ENV n'est pas local.
#
# Utilisation :
#   sh ci-cd.sh
#
# Les couleurs sont désactivées si la sortie n'est pas un terminal ou si NO_COLOR est défini.

set -e

# --- Couleurs et affichage -------------------------------------------------
if [ -t 1 ] && [ -z "${NO_COLOR:-}" ] && [ "${TERM:-}" != "dumb" ]; then
    ESC=$(printf '\033')
    C_RESET="${ESC}[0m"; C_BOLD="${ESC}[1m"; C_DIM="${ESC}[2m"
    C_RED="${ESC}[31m"; C_GREEN="${ESC}[32m"; C_YELLOW="${ESC}[33m"
    C_BLUE="${ESC}[34m"; C_MAGENTA="${ESC}[35m"; C_CYAN="${ESC}[36m"
else
    C_RESET=""; C_BOLD=""; C_DIM=""
    C_RED=""; C_GREEN=""; C_YELLOW=""; C_BLUE=""; C_MAGENTA=""; C_CYAN=""
fi

section() { printf '\n%s%s┈┈ %s %s┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈%s\n' "$C_BOLD" "$C_MAGENTA" "$1" "$C_DIM" "$C_RESET"; }
step()    { printf '%s%s▶%s %s\n' "$C_BOLD" "$C_CYAN" "$C_RESET" "$1"; }
ok()      { printf '%s✓%s %s\n' "$C_GREEN" "$C_RESET" "$1"; }
warn()    { printf '%s⚠%s %s\n' "$C_YELLOW" "$C_RESET" "$1"; }
err()     { printf '%s✗ %s%s\n' "$C_BOLD$C_RED" "$1" "$C_RESET" >&2; }
hint()    { printf '%s  %s%s\n' "$C_DIM" "$1" "$C_RESET"; }

# Lance "$@" en arrière-plan, affiche un spinner braille pendant l'exécution
# (seulement dans un terminal), stocke stdout+stderr dans $_CAP_OUT et renvoie
# le code de sortie de la commande. Utilisé par les helpers run_* ci-dessous.
_run_capture() {
    _cap_file="$WORK/cmd.out"
    "$@" > "$_cap_file" 2>&1 &
    _cap_pid=$!
    if [ -t 1 ]; then
        _si=0
        while kill -0 "$_cap_pid" 2>/dev/null; do
            case $_si in
                0) _f='⠋' ;; 1) _f='⠙' ;; 2) _f='⠹' ;; 3) _f='⠸' ;; 4) _f='⠼' ;;
                5) _f='⠴' ;; 6) _f='⠦' ;; 7) _f='⠧' ;; 8) _f='⠇' ;; 9) _f='⠏' ;;
            esac
            _si=$(( (_si + 1) % 10 ))
            printf '\r%s%s%s en cours...' "$C_CYAN" "$_f" "$C_RESET"
            sleep 0.1
        done
        printf '\r\033[K'
    fi
    _cap_rc=0
    wait "$_cap_pid" || _cap_rc=$?
    # Retire les avertissements WWWUSER/WWWGROUP de docker compose, sans intérêt ici.
    _CAP_OUT=$(grep -v -e '^time=.*level=warning' "$_cap_file" || true)
    return "$_cap_rc"
}

# Lance une commande Composer en conservant son code de sortie, pour que la
# restauration se déclenche en cas d'échec. En cas de succès, seules les lignes
# utiles sont affichées (opérations sur les paquets, avertissements, dépréciations).
# En cas d'échec, toute la sortie est affichée pour voir l'erreur.
run_composer() {
    _rc=0
    _run_capture "$@" || _rc=$?
    if [ "$_rc" -ne 0 ]; then
        printf '%s\n' "$_CAP_OUT"
    else
        printf '%s\n' "$_CAP_OUT" | grep -E \
            '^(Package|Lock file) operations:|^[[:space:]]+- (Installing|Upgrading|Downgrading|Removing) |[Ww]arning|[Dd]eprecat|abandoned' \
            || printf '%s   (aucun changement de dépendance)%s\n' "$C_DIM" "$C_RESET"
    fi
    return "$_rc"
}

# Lance une commande discrètement : en cas de succès, seules les lignes
# d'avertissement/erreur sont affichées (rien s'il n'y en a pas), en cas d'échec
# toute la sortie. Conserve le code de sortie pour la restauration.
run_quiet() {
    _rc=0
    _run_capture "$@" || _rc=$?
    if [ "$_rc" -ne 0 ]; then
        printf '%s\n' "$_CAP_OUT"
    else
        printf '%s\n' "$_CAP_OUT" | grep -iE 'warn|error|deprecat|fail|exception|fatal' || true
    fi
    return "$_rc"
}

# Lance une vérification : silencieuse en cas de succès, toute la sortie en cas
# d'échec. Conserve le code de sortie.
run_check() {
    _rc=0
    _run_capture "$@" || _rc=$?
    [ "$_rc" -ne 0 ] && printf '%s\n' "$_CAP_OUT"
    return "$_rc"
}

# --- Vérifications préalables ----------------------------------------------
if [ ! -f .env ]; then
    err "'.env' introuvable. Lance ce script depuis la racine du projet."
    exit 1
fi

env_get() {
    grep -E "^$1=" .env | head -n 1 | cut -d '=' -f 2- | tr -d '"' | tr -d "'"
}

APP_ENV=$(env_get APP_ENV)
if [ "$APP_ENV" != "local" ]; then
    err "ci-cd.sh est réservé au développement local (APP_ENV=$APP_ENV)."
    exit 1
fi

SAIL="vendor/bin/sail"

if [ ! -x "$SAIL" ]; then
    err "$SAIL introuvable. Installe d'abord les dépendances (composer install)."
    exit 1
fi

# ide-helper lit le schéma en base et les vérifications tournent dans le
# conteneur : Sail doit être démarré.
if ! $SAIL php -v >/dev/null 2>&1; then
    err "Sail n'est pas démarré. Lance d'abord : $SAIL up -d"
    exit 1
fi

# --- Filet de sécurité (restauration) --------------------------------------
WORK=$(mktemp -d)
UPDATES_STARTED="no"
PIPELINE_OK="no"

cleanup() {
    _status=$?
    if [ "$UPDATES_STARTED" = "yes" ] && [ "$PIPELINE_OK" != "yes" ]; then
        echo
        err "Échec détecté (code $_status) - restauration des dépendances..."
        for _f in composer.json composer.lock package.json package-lock.json; do
            if [ -f "$WORK/$_f" ]; then
                cp "$WORK/$_f" "$_f" && ok "$_f restauré"
            fi
        done
        step "réinstallation selon les fichiers restaurés..."
        run_composer $SAIL composer install || true
        run_quiet $SAIL npm install || true
        ok "restauration terminée - le projet est revenu à son état initial."
    fi
    rm -rf "$WORK"
}
trap cleanup EXIT

# --- Menu de sélection -----------------------------------------------------
# Lit des lignes "nom|actuelle|dernière[|section]" depuis $2, affiche un menu
# numéroté intitulé $1, et laisse les noms choisis (séparés par des espaces)
# dans REPLY_SELECTED.
prompt_selection() {
    _title="$1"
    _file="$2"
    REPLY_SELECTED=""

    if [ ! -s "$_file" ]; then
        ok "$_title : tout est à jour."
        return 0
    fi

    printf '%s%s%s\n' "$C_BOLD" "$_title" "$C_RESET"
    _i=1
    while IFS='|' read -r _name _cur _lat _section; do
        printf '   %s%2d)%s %s  %s%s%s %s→%s %s%s%s\n' \
            "$C_CYAN" "$_i" "$C_RESET" \
            "$_name" \
            "$C_DIM" "$_cur" "$C_RESET" \
            "$C_DIM" "$C_RESET" \
            "$C_GREEN" "$_lat" "$C_RESET"
        _i=$((_i + 1))
    done < "$_file"

    printf '   %sNuméros à monter%s (ex. %s1 3 4%s), %sa%s = tout, %svide%s = rien : ' \
        "$C_BOLD" "$C_RESET" "$C_CYAN" "$C_RESET" "$C_CYAN" "$C_RESET" "$C_DIM" "$C_RESET"
    read -r _answer < /dev/tty || _answer=""

    case "$_answer" in
        a|A|all|tout)
            REPLY_SELECTED=$(cut -d '|' -f1 "$_file" | tr '\n' ' ')
            ;;
        "")
            REPLY_SELECTED=""
            ;;
        *)
            for _n in $_answer; do
                case "$_n" in
                    ''|*[!0-9]*) continue ;;
                esac
                _pick=$(sed -n "${_n}p" "$_file" | cut -d '|' -f1)
                [ -n "$_pick" ] && REPLY_SELECTED="$REPLY_SELECTED $_pick"
            done
            ;;
    esac
}

# --- Bannière --------------------------------------------------------------
printf '\n%s%s  Maintenance CI/CD  %s %s(APP_ENV=%s)%s\n' \
    "$C_BOLD" "$C_BLUE" "$C_RESET" "$C_DIM" "$APP_ENV" "$C_RESET"
hint "Met à jour les dépendances, monte les majeures choisies, lance Boost + ide-helper + Pint, puis build, phpstan, pest et audits."

# --- Arbre Git propre ? ----------------------------------------------------
# On ne demande confirmation que si l'arbre de travail est sale : sinon les
# changements du script (dépendances, PHPDocs, Pint) se mélangeraient aux tiens
# dans 'git diff'.
if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    _dirty=$(git status --porcelain)
    if [ -n "$_dirty" ]; then
        warn "L'arbre Git n'est PAS propre - les changements du script (dépendances, PHPDocs, Pint) vont se mélanger aux tiens dans 'git diff' :"
        printf '%s\n' "$_dirty" | sed "s/^/     /"
        printf '\n   %sContinuer quand même ?%s [o/N] ' "$C_BOLD" "$C_RESET"
        read -r _go < /dev/tty || _go=""
        case "$_go" in
            o|O|oui|OUI|y|Y|yes|YES) ;;
            *) hint "Abandon - rien n'a été modifié."; exit 0 ;;
        esac
    fi
fi

# --- Sauvegarde des manifestes et lock files avant toute modification -------
for _f in composer.json composer.lock package.json package-lock.json; do
    [ -f "$_f" ] && cp "$_f" "$WORK/$_f"
done

# --- Mise à jour complète (dans les contraintes de composer.json / package.json)
section "Mise à jour de toutes les dépendances"
UPDATES_STARTED="yes"

step "composer update (tout)..."
run_composer $SAIL composer update

step "npm update (tout)..."
run_quiet $SAIL npm update

# --- Choix des montées de version ------------------------------------------
# Après la mise à jour complète, ce qui reste obsolète est bloqué par les
# contraintes de version (en général des majeures qui demandent de modifier
# composer.json). Le JSON est lu par php/node dans le conteneur Sail (pas besoin
# de jq/fzf). Chaque ligne est "nom|actuelle|dernière".
section "Recherche des dépendances encore obsolètes"
step "lecture des rapports outdated de Composer et npm..."

$SAIL php -r '
    $out = shell_exec("composer outdated --direct --format=json 2>/dev/null");
    $d = json_decode($out ?: "{}", true);
    $cj = json_decode(@file_get_contents("composer.json"), true) ?: [];
    $dev = $cj["require-dev"] ?? [];
    foreach ($d["installed"] ?? [] as $p) {
        $section = isset($dev[$p["name"]]) ? "dev" : "prod";
        echo $p["name"]."|".($p["version"] ?? "?")."|".($p["latest"] ?? "?")."|".$section."\n";
    }
' > "$WORK/composer.list" 2>/dev/null || true

$SAIL node -e '
    const { execSync } = require("child_process");
    const fs = require("fs");
    let out = "{}";
    try { out = execSync("npm outdated --json", { encoding: "utf8" }); }
    catch (e) { out = e.stdout || "{}"; }
    const d = JSON.parse(out || "{}");
    const pj = JSON.parse(fs.readFileSync("package.json", "utf8"));
    const direct = { ...(pj.dependencies || {}), ...(pj.devDependencies || {}) };
    for (const name of Object.keys(d)) {
        if (!(name in direct)) { continue; } // ignore les dépendances transitives
        const p = d[name];
        console.log(name + "|" + (p.current || "-") + "|" + (p.latest || "-"));
    }
' > "$WORK/npm.list" 2>/dev/null || true

section "Paquets Composer"
prompt_selection "Paquets Composer avec une version plus récente" "$WORK/composer.list"
COMPOSER_TARGETS="$REPLY_SELECTED"

section "Paquets npm"
prompt_selection "Paquets npm avec une version plus récente" "$WORK/npm.list"
NPM_TARGETS="$REPLY_SELECTED"

# --- Montée des paquets choisis --------------------------------------------
# Ce sont des majeures bloquées par les contraintes : on relève la contrainte
# avec `composer require` / `npm install <pkg>@latest` (un `update` ne ferait rien).
section "Montée des paquets choisis"

if [ -n "$COMPOSER_TARGETS" ]; then
    _prod_req=""
    _dev_req=""
    for _name in $COMPOSER_TARGETS; do
        _line=$(awk -F '|' -v n="$_name" '$1 == n { print; exit }' "$WORK/composer.list")
        _lat=$(printf '%s' "$_line" | cut -d '|' -f3)
        _sec=$(printf '%s' "$_line" | cut -d '|' -f4)
        [ -z "$_lat" ] && continue
        if [ "$_sec" = "dev" ]; then
            _dev_req="$_dev_req ${_name}:^${_lat}"
        else
            _prod_req="$_prod_req ${_name}:^${_lat}"
        fi
    done
    if [ -n "$_prod_req" ]; then
        step "composer require -W$_prod_req"
        run_composer $SAIL composer require -W $_prod_req
    fi
    if [ -n "$_dev_req" ]; then
        step "composer require --dev -W$_dev_req"
        run_composer $SAIL composer require --dev -W $_dev_req
    fi
else
    warn "aucun paquet Composer choisi - étape sautée."
fi

if [ -n "$NPM_TARGETS" ]; then
    _npm_args=""
    for _name in $NPM_TARGETS; do
        _lat=$(awk -F '|' -v n="$_name" '$1 == n { print $3; exit }' "$WORK/npm.list")
        if [ -n "$_lat" ] && [ "$_lat" != "-" ]; then
            _npm_args="$_npm_args ${_name}@${_lat}"
        else
            _npm_args="$_npm_args ${_name}@latest"
        fi
    done
    step "npm install$_npm_args"
    run_quiet $SAIL npm install $_npm_args
else
    warn "aucun paquet npm choisi - étape sautée."
fi

# Les dépendances ne bougeront plus : un échec plus loin ne restaure pas les mises à jour.
PIPELINE_OK="yes"

# --- Qualité du code -------------------------------------------------------
section "Qualité du code"

# boost.json est créé par `artisan boost:install` (interactif, à lancer une fois à la main).
if [ -f boost.json ]; then
    step "rafraîchissement des guidelines Laravel Boost..."
    if run_quiet $SAIL artisan boost:update --no-interaction; then
        BOOST_RESULT="à jour"
    else
        BOOST_RESULT="ÉCHEC"; warn "boost:update a échoué"
    fi
else
    BOOST_RESULT="non installé"
    warn "Laravel Boost n'est pas installé dans le projet - lance une fois : $SAIL artisan boost:install"
fi

step "régénération des PHPDocs des modèles (ide-helper)..."
# --no-interaction répond « non » à la question sur _ide_helper.php ; les lignes
# « Written new phpDocBlock to » sont masquées, seuls les vrais avertissements
# et un total sont affichés.
_run_capture $SAIL artisan ide-helper:models --write --no-interaction || true
_ide_count=$(printf '%s\n' "$_CAP_OUT" | grep -c 'Written new phpDocBlock to' || true)
printf '%s\n' "$_CAP_OUT" | grep -v -e 'Written new phpDocBlock to' -e '^[[:space:]]*$' || true
ok "$_ide_count docblocks de modèles mis à jour"

step "formatage PHP avec Pint..."
run_quiet $SAIL shell -c "./vendor/bin/pint"

# --- Vérification front (informative : ne restaure jamais rien) -------------
# Une montée de majeure peut casser le front ou le back. Les échecs sont
# signalés mais NE restaurent RIEN : à toi de les examiner (`git diff`).
section "Vérification front"
if run_check $SAIL npm run build; then
    VITE_BUILD_RESULT="ok"; ok "build Vite réussi"
else
    VITE_BUILD_RESULT="ÉCHEC"; warn "le build Vite a échoué"
fi

# --- Vérification back (informative : ne restaure jamais rien) --------------
section "Vérification back"
if run_check $SAIL shell -c "./vendor/bin/phpstan analyse --memory-limit=2G --no-progress"; then
    PHPSTAN_RESULT="ok"; ok "PHPStan sans erreur"
else
    PHPSTAN_RESULT="ERREURS"; warn "PHPStan signale des erreurs"
fi

if run_check $SAIL shell -c "./vendor/bin/pest"; then
    PEST_RESULT="ok"; ok "tests Pest réussis"
else
    PEST_RESULT="ÉCHEC"; warn "des tests Pest échouent"
fi

# --- Audits de sécurité (informatifs : ne restaurent jamais rien) -----------
# On n'affiche que le résumé : le détail se consulte avec les mêmes commandes.
section "Audits de sécurité"
_rc=0
_run_capture $SAIL composer audit --no-interaction || _rc=$?
if [ "$_rc" -eq 0 ]; then
    COMPOSER_AUDIT_RESULT="ok"; ok "composer audit : aucune faille connue"
else
    COMPOSER_AUDIT_RESULT="FAILLES"
    warn "composer audit : $(printf '%s\n' "$_CAP_OUT" | grep -iE 'Found [0-9]+' | head -n 1)"
    hint "détail : $SAIL composer audit"
fi

_rc=0
_run_capture $SAIL npm audit || _rc=$?
if [ "$_rc" -eq 0 ]; then
    NPM_AUDIT_RESULT="ok"; ok "npm audit : aucune faille connue"
else
    NPM_AUDIT_RESULT="FAILLES"
    warn "npm audit : $(printf '%s\n' "$_CAP_OUT" | grep -iE '[0-9]+ vulnerabilit' | tail -n 1)"
    hint "détail : $SAIL npm audit"
fi

# --- Récapitulatif ---------------------------------------------------------
mark() { if [ "$1" = "ok" ] || [ "$1" = "à jour" ]; then printf '%s✓%s' "$C_GREEN" "$C_RESET"; else printf '%s⚠%s' "$C_YELLOW" "$C_RESET"; fi; }
line() { printf '%s┃%s  %s %-27s %s%s%s\n' "$C_GREEN" "$C_RESET" "$1" "$2" "$C_DIM" "$3" "$C_RESET"; }

printf '\n%s%s┃ Récapitulatif ─ maintenance CI/CD terminée%s\n' "$C_BOLD" "$C_GREEN" "$C_RESET"
line "$(mark ok)" "composer update + montées" "${COMPOSER_TARGETS:- aucune}"
line "$(mark ok)" "npm update + montées" "${NPM_TARGETS:- aucune}"
line "$(mark "$BOOST_RESULT")" "boost:update" "$BOOST_RESULT"
line "$(mark ok)" "ide-helper:models --write" "PHPDocs des modèles"
line "$(mark ok)" "pint" "style du code PHP"
line "$(mark "$VITE_BUILD_RESULT")" "vite build" "$VITE_BUILD_RESULT"
line "$(mark "$PHPSTAN_RESULT")" "phpstan analyse" "$PHPSTAN_RESULT"
line "$(mark "$PEST_RESULT")" "pest" "$PEST_RESULT"
line "$(mark "$COMPOSER_AUDIT_RESULT")" "composer audit" "$COMPOSER_AUDIT_RESULT"
line "$(mark "$NPM_AUDIT_RESULT")" "npm audit" "$NPM_AUDIT_RESULT"
hint "Relis les changements avec 'git diff' avant de commiter."
