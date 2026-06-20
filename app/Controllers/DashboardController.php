<?php

namespace App\Controllers;

use App\Models\PelangganModel;
use App\Models\TransaksiModel;
use App\Models\RfmModel;

class DashboardController extends BaseController
{
    protected PelangganModel $pelanggan;
    protected TransaksiModel $transaksi;

    public function __construct()
    {
        $this->pelanggan = new PelangganModel();
        $this->transaksi = new TransaksiModel();
    }
    public function index()
    {

        $segmen = $this->pelanggan->countBySegment();
        $totalPelanggan = array_sum($segmen);

        $db = \Config\Database::connect();
        $totalPendapatan = (int) $db->table('tb_transaksi')->selectSum('jumlah_transaksi')->get()->getRow()->jumlah_transaksi ?? 0;
        $totalTransaksi  = (int) $db->table('tb_transaksi')->countAll();

        $data = [
            'title'           => 'Dashboard',
            'pageTitle'       => 'Dashboard',
            'pageIcon'        => 'bi-speedometer2',
            'totalPelanggan'  => $totalPelanggan,
            'totalTransaksi'  => $totalTransaksi,
            'totalPendapatan' => $totalPendapatan,
            'segmen'          => $segmen,
            'transaksiBaru'   => $this->transaksi->getRecent(5),
        ];
        return view('pages/dashboard', $data);
    }
}
