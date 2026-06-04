<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

/* ===== Public (Auth) ===== */
$routes->get('login',  'AuthController::login');
$routes->post('login', 'AuthController::doLogin');
$routes->get('logout', 'AuthController::logout');

/* ===== Protected (auth filter) ===== */
$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('/',         'DashboardController::index');
    $routes->get('dashboard', 'DashboardController::index');

    $routes->get('pelanggan',                 'PelangganController::index');
    $routes->get('pelanggan/create',          'PelangganController::create');
    $routes->post('pelanggan/store',          'PelangganController::store');
    $routes->get('pelanggan/edit/(:num)',     'PelangganController::edit/$1');
    $routes->post('pelanggan/update/(:num)',  'PelangganController::update/$1');
    $routes->get('pelanggan/delete/(:num)',   'PelangganController::delete/$1');

    $routes->get('transaksi',                 'TransaksiController::index');
    $routes->get('transaksi/create',          'TransaksiController::create');
    $routes->post('transaksi/store',          'TransaksiController::store');
    $routes->get('transaksi/edit/(:num)',     'TransaksiController::edit/$1');
    $routes->post('transaksi/update/(:num)',  'TransaksiController::update/$1');
    $routes->get('transaksi/delete/(:num)',   'TransaksiController::delete/$1');

    $routes->get('analisis',                 'AnalisisController::index');
    $routes->post('analisis/proses-rfm',     'AnalisisController::prosesRFM');
    $routes->post('analisis/proses-segmentasi', 'AnalisisController::prosesSegmentasi');

    $routes->get('promosi',         'PromosiController::index');
    $routes->post('promosi/kirim',  'PromosiController::kirim');
});
