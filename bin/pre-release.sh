#!/usr/bin/env bash
# pre-release.sh — build, QIT tests, git release
# Usage:
#   ! ./bin/pre-release.sh                      — fluxo completo
#   ! ./bin/pre-release.sh --skip-manual        — todos autos, pula manuais
#   ! ./bin/pre-release.sh phpstan              — só rerun de teste(s) específico(s)
#   ! ./bin/pre-release.sh phpstan --skip-manual — combinado (--skip-manual é ignorado para testes autos)

set -euo pipefail

# ── colors ───────────────────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'
BOLD='\033[1m'

# ── config ────────────────────────────────────────────────────────────────────
PLUGIN_DIR="$(cd "$(dirname "$0")/.." && pwd)"
ZIP_PATH="$PLUGIN_DIR/dist/notifications-with-whatsapp.zip"
QIT="$PLUGIN_DIR/vendor/bin/qit"
PLUGIN_SLUG="notifications-with-whatsapp"
TMP_DIR=$(mktemp -d)

VERSION=$(grep " \* Version:" "$PLUGIN_DIR/notifications-with-whatsapp.php" | awk '{print $NF}')

TESTS_AUTO=("phpstan" "phpcompatibility" "security" "validation" "plugin-check")
TESTS_MANUAL=("malware" "woo-api" "woo-e2e")
ALL_TESTS=("${TESTS_AUTO[@]}" "${TESTS_MANUAL[@]}")

# ── parse args ────────────────────────────────────────────────────────────────
SKIP_MANUAL=false
SKIP_NPM_BUILD=false
RUN_TESTS=()

for arg in "$@"; do
    if [ "$arg" = "--skip-manual" ]; then
        SKIP_MANUAL=true
    else
        RUN_TESTS+=("$arg")
    fi
done

# Se nenhum teste especificado, rodar todos
if [ ${#RUN_TESTS[@]} -eq 0 ]; then
    if $SKIP_MANUAL; then
        RUN_TESTS=("${TESTS_AUTO[@]}")
    else
        RUN_TESTS=("${ALL_TESTS[@]}")
    fi
else
    # Testes específicos passados — pula npm build
    SKIP_NPM_BUILD=true
    # --skip-manual é implícito (testes manuais não estão na lista)
fi

# Separar os testes a rodar em auto vs manual
RUN_AUTO=()
RUN_MANUAL=()
for t in "${RUN_TESTS[@]}"; do
    IS_AUTO=false
    for a in "${TESTS_AUTO[@]}"; do
        [ "$t" = "$a" ] && IS_AUTO=true && break
    done
    if $IS_AUTO; then
        RUN_AUTO+=("$t")
    else
        RUN_MANUAL+=("$t")
    fi
done

# ── helpers ───────────────────────────────────────────────────────────────────
open_url() {
    if command -v xdg-open &>/dev/null; then
        xdg-open "$1" &>/dev/null &
    elif command -v open &>/dev/null; then
        open "$1" &>/dev/null &
    else
        echo -e "       ${CYAN}$1${NC}"
    fi
}

check_deps() {
    local missing=()
    command -v jq       &>/dev/null || missing+=("jq")
    command -v npm      &>/dev/null || missing+=("npm")
    command -v composer &>/dev/null || missing+=("composer")
    command -v git      &>/dev/null || missing+=("git")
    [ -f "$QIT" ]                   || missing+=("vendor/bin/qit (rode composer install)")
    if [ ${#missing[@]} -gt 0 ]; then
        echo -e "${RED}Dependências faltando: ${missing[*]}${NC}"
        exit 1
    fi
}

# ── step 1: build ─────────────────────────────────────────────────────────────
step_build() {
    echo -e "\n${BOLD}${BLUE}[1/3] Build${NC}"
    cd "$PLUGIN_DIR"
    if $SKIP_NPM_BUILD; then
        echo -e "  ${YELLOW}⏭ npm build pulado (reaproveitando build anterior)${NC}"
    else
        npm run build
    fi
    npm run build-market
    echo -e "${GREEN}✅ ZIP gerado: $ZIP_PATH${NC}"
}

# ── step 2: composer install (restaura deps dev, incluindo QIT) ───────────────
step_composer() {
    echo -e "\n${BOLD}${BLUE}[2/3] Composer install${NC}"
    cd "$PLUGIN_DIR"
    composer install --quiet
    echo -e "${GREEN}✅ Dependências dev restauradas${NC}"
}

# ── step 3: QIT tests em paralelo ─────────────────────────────────────────────
step_tests() {
    echo -e "\n${BOLD}${BLUE}[3/3] QIT Tests${NC}\n"
    cd "$PLUGIN_DIR"

    declare -A PIDS

    for test in "${RUN_TESTS[@]}"; do
        echo -e "  ${CYAN}▶ Lançando $test...${NC}"
        (
            "$QIT" "run:$test" "$PLUGIN_SLUG" "--zip=$ZIP_PATH" --wait --json \
                > "$TMP_DIR/$test.out" 2>/dev/null
        ) &
        PIDS[$test]=$!
    done

    echo -e "\n  Aguardando resultados (todos rodando em paralelo nos servidores QIT)...\n"

    for test in "${RUN_TESTS[@]}"; do
        wait "${PIDS[$test]}" 2>/dev/null || true

        JSON=$(grep '^{' "$TMP_DIR/$test.out" 2>/dev/null | tail -1)
        if [ -z "$JSON" ]; then
            JSON='{"status":"error","test_summary":"Falha ao executar o teste"}'
        fi
        echo "$JSON" > "$TMP_DIR/$test.json"

        STATUS=$(echo "$JSON" | jq -r '.status // "error"' 2>/dev/null)
        SUMMARY=$(echo "$JSON" | jq -r '.test_summary // ""' 2>/dev/null)

        case "$STATUS" in
            success|warning) echo -e "  ${GREEN}✅ $test${NC}" ;;
            *)               echo -e "  ${RED}❌ $test — $SUMMARY${NC}" ;;
        esac
    done
}

# ── exibe resultados detalhados e decide se continua ──────────────────────────
show_results() {
    local HAS_AUTO_FAILURE=false
    local HAS_MANUAL=false

    echo -e "\n${BOLD}══════════════════════════════════${NC}"
    echo -e "${BOLD}  Resultados QIT — Version $VERSION${NC}"
    echo -e "${BOLD}══════════════════════════════════${NC}\n"

    # Testes automáticos
    for test in "${RUN_AUTO[@]}"; do
        JSON=$(cat "$TMP_DIR/$test.json" 2>/dev/null || echo '{}')
        STATUS=$(echo "$JSON" | jq -r '.status // "error"')

        # success e warning são considerados OK
        if [ "$STATUS" = "success" ] || [ "$STATUS" = "warning" ]; then
            echo -e "  ${GREEN}✅ $test${NC}"
            continue
        fi

        echo -e "  ${RED}❌ $test${NC}"
        HAS_AUTO_FAILURE=true

        RESULT_JSON=$(echo "$JSON" | jq -r '.test_result_json // ""' 2>/dev/null)
        URL=$(echo "$JSON" | jq -r '.test_results_manager_url // ""' 2>/dev/null)

        if [ -n "$RESULT_JSON" ] && [ "$RESULT_JSON" != "null" ]; then
            case "$test" in
                phpstan)
                    echo "$RESULT_JSON" | jq -r '
                        .files // {} | to_entries[] |
                        select((.value.messages | length) > 0) |
                        .key as $path |
                        ($path | split("/") | last) as $file |
                        .value.messages[] |
                        "       \($file) linha \(.line): \(.message)"
                    ' 2>/dev/null
                    ;;
                phpcompatibility)
                    echo "$RESULT_JSON" | jq -r '
                        .tool.phpcs.files // {} | to_entries[] |
                        select((.value.messages | length) > 0) |
                        .key as $path |
                        ($path | split("/") | last) as $file |
                        .value.messages[] |
                        "       \($file) linha \(.line): \(.message)"
                    ' 2>/dev/null
                    ;;
                security|validation|plugin-check)
                    echo "$RESULT_JSON" | jq -r '
                        if type == "array" then
                            .[] | select(.severity == "ERROR") |
                            "       \(.code // "ERROR"): \(.message // "")"
                        elif type == "object" then
                            .. | .messages? // empty | .[]? |
                            select(.severity == "ERROR" or .type == "ERROR") |
                            "       \(.code // .type // "ERROR"): \(.message // "")"
                        else empty end
                    ' 2>/dev/null | head -20
                    ;;
            esac
        fi

        [ -n "$URL" ] && [ "$URL" != "null" ] && echo -e "       ${CYAN}↗ $URL${NC}"
    done

    echo ""

    # Testes manuais
    for test in "${RUN_MANUAL[@]}"; do
        JSON=$(cat "$TMP_DIR/$test.json" 2>/dev/null || echo '{}')
        STATUS=$(echo "$JSON" | jq -r '.status // "error"')
        URL=$(echo "$JSON" | jq -r '.test_results_manager_url // ""' 2>/dev/null)

        if [ "$STATUS" = "success" ] || [ "$STATUS" = "warning" ]; then
            echo -e "  ${GREEN}✅ $test${NC}"
        else
            echo -e "  ${YELLOW}⚠️  $test — avaliação manual necessária${NC}"
            HAS_MANUAL=true
            if [ -n "$URL" ] && [ "$URL" != "null" ]; then
                echo -e "       Abrindo no browser..."
                open_url "$URL"
            else
                echo -e "       ${RED}URL não disponível${NC}"
            fi
        fi
    done

    if $SKIP_MANUAL && [ ${#RUN_MANUAL[@]} -eq 0 ]; then
        echo -e "  ${YELLOW}⏭ Testes manuais pulados (--skip-manual)${NC}"
    fi

    # Para se testes automáticos falharam
    if $HAS_AUTO_FAILURE; then
        echo -e "\n${RED}${BOLD}❌ Corrija os erros acima e rode novamente.${NC}\n"
        rm -rf "$TMP_DIR"
        exit 1
    fi

    # Pergunta sobre testes manuais
    if $HAS_MANUAL; then
        echo -e "\n${YELLOW}Revise os resultados abertos no browser e volte aqui.${NC}"
        read -rp $'\nOs testes manuais passaram? [s/n]: ' MANUAL_OK
        if [ "$MANUAL_OK" != "s" ]; then
            echo -e "\n${RED}Deploy cancelado.${NC}\n"
            rm -rf "$TMP_DIR"
            exit 1
        fi
    fi
}

# ── git commit + tag + push ───────────────────────────────────────────────────
git_release() {
    echo -e "\n${BOLD}${BLUE}Criando release $VERSION...${NC}"
    cd "$PLUGIN_DIR"

    git add -A
    git commit -m "Version $VERSION"
    git tag "v$VERSION"
    git push
    git push --tags

    echo -e "\n${GREEN}${BOLD}🚀 Version $VERSION publicada!${NC}"
    echo -e "GitHub Actions vai criar o Release automaticamente.\n"
    echo -e "${YELLOW}Próximo passo: upload do ZIP no WooCommerce Marketplace:${NC}"
    echo -e "  ${CYAN}$ZIP_PATH${NC}\n"

    rm -rf "$TMP_DIR"
}

# ── main ──────────────────────────────────────────────────────────────────────
echo -e "\n${BOLD}🚀 Pre-release — Version $VERSION${NC}"

if $SKIP_NPM_BUILD; then
    echo -e "${YELLOW}Modo parcial: rodando apenas: ${RUN_TESTS[*]}${NC}"
elif $SKIP_MANUAL; then
    echo -e "${YELLOW}Modo sem manuais: rodando todos os testes automáticos${NC}"
fi

check_deps
step_build
step_composer
step_tests
show_results
git_release