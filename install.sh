#!/bin/bash

# ============================================================
#   Pterodactyl Panel Theme - Installer
#   GitHub: https://github.com/yuvrajnathawat/pterodyctalpaneltheme
# ============================================================

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

REPO="https://github.com/yuvrajnathawat/pterodyctalpaneltheme"
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
    echo -e "  ${BOLD}Pterodactyl Panel Theme Installer${NC}"
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

check_dependencies() {
    echo -e "${CYAN}[*]${NC} Checking dependencies..."

    local missing=()

    command -v php    >/dev/null 2>&1 || missing+=("php")
    command -v yarn   >/dev/null 2>&1 || missing+=("yarn")
    command -v node   >/dev/null 2>&1 || missing+=("node")
    command -v git    >/dev/null 2>&1 || missing+=("git")
    command -v curl   >/dev/null 2>&1 || missing+=("curl")

    if [ ${#missing[@]} -ne 0 ]; then
        echo -e "${RED}[ERROR]${NC} Missing dependencies: ${missing[*]}"
        echo -e "  Install them and re-run this script."
        exit 1
    fi

    local node_ver
    node_ver=$(node -v | sed 's/v//' | cut -d. -f1)
    if [ "$node_ver" -lt 14 ]; then
        echo -e "${RED}[ERROR]${NC} Node.js v${node_ver} is too old. Minimum required: v14"
        exit 1
    fi

    echo -e "${GREEN}[✓]${NC} All dependencies found."
}

install_theme() {
    local panel_dir="$1"

    echo ""
    echo -e "${CYAN}[*]${NC} Downloading theme ${THEME_VERSION} from GitHub..."
    cd "$panel_dir" || exit 1

    # Clone or download the theme files
    local tmp_dir
    tmp_dir=$(mktemp -d)

    git clone --depth=1 "$REPO" "$tmp_dir/theme" 2>&1
    if [ $? -ne 0 ]; then
        echo -e "${RED}[ERROR]${NC} Failed to clone repository. Check your internet connection."
        rm -rf "$tmp_dir"
        exit 1
    fi

    # Copy the arix folder into the panel directory
    echo -e "${CYAN}[*]${NC} Copying theme files..."
    cp -r "$tmp_dir/theme/pterodactyl/arix" "$panel_dir/"
    cp -r "$tmp_dir/theme/pterodactyl/app/Console/Commands/arix.php" "$panel_dir/app/Console/Commands/"
    cp -r "$tmp_dir/theme/pterodactyl/app/Console/Commands/ArixLang.php" "$panel_dir/app/Console/Commands/"

    rm -rf "$tmp_dir"

    echo -e "${GREEN}[✓]${NC} Theme files copied."

    # Run the artisan install command
    echo ""
    echo -e "${CYAN}[*]${NC} Running theme installer..."
    php artisan arix install
}

update_theme() {
    local panel_dir="$1"

    echo ""
    echo -e "${CYAN}[*]${NC} Downloading latest theme from GitHub..."
    cd "$panel_dir" || exit 1

    local tmp_dir
    tmp_dir=$(mktemp -d)

    git clone --depth=1 "$REPO" "$tmp_dir/theme" 2>&1
    if [ $? -ne 0 ]; then
        echo -e "${RED}[ERROR]${NC} Failed to clone repository."
        rm -rf "$tmp_dir"
        exit 1
    fi

    echo -e "${CYAN}[*]${NC} Copying updated theme files..."
    cp -r "$tmp_dir/theme/pterodactyl/arix" "$panel_dir/"
    cp -r "$tmp_dir/theme/pterodactyl/app/Console/Commands/arix.php" "$panel_dir/app/Console/Commands/"
    cp -r "$tmp_dir/theme/pterodactyl/app/Console/Commands/ArixLang.php" "$panel_dir/app/Console/Commands/"

    rm -rf "$tmp_dir"

    echo -e "${GREEN}[✓]${NC} Theme files updated."

    echo ""
    echo -e "${CYAN}[*]${NC} Running theme updater..."
    php artisan arix update
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

    php artisan arix uninstall
}

manual_install() {
    local panel_dir="$1"
    cd "$panel_dir" || exit 1

    echo ""
    echo -e "${CYAN}[*]${NC} Running manual step-by-step installation..."

    # Step 1: Migrate DB
    echo -e "${CYAN}[1/5]${NC} Running database migrations..."
    php artisan migrate --force

    # Step 2: Install npm packages
    echo -e "${CYAN}[2/5]${NC} Installing npm packages..."
    yarn add @types/md5 md5 react-icons@5.4.0 @types/bbcode-to-react bbcode-to-react i18next-browser-languagedetector@7.2.1

    # Step 3: Build assets
    echo -e "${CYAN}[3/5]${NC} Building panel assets..."
    node_ver=$(node -v | sed 's/v//' | cut -d. -f1)
    if [ "$node_ver" -ge 17 ]; then
        NODE_OPTIONS=--openssl-legacy-provider yarn build:production
    else
        yarn build:production
    fi

    # Step 4: Set permissions
    echo -e "${CYAN}[4/5]${NC} Setting permissions..."
    for user in www-data nginx apache; do
        if id "$user" &>/dev/null; then
            chown -R "$user":"$user" "$panel_dir"/*
            echo -e "${GREEN}[✓]${NC} Permissions set for: $user"
            break
        fi
    done

    # Step 5: Optimize
    echo -e "${CYAN}[5/5]${NC} Optimizing..."
    php artisan optimize:clear
    php artisan optimize
    php artisan language:compile

    echo ""
    echo -e "${GREEN}[✓] Manual installation complete!${NC}"
}

# ─── Main Menu ───────────────────────────────────────────────

print_banner
check_root
check_dependencies

# Detect panel directory
PANEL_DIR=$(detect_panel_dir)

if [ -z "$PANEL_DIR" ]; then
    echo -e "${YELLOW}[!]${NC} Could not auto-detect Pterodactyl panel directory."
    read -rp "Enter the full path to your panel (e.g. /var/www/pterodactyl): " PANEL_DIR
    if [ ! -f "${PANEL_DIR}/artisan" ]; then
        echo -e "${RED}[ERROR]${NC} No artisan file found at ${PANEL_DIR}. Is this the correct path?"
        exit 1
    fi
fi

echo -e "${GREEN}[✓]${NC} Panel directory: ${BOLD}${PANEL_DIR}${NC}"
echo ""

echo -e "${BOLD}What would you like to do?${NC}"
echo "  1) Install theme"
echo "  2) Update theme"
echo "  3) Uninstall theme (restore vanilla panel)"
echo "  4) Manual install (run steps individually)"
echo "  5) Exit"
echo ""
read -rp "Select an option [1-5]: " choice

case "$choice" in
    1) install_theme "$PANEL_DIR" ;;
    2) update_theme "$PANEL_DIR" ;;
    3) uninstall_theme "$PANEL_DIR" ;;
    4) manual_install "$PANEL_DIR" ;;
    5) echo "Bye!"; exit 0 ;;
    *) echo -e "${RED}Invalid option.${NC}"; exit 1 ;;
esac
