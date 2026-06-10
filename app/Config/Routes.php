<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

/* ===== Public (Auth) ===== */
$routes->get('/',  'AuthController::login');
$routes->post('login', 'AuthController::doLogin');
$routes->get('logout', 'AuthController::logout');

/* ===== Protected (auth filter) ===== */
$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('dashboard',         'DashboardController::index');

    $routes->get('pelanggan',                 'PelangganController::index');
    $routes->get('pelanggan/data',            'PelangganController::data');
    $routes->get('pelanggan/create',          'PelangganController::create');
    $routes->post('pelanggan/store',          'PelangganController::store');
    $routes->get('pelanggan/edit/(:num)',     'PelangganController::edit/$1');
    $routes->post('pelanggan/update/(:num)',  'PelangganController::update/$1');
    $routes->get('pelanggan/delete/(:num)',   'PelangganController::delete/$1');

    $routes->get('transaksi',                 'TransaksiController::index');
    $routes->get('transaksi/data',            'TransaksiController::data');
    $routes->get('transaksi/create',          'TransaksiController::create');
    $routes->post('transaksi/store',          'TransaksiController::store');
    $routes->get('transaksi/edit/(:num)',     'TransaksiController::edit/$1');
    $routes->post('transaksi/update/(:num)',  'TransaksiController::update/$1');
    $routes->get('transaksi/delete/(:num)',   'TransaksiController::delete/$1');

    $routes->get('analisis',                     'AnalisisController::index');
    $routes->get('analisis/data',                'AnalisisController::data');
    $routes->get('analisis/progress',            'AnalisisController::progress');
    $routes->post('analisis/proses-rfm-cluster', 'AnalisisController::prosesRFMCluster');

    $routes->get('promosi',         'PromosiController::index');
    $routes->get('promosi/data',    'PromosiController::data');
    $routes->post('promosi/kirim',  'PromosiController::kirim');
    $routes->get('promosi/riwayat', 'PromosiController::riwayat');
    $routes->post('promosi/riwayat/hapus', 'PromosiController::hapusLog');
});
