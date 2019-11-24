<?php

// Default configuration
return [
    'debug'        => preg_match('/(localhost|::1|\.local)$/', @$_SERVER['SERVER_NAME']),
    'app.name'     => 'Cockpit',
    # site url (optional) - helpful if you're behind a reverse proxy
    'site_url' => 'https://mydomain.com',
    'base_url'     => COCKPIT_BASE_URL,
    'base_route'   => COCKPIT_BASE_ROUTE,
    'base_host'    => $_SERVER['SERVER_NAME'] ?? \php_uname('n'),
    'base_port'    => $_SERVER['SERVER_PORT'] ?? 80,
    'docs_root'    => COCKPIT_DOCS_ROOT,
    'session.name' => md5(COCKPIT_ENV_ROOT),
    'session.init' => (COCKPIT_ADMIN && !COCKPIT_API_REQUEST),
    'sec-key'      => 'c3b40c4c-db44-s5h7-a814-b4931a15e5e1',
    // Interface language
    'ui.i18n'         => 'en',

    // Content languages
    'languages' => [
        //'default' => 'English',       #setting a default language is optional
        'fr' => 'French',
        'de' => 'German'
    ],
    'modules.disabled' => [],
    'database.config'     => [
        'driver' => 'mongolite',
        'server' => 'mongolite://'.(COCKPIT_STORAGE_FOLDER.'/data'),
        'options' => ['db' => 'cockpitdb'],
        'driverOptions' => []
    ],
    'memory.config'       => [
        'server' => 'redislite://'.(COCKPIT_STORAGE_FOLDER.'/data/cockpit.memory.sqlite'),
        'options' => []
    ],
    'mailer.config' => [
        'from'       => 'info@mydomain.tld',
        'transport'  => 'mail',   //mail|smtp
        'host'       => 'smtp.myhost.tld',
        'user'       => 'username',
        'password'   => 'xxpasswordxx',
        'port'       => 25,
        'auth'       => true,
        'encryption' => ''
    ],
    'groups' => [
//        'author' => [
//            '$admin' => false,
//            '$vars' => [
//                'finder.path' => '/storage/upload'
//            ],
//            'cockpit' => [
//                'backend' => true,
//                'finder' => true
//            ],
//            'collections' => [
//                'manage' => true
//            ]
    ],
    'cors' => [
//        'allowedHeaders' => 'X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding, Cockpit-Token',
//        'allowedMethods' => 'PUT, POST, GET, OPTIONS, DELETE',
//        'allowedOrigins' => '*',
//        'maxAge' => '1000',
//        'allowCredentials' => 'true',
//        'exposedHeaders' => 'true',
    ],
    'paths'         => [
        '#root'     => COCKPIT_DIR,
        '#storage'  => COCKPIT_STORAGE_FOLDER,
        '#pstorage' => COCKPIT_PUBLIC_STORAGE_FOLDER,
        '#data'     => COCKPIT_STORAGE_FOLDER.'/data',
        '#cache'    => COCKPIT_STORAGE_FOLDER.'/cache',
        '#tmp'      => COCKPIT_STORAGE_FOLDER.'/tmp',
        '#thumbs'   => COCKPIT_PUBLIC_STORAGE_FOLDER.'/thumbs',
        '#uploads'  => COCKPIT_PUBLIC_STORAGE_FOLDER.'/uploads',
        '#modules'  => COCKPIT_DIR.'/modules',
        '#addons'   => COCKPIT_ENV_ROOT.'/addons',
        '#config'   => COCKPIT_CONFIG_DIR,
        'assets'    => COCKPIT_DIR.'/assets',
        'site'      => COCKPIT_SITE_DIR
    ],

    'filestorage.config' => [],
];
