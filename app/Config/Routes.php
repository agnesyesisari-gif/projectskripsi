<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->group('', ['namespace' => 'App\Controllers\Gereja'], function ($routes) {
    // Ibadah Routes
    $routes->get('jadwal-ibadah', 'Ibadah::index');
    $routes->get('jadwal-ibadah/(:segment)', 'Ibadah::detail/$1');
    $routes->group('admin', ['filter' => 'auth'], function ($routes) {
        $routes->resource('ibadah', ['controller' => 'Admin\Ibadah']);
    });
});

// Autoload Routes untuk Program Kerja
$routes->group('program-kerja', ['namespace' => 'App\Controllers\Program'], function ($routes) {
    $routes->get('/', 'ProgramKerja::index');
    $routes->get('(:segment)', 'ProgramKerja::detail/$1');
});
