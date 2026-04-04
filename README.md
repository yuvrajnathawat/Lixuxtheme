# Pterodactyl Panel Theme

A custom theme for [Pterodactyl Panel](https://pterodactyl.io).

## Requirements

- Pterodactyl Panel (latest)
- PHP 8.1+
- Node.js 14+ (v17+ supported)
- Yarn
- Git
- curl

## Quick Install (Recommended)

Run this one-liner on your server as root:

```bash
bash <(curl -s https://raw.githubusercontent.com/yuvrajnathawat/pterodyctalpaneltheme/master/install.sh)
```

Or download and run manually:

```bash
curl -O https://raw.githubusercontent.com/yuvrajnathawat/pterodyctalpaneltheme/master/install.sh
chmod +x install.sh
sudo bash install.sh
```

The script will:
- Auto-detect your Pterodactyl panel directory
- Present a menu: Install / Update / Uninstall / Manual steps
- Handle Node.js version differences automatically
- Set correct file permissions

## Manual Install

If you prefer to install manually:

```bash
# 1. Go to your panel directory
cd /var/www/pterodactyl

# 2. Clone the theme
git clone https://github.com/yuvrajnathawat/pterodyctalpaneltheme /tmp/theme

# 3. Copy theme files
cp -r /tmp/theme/pterodactyl/arix ./
cp /tmp/theme/pterodactyl/app/Console/Commands/arix.php ./app/Console/Commands/
cp /tmp/theme/pterodactyl/app/Console/Commands/ArixLang.php ./app/Console/Commands/

# 4. Run the installer
php artisan arix install
```

## Artisan Commands

```bash
php artisan arix           # Show help
php artisan arix install   # Install theme
php artisan arix update    # Update theme
php artisan arix uninstall # Remove theme (restores vanilla panel)
```

## Uninstall

Run the install script and choose option 3, or:

```bash
php artisan arix uninstall
```

## License

This theme is provided as-is. Do not redistribute without permission.
