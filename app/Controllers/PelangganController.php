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
        $sort  = $this->request->getGet('sort') ?? 'nama_pelanggan';
        $order = $this->request->getGet('order') ?? 'ASC';
        $perPage = $this->request->getGet('per_page');

        $allowedSort = ['id', 'nama_pelanggan', 'email', 'telepon', 'agama', 'segment'];
        $sort  = in_array($sort, $allowedSort, true) ? $sort : 'nama_pelanggan';
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $allowedPerPage = [25, 50, 100, 200, 500, 'all'];
        if (is_numeric($perPage)) {
            $perPage = (int) $perPage;
        }
        $perPage = in_array($perPage, $allowedPerPage, true) ? $perPage : 100;

        if ($perPage === 'all') {
            $pelanggan = $this->model->orderBy($sort, $order)->findAll();
            $pager = null;
        } else {
            $page = (int) ($this->request->getGet('page') ?: 1);
            $page = max($page, 1);

            $this->model->orderBy($sort, $order);
            $total = $this->model->countAllResults(false);

            $offset = ($page - 1) * (int) $perPage;
            $pelanggan = $this->model->findAll((int) $perPage, $offset);

            $pager = service('pager');
            $pager->store('default', $page, (int) $perPage, $total, 0);
            $pager->only(['sort', 'order', 'per_page']);
        }

        $data = [
            'title'     => 'Daftar Pelanggan',
            'pageTitle' => 'Manajemen Pelanggan',
            'pelanggan' => $pelanggan,
            'pager'     => $pager,
            'sort'      => $sort,
            'order'     => $order,
            'perPage'   => $perPage,
        ];
        return view('pages/pelanggan/index', $data);
    }

    public function data()
    {
        $allowedSort = [
            'id'            => 'id',
            'nama_pelanggan'=> 'nama_pelanggan',
            'email'         => 'email',
            'telepon'       => 'telepon',
            'agama'         => 'agama',
            'segment'       => 'segment',
        ];
        $allowedPerPage = [25, 50, 100, 200, 500, 'all'];
        $allowedDir     = ['asc', 'desc'];

        $sortKey = (string) ($this->request->getGet('sort') ?? 'id');
        $sort    = $allowedSort[$sortKey] ?? 'id';

        $dir = strtolower((string) ($this->request->getGet('dir') ?? 'asc'));
        if (! in_array($dir, $allowedDir, true)) {
            $dir = 'asc';
        }

        $perPageRaw = $this->request->getGet('per_page');
        if (is_numeric($perPageRaw)) {
            $perPage = (int) $perPageRaw;
        } else {
            $perPage = (string) $perPageRaw;
        }
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 100;
        }

        $page = max((int) ($this->request->getGet('page') ?: 1), 1);

        $total = $this->model->countAllResults(true);

        if ($perPage === 'all') {
            $rows       = $this->model->orderBy($sort, $dir)->findAll();
            $totalPages = $total > 0 ? 1 : 0;
            $page       = 1;
        } else {
            $offset     = ($page - 1) * (int) $perPage;
            $rows       = $this->model->orderBy($sort, $dir)->findAll((int) $perPage - 1, $offset);
            $totalPages = (int) ceil($total / (int) $perPage);
        }

        return $this->response->setJSON([
            'rows'        => $rows,
            'total'       => (int) $total,
            'page'        => (int) $page,
            'per_page'    => $perPage === 'all' ? 'all' : (int) $perPage,
            'total_pages' => (int) $totalPages,
            'sort'        => $sortKey,
            'dir'         => $dir,
        ]);
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
            'agama'          => 'required|in_list[Islam,Kristen,Katolik,Buddha,Hindu,Lainnya]',
            'tanggal_lahir'  => 'required|valid_date',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->insert([
            'nama_pelanggan' => $this->request->getPost('nama_pelanggan'),
            'email'          => $this->request->getPost('email'),
            'telepon'        => $this->request->getPost('telepon'),
            'alamat'         => $this->request->getPost('alamat'),
            'agama'          => $this->request->getPost('agama'),
            'tanggal_lahir'  => $this->request->getPost('tanggal_lahir'),
            'profesi'        => $this->request->getPost('profesi'),
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
            'agama'          => 'required|in_list[Islam,Kristen,Katolik,Buddha,Hindu,Lainnya]',
            'tanggal_lahir'  => 'required|valid_date',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, [
            'nama_pelanggan' => $this->request->getPost('nama_pelanggan'),
            'email'          => $this->request->getPost('email'),
            'telepon'        => $this->request->getPost('telepon'),
            'alamat'         => $this->request->getPost('alamat'),
            'agama'          => $this->request->getPost('agama'),
            'tanggal_lahir'  => $this->request->getPost('tanggal_lahir'),
            'profesi'        => $this->request->getPost('profesi'),
        ]);

        return redirect()->to('/pelanggan')->with('success', 'Data pelanggan diperbarui.');
    }

    public function delete(int $id)
    {
        $this->model->delete($id);
        return redirect()->to('/pelanggan')->with('success', 'Pelanggan dihapus.');
    }
}
