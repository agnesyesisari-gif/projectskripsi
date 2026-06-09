<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public array $aliases = [
        'csrf'     => \CodeIgniter\Filters\CSRF::class,
        'toolbar'  => \CodeIgniter\Filters\DebugToolbar::class,
        'honeypot' => \CodeIgniter\Filters\Honeypot::class,
        'invalidchars' => \CodeIgniter\Filters\InvalidChars::class,
        'secureheaders' => \CodeIgniter\Filters\SecureHeaders::class,
        'auth'     => \App\Filters\AuthFilter::class,
    ];

    public array $required = [
        'before' => [],
        'after'  => ['toolbar'],
    ];

    public array $globals = [
        'before' => [
            // 'csrf',
        ],
        'after'  => [],
    ];

    public array $methods = [];

    public array $filters = [
        'auth' => [
            'before' => [
                'dashboard',
                'jadwal*',
                'komisi*',
                'program*',
                'whatsapp*',
                'pengumuman*',
                'backup*',
            ],
        ],
    ];
}
