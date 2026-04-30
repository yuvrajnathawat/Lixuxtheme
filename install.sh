#!/bin/bash

# ============================================================
#   Pterodactyl Panel Theme - Installer v2.0.1
#   GitHub: https://github.com/yuvrajnathawat/Lixuxtheme
# ============================================================

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

REPO="https://github.com/yuvrajnathawat/Lixuxtheme"
THEME_VERSION="v1.3.1"

print_banner() {
    echo -e "${CYAN}"
    echo "  ░█████╗░██████╗░██╗██╗░░██╗"
    echo "  ██╔══██╗██╔══██╗██║╚██╗██╔╝"
    echo "  ███████║██████╔╝██║░╚███╔╝░"
    echo "  ██╔══██║██╔══██╗██║░██╔██╗░"
    echo "  ██║░░██║██║░░██║██║██╔╝╚██╗"
    echo "  ╚═╝░░╚═╝╚═╝░░╚═╝╚═╝╚═╝░░╚═╝"
    echo -e "${NC}"
    echo -e "  ${BOLD}Pterodactyl Panel Theme Installer v2.0.1${NC}"
    echo -e "  ${CYAN}${REPO}${NC}"
    echo ""
}

check_root() {
    if [ "$EUID" -ne 0 ]; then
        echo -e "${RED}[ERROR]${NC} Please run this script as root (sudo bash install.sh)"
        exit 1
    fi
}

detect_panel_dir() {
    for dir in /var/www/pterodactyl /var/www/html/pterodactyl /srv/pterodactyl; do
        if [ -f "${dir}/artisan" ]; then
            echo "$dir"
            return
        fi
    done
    echo ""
}

install_node22() {
    echo -e "${CYAN}[*]${NC} Installing Node.js v22..."
    curl -fsSL https://deb.nodesource.com/setup_22.x | bash - >/dev/null 2>&1
    apt-get install -y nodejs >/dev/null 2>&1
    if ! command -v node >/dev/null 2>&1; then
        echo -e "${RED}[ERROR]${NC} Failed to install Node.js."
        exit 1
    fi
    echo -e "${GREEN}[✓]${NC} Node.js $(node -v) installed."
}

install_yarn() {
    echo -e "${CYAN}[*]${NC} Installing yarn..."
    npm install -g yarn >/dev/null 2>&1
    if ! command -v yarn >/dev/null 2>&1; then
        echo -e "${RED}[ERROR]${NC} Failed to install yarn."
        exit 1
    fi
    echo -e "${GREEN}[✓]${NC} yarn $(yarn -v) installed."
}

check_dependencies() {
    echo -e "${CYAN}[*]${NC} Checking dependencies..."

    local missing=()
    command -v php  >/dev/null 2>&1 || missing+=("php")
    command -v git  >/dev/null 2>&1 || missing+=("git")
    command -v curl >/dev/null 2>&1 || missing+=("curl")
    command -v rsync >/dev/null 2>&1 || missing+=("rsync")

    if [ ${#missing[@]} -ne 0 ]; then
        echo -e "${YELLOW}[!]${NC} Installing missing: ${missing[*]}"
        apt-get install -y "${missing[@]}" >/dev/null 2>&1
    fi

    # Node.js v22 required
    if ! command -v node >/dev/null 2>&1; then
        install_node22
    else
        local node_ver
        node_ver=$(node -v | sed 's/v//' | cut -d. -f1)
        if [ "$node_ver" -lt 22 ]; then
            echo -e "${YELLOW}[!]${NC} Node.js v${node_ver} found, upgrading to v22..."
            install_node22
        else
            echo -e "${GREEN}[✓]${NC} Node.js $(node -v) found."
        fi
    fi

    if ! command -v yarn >/dev/null 2>&1; then
        install_yarn
    else
        echo -e "${GREEN}[✓]${NC} yarn $(yarn -v) found."
    fi
}

fix_nginx_iframe() {
    local panel_dir="$1"
    echo -e "${CYAN}[*]${NC} Fixing X-Frame-Options for admin preview..."

    # Try to find and fix nginx config
    for conf in /etc/nginx/sites-available/pterodactyl.conf /etc/nginx/sites-available/default /etc/nginx/conf.d/pterodactyl.conf; do
        if [ -f "$conf" ]; then
            if grep -q "X-Frame-Options" "$conf"; then
                sed -i 's/add_header X-Frame-Options.*/add_header X-Frame-Options "SAMEORIGIN";/' "$conf"
                echo -e "${GREEN}[✓]${NC} Fixed X-Frame-Options in $conf"
            else
                # Add it if not present in server block
                sed -i '/server_name/a\    add_header X-Frame-Options "SAMEORIGIN";' "$conf"
            fi
            nginx -t >/dev/null 2>&1 && systemctl reload nginx >/dev/null 2>&1
            break
        fi
    done

    # Also fix via Laravel middleware if exists
    local middleware="$panel_dir/app/Http/Middleware/SetSecurityHeaders.php"
    if [ -f "$middleware" ]; then
        sed -i "s/'X-Frame-Options'.*=>.*'DENY'/'X-Frame-Options' => 'SAMEORIGIN'/" "$middleware"
        sed -i "s/X-Frame-Options.*DENY/X-Frame-Options: SAMEORIGIN/" "$middleware"
    fi
}

copy_theme_files() {
    local panel_dir="$1"
    local tmp_dir="$2"
    local is_update="${3:-false}"

    echo -e "${CYAN}[*]${NC} Copying theme files..."

    # Copy artisan commands
    cp "$tmp_dir/theme/pterodactyl/app/Console/Commands/arix.php" "$panel_dir/app/Console/Commands/"
    cp "$tmp_dir/theme/pterodactyl/app/Console/Commands/ArixLang.php" "$panel_dir/app/Console/Commands/"

    # Copy arix version folder to panel root (this copies app/, config/, resources/, etc.)
    if [ "$is_update" = "true" ]; then
        rsync -a \
            --exclude='routes.ts' \
            --exclude='getServer.ts' \
            --exclude='admin.blade.php' \
            --exclude='admin.php' \
            --exclude='ServerTransformer.php' \
            "$tmp_dir/theme/pterodactyl/arix/$THEME_VERSION/" "$panel_dir/"
    else
        rsync -a "$tmp_dir/theme/pterodactyl/arix/$THEME_VERSION/" "$panel_dir/"
    fi

    # Also keep the arix/ folder for artisan commands
    rsync -a "$tmp_dir/theme/pterodactyl/arix/" "$panel_dir/arix/"

    echo -e "${GREEN}[✓]${NC} Theme files copied."
}

build_assets() {
    local panel_dir="$1"
    cd "$panel_dir" || exit 1

    echo -e "${CYAN}[*]${NC} Installing required npm packages..."
    yarn add react-icons@5.4.0 md5 @types/md5 bbcode-to-react @types/bbcode-to-react \
        i18next-browser-languagedetector@7.2.1 path-browserify cross-env 2>&1 | tail -3

    # Fix webpack path polyfill if needed
    if grep -q "resolve:" webpack.config.js 2>/dev/null; then
        if ! grep -q "path-browserify" webpack.config.js; then
            sed -i "s/resolve: {/resolve: {\n        fallback: { \"path\": require.resolve(\"path-browserify\") },/" webpack.config.js
            echo -e "${GREEN}[✓]${NC} Added path-browserify polyfill to webpack config."
        fi
    fi

    echo -e "${CYAN}[*]${NC} Building panel assets (this takes a few minutes)..."
    yarn build:production
    if [ $? -ne 0 ]; then
        echo -e "${RED}[ERROR]${NC} Build failed. Check errors above."
        exit 1
    fi
    echo -e "${GREEN}[✓]${NC} Assets built successfully."
}

set_permissions() {
    local panel_dir="$1"
    echo -e "${CYAN}[*]${NC} Setting permissions..."
    for user in www-data nginx apache; do
        if id "$user" &>/dev/null; then
            chown -R "$user":"$user" "$panel_dir"
            echo -e "${GREEN}[✓]${NC} Permissions set for: $user"
            break
        fi
    done
}

install_theme() {
    local panel_dir="$1"
    cd "$panel_dir" || exit 1

    local tmp_dir
    tmp_dir=$(mktemp -d)

    echo -e "${CYAN}[*]${NC} Downloading theme from GitHub..."
    git clone --depth=1 "$REPO" "$tmp_dir/theme" 2>&1 | tail -3
    if [ $? -ne 0 ]; then
        echo -e "${RED}[ERROR]${NC} Failed to clone repository."
        rm -rf "$tmp_dir"
        exit 1
    fi

    copy_theme_files "$panel_dir" "$tmp_dir" "false"
    rm -rf "$tmp_dir"

    echo -e "${CYAN}[*]${NC} Running database migrations..."
    php artisan migrate --force

    build_assets "$panel_dir"
    fix_nginx_iframe "$panel_dir"
    set_permissions "$panel_dir"

    echo -e "${CYAN}[*]${NC} Optimizing..."
    php artisan optimize:clear
    php artisan optimize
    php artisan language:compile

    echo ""
    echo -e "${GREEN}╔══════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║   ✨  Theme installed successfully!  ║${NC}"
    echo -e "${GREEN}╚══════════════════════════════════════╝${NC}"
    echo ""
    echo -e "  Access theme editor: ${CYAN}your-panel/admin/arix${NC}"
}

update_theme() {
    local panel_dir="$1"
    cd "$panel_dir" || exit 1

    local tmp_dir
    tmp_dir=$(mktemp -d)

    echo -e "${CYAN}[*]${NC} Downloading latest theme from GitHub..."
    git clone --depth=1 "$REPO" "$tmp_dir/theme" 2>&1 | tail -3
    if [ $? -ne 0 ]; then
        echo -e "${RED}[ERROR]${NC} Failed to clone repository."
        rm -rf "$tmp_dir"
        exit 1
    fi

    copy_theme_files "$panel_dir" "$tmp_dir" "true"
    rm -rf "$tmp_dir"

    build_assets "$panel_dir"
    fix_nginx_iframe "$panel_dir"
    set_permissions "$panel_dir"

    php artisan optimize:clear
    php artisan optimize
    php artisan language:compile

    echo ""
    echo -e "${GREEN}[✓] Theme updated successfully!${NC}"
}

uninstall_theme() {
    local panel_dir="$1"
    cd "$panel_dir" || exit 1

    echo -e "${YELLOW}[!]${NC} This will restore the vanilla Pterodactyl panel."
    read -rp "Are you sure? (yes/no): " confirm
    if [ "$confirm" != "yes" ]; then
        echo "Uninstall cancelled."
        exit 0
    fi

    php artisan down
    curl -sL https://github.com/pterodactyl/panel/releases/latest/download/panel.tar.gz | tar -xzv >/dev/null 2>&1
    chmod -R 755 storage/* bootstrap/cache
    composer install --no-dev --optimize-autoloader -q
    php artisan view:clear
    php artisan config:clear
    php artisan migrate --seed --force
    set_permissions "$panel_dir"
    php artisan queue:restart
    php artisan up

    echo -e "${GREEN}[✓] Theme uninstalled. Panel restored to vanilla.${NC}"
}

# ─── Main Menu ───────────────────────────────────────────────

print_banner
check_root
check_dependencies

PANEL_DIR=$(detect_panel_dir)

if [ -z "$PANEL_DIR" ]; then
    echo -e "${YELLOW}[!]${NC} Could not auto-detect panel directory."
    read -rp "Enter full path to your panel (e.g. /var/www/pterodactyl): " PANEL_DIR
    if [ ! -f "${PANEL_DIR}/artisan" ]; then
        echo -e "${RED}[ERROR]${NC} No artisan file found at ${PANEL_DIR}."
        exit 1
    fi
fi

echo -e "${GREEN}[✓]${NC} Panel directory: ${BOLD}${PANEL_DIR}${NC}"
echo ""
echo -e "${BOLD}What would you like to do?${NC}"
echo "  1) Install theme"
echo "  2) Update theme"
echo "  3) Uninstall theme (restore vanilla panel)"
echo "  4) Exit"
echo ""
read -rp "Select an option [1-4]: " choice

case "$choice" in
    1) install_theme "$PANEL_DIR" ;;
    2) update_theme "$PANEL_DIR" ;;
    3) uninstall_theme "$PANEL_DIR" ;;
    4) echo "Bye!"; exit 0 ;;
    *) echo -e "${RED}Invalid option.${NC}"; exit 1 ;;
esac
