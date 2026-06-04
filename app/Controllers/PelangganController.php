<?php

namespace App\Controllers;

use App\Models\PelangganModel;

class PelangganController extends BaseController
{
    protected PelangganModel $model;

    public function __construct()
    {
        $this->model = new PelangganModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $data = [
            'title'      => 'Daftar Pelanggan',
            'pageTitle'  => 'Manajemen Pelanggan',
            'pelanggan'  => $this->model->orderBy('nama_pelanggan', 'ASC')->findAll(),
        ];
        return view('pages/pelanggan/index', $data);
    }

    public function create()
    {
        $data = [
            'title'     => 'Tambah Pelanggan',
            'pageTitle' => 'Tambah Pelanggan',
            'pelanggan' => null,
        ];
        return view('pages/pelanggan/form', $data);
    }

    public function store()
    {
        $rules = [
            'nama_pelanggan' => 'required|min_length[3]',
            'email'          => 'required|valid_email',
            'telepon'        => 'required|min_length[8]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->insert([
            'nama_pelanggan' => $this->request->getPost('nama_pelanggan'),
            'email'          => $this->request->getPost('email'),
            'telepon'        => $this->request->getPost('telepon'),
            'alamat'         => $this->request->getPost('alamat'),
        ]);

        return redirect()->to('/pelanggan')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $pelanggan = $this->model->find($id);
        if (! $pelanggan) {
            return redirect()->to('/pelanggan')->with('error', 'Pelanggan tidak ditemukan.');
        }
        $data = [
            'title'     => 'Edit Pelanggan',
            'pageTitle' => 'Edit Pelanggan',
            'pelanggan' => $pelanggan,
        ];
        return view('pages/pelanggan/form', $data);
    }

    public function update(int $id)
    {
        $rules = [
            'nama_pelanggan' => 'required|min_length[3]',
            'email'          => 'required|valid_email',
            'telepon'        => 'required|min_length[8]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, [
            'nama_pelanggan' => $this->request->getPost('nama_pelanggan'),
            'email'          => $this->request->getPost('email'),
            'telepon'        => $this->request->getPost('telepon'),
            'alamat'         => $this->request->getPost('alamat'),
        ]);

        return redirect()->to('/pelanggan')->with('success', 'Data pelanggan diperbarui.');
    }

    public function delete(int $id)
    {
        $this->model->delete($id);
        return redirect()->to('/pelanggan')->with('success', 'Pelanggan dihapus.');
    }
}
