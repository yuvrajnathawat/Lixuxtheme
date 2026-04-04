<?php

namespace Pterodactyl\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Illuminate\Support\Facades\File;

class Arix extends Command
{
    protected $signature = "arix {action?}";
    protected $description = "All commands for Arix Theme for Pterodactyl.";

    public function handle()
    {
        $action = $this->argument("action");

        $title = new OutputFormatterStyle("#fff", null, ["bold"]);
        $this->output->getFormatter()->setStyle("title", $title);

        $b = new OutputFormatterStyle(null, null, ["bold"]);
        $this->output->getFormatter()->setStyle("b", $b);

        if ($action === null) {
            $this->line("
            <title>
            ░█████╗░██████╗░██╗██╗░░██╗
            ██╔══██╗██╔══██╗██║╚██╗██╔╝
            ███████║██████╔╝██║░╚███╔╝░
            ██╔══██║██╔══██╗██║░██╔██╗░
            ██║░░██║██║░░██║██║██╔╝╚██╗
            ╚═╝░░╚═╝╚═╝░░╚═╝╚═╝╚═╝░░╚═╝

           Pterodactyl Panel Theme</title>

           > php artisan arix (this window)
           > php artisan arix install
           > php artisan arix update
           > php artisan arix uninstall
            ");
        } elseif ($action === "install") {
            $this->install();
        } elseif ($action === "update") {
            $this->update();
        } elseif ($action === "uninstall") {
            $this->uninstall();
        } else {
            $this->error(
                "Invalid action. Supported actions: install, update, uninstall"
            );
        }
    }

    public function installOrUpdate($isUpdate = false)
    {
        if ($isUpdate) {
            $this->info("
    Note: This command skips frequently customized files (routes.ts, getServer.ts,
    admin.blade.php, admin.php, ServerTransformer.php) to preserve your customizations.");
        }

        $confirmation = $this->confirm(
            "Have you read the readme and installed all required dependencies?",
            true
        );
        if (!$confirmation) {
            $this->warn("Installation cancelled.");
            return;
        }

        $versions = File::directories("./arix");
        if (empty($versions)) {
            $this->error("No versions found in the /arix directory. Make sure the theme files are placed in /arix/vX.X.X/");
            return;
        }

        $version = basename($this->choice("Select a version to install:", $versions));
        $this->info("Installing Arix Theme {$version}...");

        // Copy files - use rsync if available, otherwise use PHP copy
        $excludeOption = "";
        if ($isUpdate) {
            $excludeOption = "--exclude='routes.ts' --exclude='getServer.ts' --exclude='admin.blade.php' --exclude='admin.php' --exclude='ServerTransformer.php'";
        }

        $rsyncAvailable = !empty(shell_exec("which rsync 2>/dev/null"));
        if ($rsyncAvailable) {
            $this->command("rsync -a {$excludeOption} arix/{$version}/ ./");
        } else {
            $this->warn("rsync not found, using PHP file copy...");
            $this->copyDirectory("arix/{$version}", base_path(), $isUpdate);
        }

        $this->info("Running database migrations...");
        $this->command("php artisan migrate --force");

        $this->info("Installing required npm packages (this may take a minute)...");
        $this->command(
            "yarn add @types/md5 md5 react-icons@5.4.0 @types/bbcode-to-react bbcode-to-react i18next-browser-languagedetector@7.2.1"
        );

        // Detect Node.js version and set OpenSSL legacy provider if needed
        $nodeVersionRaw = shell_exec("node -v 2>/dev/null");
        $nodeVersion = (int) ltrim(trim($nodeVersionRaw ?? '0'), "v");

        if ($nodeVersion >= 17) {
            $this->info("Node.js v{$nodeVersion} detected — enabling OpenSSL legacy provider for build.");
            $buildCmd = "NODE_OPTIONS=--openssl-legacy-provider yarn build:production";
        } else {
            $this->info("Node.js v{$nodeVersion} detected.");
            $buildCmd = "yarn build:production";
        }

        $this->info("Building panel assets (this may take a minute)...");
        $this->command($buildCmd);

        // Set permissions for the detected web server user
        $this->info("Setting file permissions...");
        $webUser = $this->detectWebUser();
        if ($webUser) {
            $this->info("Detected web server user: {$webUser}");
            $this->command("chown -R {$webUser}:{$webUser} " . base_path() . "/*");
        } else {
            $this->warn("Could not detect web server user. Trying common users...");
            foreach (['www-data', 'nginx', 'apache'] as $user) {
                if ($this->userExists($user)) {
                    $this->command("chown -R {$user}:{$user} " . base_path() . "/*");
                    $this->info("Permissions set for: {$user}");
                    break;
                }
            }
        }

        $this->info("Optimizing application...");
        $this->command("php artisan optimize:clear");
        $this->command("php artisan optimize");

        $this->info("Compiling translations...");
        $this->command("php artisan language:compile");

        $message = $isUpdate ? "Theme updated successfully!" : "Theme installed successfully!";
        $this->line("
        ─────────────────────────────────────
        │                                   │
        │   ✨  {$message}  ✨   │
        │                                   │
        ─────────────────────────────────────
        ");
    }

    public function install()
    {
        $this->installOrUpdate(false);
    }

    public function update()
    {
        $this->installOrUpdate(true);
    }

    private function uninstall()
    {
        $this->warn("This will restore the vanilla Pterodactyl panel.");
        if (!$this->confirm("Are you sure you want to uninstall the theme?")) {
            return;
        }

        $this->info("Uninstalling theme...");
        $this->command("php artisan down");
        $this->command("curl -L https://github.com/pterodactyl/panel/releases/latest/download/panel.tar.gz | tar -xzv");
        $this->command("chmod -R 755 storage/* bootstrap/cache");
        $this->command("composer install --no-dev --optimize-autoloader");
        $this->command("php artisan view:clear");
        $this->command("php artisan config:clear");
        $this->command("php artisan migrate --seed --force");

        $webUser = $this->detectWebUser();
        if ($webUser) {
            $this->command("chown -R {$webUser}:{$webUser} " . base_path() . "/*");
        } else {
            foreach (['www-data', 'nginx', 'apache'] as $user) {
                if ($this->userExists($user)) {
                    $this->command("chown -R {$user}:{$user} " . base_path() . "/*");
                    break;
                }
            }
        }

        $this->command("php artisan queue:restart");
        $this->command("php artisan up");
        $this->info("Theme uninstalled. Panel restored to vanilla Pterodactyl.");
    }

    /**
     * Detect the running web server user.
     */
    private function detectWebUser(): ?string
    {
        $apacheUser = shell_exec("ps aux | grep -E 'apache|httpd' | grep -v grep | awk '{print $1}' | head -1 2>/dev/null");
        $nginxUser  = shell_exec("ps aux | grep nginx | grep -v grep | awk '{print $1}' | head -1 2>/dev/null");

        $apacheUser = trim($apacheUser ?? '');
        $nginxUser  = trim($nginxUser ?? '');

        if (!empty($nginxUser) && $nginxUser !== 'root') return $nginxUser;
        if (!empty($apacheUser) && $apacheUser !== 'root') return $apacheUser;

        return null;
    }

    /**
     * Check if a system user exists.
     */
    private function userExists(string $user): bool
    {
        $result = shell_exec("id -u {$user} 2>/dev/null");
        return !empty(trim($result ?? ''));
    }

    /**
     * Fallback PHP-based directory copy when rsync is unavailable.
     */
    private function copyDirectory(string $src, string $dest, bool $isUpdate = false): void
    {
        $excludeFiles = $isUpdate
            ? ['routes.ts', 'getServer.ts', 'admin.blade.php', 'admin.php', 'ServerTransformer.php']
            : [];

        $srcPath  = base_path($src);
        $destPath = $dest;

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($srcPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $relativePath = substr($file->getPathname(), strlen($srcPath) + 1);
            $targetPath   = $destPath . DIRECTORY_SEPARATOR . $relativePath;

            if ($file->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                if (in_array(basename($file->getPathname()), $excludeFiles)) {
                    continue;
                }
                copy($file->getPathname(), $targetPath);
            }
        }
    }

    private function command(string $cmd): void
    {
        passthru($cmd);
    }
}
