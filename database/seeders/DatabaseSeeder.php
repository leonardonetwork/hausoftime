<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name'       => 'Admin',
            'first_name' => 'Admin',
            'last_name'  => 'HOT',
            'email'      => env('ADMIN_EMAIL', 'admin@hausoftime.com'),
            'password'   => Hash::make(env('ADMIN_PASSWORD', 'changeme123')),
            'role'       => 'admin',
            'status'     => 'approved',
            'locale'     => 'es',
        ]);

        // Default settings
        $defaults = [
            // General
            ['key' => 'site_name',           'value' => 'Haus of Time',                  'group' => 'general',    'type' => 'string'],
            ['key' => 'site_tagline',        'value' => 'Where every second counts',      'group' => 'general',    'type' => 'string'],
            ['key' => 'contact_email',       'value' => 'info@hausoftime.com',            'group' => 'general',    'type' => 'string'],
            ['key' => 'commission_standard', 'value' => '7',                              'group' => 'general',    'type' => 'string'],
            ['key' => 'commission_founding', 'value' => '4',                              'group' => 'general',    'type' => 'string'],

            // Mail
            ['key' => 'mail_driver',         'value' => 'smtp',                           'group' => 'mail',       'type' => 'string'],
            ['key' => 'mail_host',           'value' => 'smtp.mailgun.org',              'group' => 'mail',       'type' => 'string'],
            ['key' => 'mail_port',           'value' => '587',                            'group' => 'mail',       'type' => 'string'],
            ['key' => 'mail_username',       'value' => '',                               'group' => 'mail',       'type' => 'encrypted'],
            ['key' => 'mail_password',       'value' => '',                               'group' => 'mail',       'type' => 'encrypted'],
            ['key' => 'mail_encryption',     'value' => 'tls',                            'group' => 'mail',       'type' => 'string'],
            ['key' => 'mail_from_address',   'value' => 'hello@hausoftime.com',           'group' => 'mail',       'type' => 'string'],
            ['key' => 'mail_from_name',      'value' => 'Haus of Time',                  'group' => 'mail',       'type' => 'string'],
            // AWS SES
            ['key' => 'aws_key',             'value' => '',                               'group' => 'mail',       'type' => 'encrypted'],
            ['key' => 'aws_secret',          'value' => '',                               'group' => 'mail',       'type' => 'encrypted'],
            ['key' => 'aws_region',          'value' => 'eu-west-1',                     'group' => 'mail',       'type' => 'string'],

            // AI
            ['key' => 'ai_provider',         'value' => 'openai',                         'group' => 'ai',         'type' => 'string'],
            ['key' => 'openai_key',          'value' => '',                               'group' => 'ai',         'type' => 'encrypted'],
            ['key' => 'openai_model',        'value' => 'gpt-4o',                         'group' => 'ai',         'type' => 'string'],
            ['key' => 'anthropic_key',       'value' => '',                               'group' => 'ai',         'type' => 'encrypted'],
            ['key' => 'anthropic_model',     'value' => 'claude-sonnet-4-6',             'group' => 'ai',         'type' => 'string'],
        ];

        foreach ($defaults as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
