<?php

namespace App\Controllers;

use App\Models\PelangganModel;
use App\Models\TransaksiModel;

class TransaksiController extends BaseController
{
    protected TransaksiModel $model;
    protected PelangganModel $pelangganModel;

    public function __construct()
    {
        $this->model          = new TransaksiModel();
        $this->pelangganModel = new PelangganModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $data = [
            'title'     => 'Daftar Transaksi',
            'pageTitle' => 'Manajemen Transaksi',
            'pageIcon'  => 'bi-receipt',
            'transaksi' => $this->model->getAllWithPelanggan(),
        ];
        return view('pages/transaksi/index', $data);
    }

    public function create()
    {
        $data = [
            'title'     => 'Tambah Transaksi',
            'pageTitle' => 'Tambah Transaksi',
            'pageIcon'  => 'bi-receipt-cutoff',
            'transaksi' => null,
            'pelanggan' => $this->pelangganModel->orderBy('nama_pelanggan', 'ASC')->findAll(),
        ];
        return view('pages/transaksi/form', $data);
    }

    public function store()
    {
        $rules = [
            'id_pelanggan'      => 'required|integer',
            'tanggal_transaksi' => 'required|valid_date',
            'layanan'           => 'required',
            'tujuan'            => 'required',
            'jumlah_transaksi'  => 'required|numeric',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->insert([
            'id_pelanggan'      => $this->request->getPost('id_pelanggan'),
            'tanggal_transaksi' => $this->request->getPost('tanggal_transaksi'),
            'layanan'           => $this->request->getPost('layanan'),
            'tujuan'            => $this->request->getPost('tujuan'),
            'jumlah_transaksi'  => $this->request->getPost('jumlah_transaksi'),
        ]);

        return redirect()->to('/transaksi')->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $transaksi = $this->model->find($id);
        if (! $transaksi) {
            return redirect()->to('/transaksi')->with('error', 'Transaksi tidak ditemukan.');
        }
        $data = [
            'title'     => 'Edit Transaksi',
            'pageTitle' => 'Edit Transaksi',
            'pageIcon'  => 'bi-receipt-cutoff',
            'transaksi' => $transaksi,
            'pelanggan' => $this->pelangganModel->orderBy('nama_pelanggan', 'ASC')->findAll(),
        ];
        return view('pages/transaksi/form', $data);
    }

    public function update(int $id)
    {
        $rules = [
            'id_pelanggan'      => 'required|integer',
            'tanggal_transaksi' => 'required|valid_date',
            'layanan'           => 'required',
            'tujuan'            => 'required',
            'jumlah_transaksi'  => 'required|numeric',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, [
            'id_pelanggan'      => $this->request->getPost('id_pelanggan'),
            'tanggal_transaksi' => $this->request->getPost('tanggal_transaksi'),
            'layanan'           => $this->request->getPost('layanan'),
            'tujuan'            => $this->request->getPost('tujuan'),
            'jumlah_transaksi'  => $this->request->getPost('jumlah_transaksi'),
        ]);

        return redirect()->to('/transaksi')->with('success', 'Data transaksi diperbarui.');
    }

    public function delete(int $id)
    {
        $this->model->delete($id);
        return redirect()->to('/transaksi')->with('success', 'Transaksi dihapus.');
    }
}
