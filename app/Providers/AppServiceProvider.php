<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Custom dark theme pagination
        Paginator::defaultView('vendor.pagination.default');

        // Dynamically configure mail from DB settings
        $this->configureMailFromSettings();
    }

    private function configureMailFromSettings(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) return;

            $driver = Setting::get('mail_driver', 'smtp');

            if ($driver === 'ses') {
                $key    = Setting::get('aws_key');
                $secret = Setting::get('aws_secret');
                $region = Setting::get('aws_region', 'eu-west-1');
                if ($key && $secret) {
                    config([
                        'mail.default'        => 'ses',
                        'services.ses.key'    => $key,
                        'services.ses.secret' => $secret,
                        'services.ses.region' => $region,
                    ]);
                }
            } else {
                $host = Setting::get('mail_host');
                $port = Setting::get('mail_port', 587);
                $user = Setting::get('mail_username');
                $pass = Setting::get('mail_password');
                $enc  = Setting::get('mail_encryption', 'tls');
                if ($host) {
                    config([
                        'mail.default'                 => 'smtp',
                        'mail.mailers.smtp.host'       => $host,
                        'mail.mailers.smtp.port'       => $port,
                        'mail.mailers.smtp.username'   => $user,
                        'mail.mailers.smtp.password'   => $pass,
                        'mail.mailers.smtp.encryption' => $enc,
                    ]);
                }
            }

            $from     = Setting::get('mail_from_address');
            $fromName = Setting::get('mail_from_name', 'Haus of Time');
            if ($from) {
                config([
                    'mail.from.address' => $from,
                    'mail.from.name'    => $fromName,
                ]);
            }
        } catch (\Exception) {
            // Silently fail during migrations / first install
        }
    }
}
