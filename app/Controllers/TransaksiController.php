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
        $sort  = $this->request->getGet('sort') ?? 'tb_transaksi.tanggal_transaksi';
        $order = $this->request->getGet('order') ?? 'DESC';
        $perPage = $this->request->getGet('per_page');

        $allowedSort = [
            'tb_transaksi.id',
            'tb_transaksi.tanggal_transaksi',
            'tb_pelanggan.nama_pelanggan',
            'tb_transaksi.layanan',
            'tb_transaksi.tujuan',
            'tb_transaksi.jumlah_transaksi',
        ];
        $sort  = in_array($sort, $allowedSort, true) ? $sort : 'tb_transaksi.tanggal_transaksi';
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $allowedPerPage = [25, 50, 100, 200, 500, 'all'];
        if (is_numeric($perPage)) {
            $perPage = (int) $perPage;
        }
        $perPage = in_array($perPage, $allowedPerPage, true) ? $perPage : 100;

        if ($perPage === 'all') {
            $transaksi = $this->model->select('tb_transaksi.*, tb_pelanggan.nama_pelanggan')
                ->join('tb_pelanggan', 'tb_pelanggan.id = tb_transaksi.id_pelanggan', 'left')
                ->orderBy($sort, $order)
                ->findAll();
            $pager = null;
        } else {
            $page = (int) ($this->request->getGet('page') ?: 1);
            $page = max($page, 1);

            $this->model->select('tb_transaksi.*, tb_pelanggan.nama_pelanggan')
                ->join('tb_pelanggan', 'tb_pelanggan.id = tb_transaksi.id_pelanggan', 'left')
                ->orderBy($sort, $order);
            $total = $this->model->countAllResults(false);

            $offset = ($page - 1) * (int) $perPage;
            $transaksi = $this->model->findAll((int) $perPage, $offset);

            $pager = service('pager');
            $pager->store('default', $page, (int) $perPage, $total, 0);
            $pager->only(['sort', 'order', 'per_page']);
        }

        $data = [
            'title'     => 'Daftar Transaksi',
            'pageTitle' => 'Manajemen Transaksi',
            'pageIcon'  => 'bi-receipt',
            'transaksi' => $transaksi,
            'pager'     => $pager,
            'sort'      => $sort,
            'order'     => $order,
            'perPage'   => $perPage,
        ];
        return view('pages/transaksi/index', $data);
    }

    public function data()
    {
        $allowedSort = [
            'id'            => 'tb_transaksi.id',
            'tanggal'       => 'tb_transaksi.tanggal_transaksi',
            'pelanggan'     => 'tb_pelanggan.nama_pelanggan',
            'layanan'       => 'tb_transaksi.layanan',
            'tujuan'        => 'tb_transaksi.tujuan',
            'jumlah'        => 'tb_transaksi.jumlah_transaksi',
        ];
        $allowedPerPage = [25, 50, 100, 200, 500, 'all'];
        $allowedDir     = ['asc', 'desc'];

        $sortKey = (string) ($this->request->getGet('sort') ?? 'id');
        $sort    = $allowedSort[$sortKey] ?? 'tb_transaksi.id';

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

        $builder = $this->model
            ->select('tb_transaksi.*, tb_pelanggan.nama_pelanggan')
            ->join('tb_pelanggan', 'tb_pelanggan.id = tb_transaksi.id_pelanggan', 'left');

        $total = $builder->countAllResults(true);

        if ($perPage === 'all') {
            $rows = $this->model
                ->select('tb_transaksi.*, tb_pelanggan.nama_pelanggan')
                ->join('tb_pelanggan', 'tb_pelanggan.id = tb_transaksi.id_pelanggan', 'left')
                ->orderBy($sort, $dir)
                ->findAll();
            $totalPages = $total > 0 ? 1 : 0;
            $page       = 1;
        } else {
            $offset     = ($page - 1) * (int) $perPage;
            $rows       = $this->model
                ->select('tb_transaksi.*, tb_pelanggan.nama_pelanggan')
                ->join('tb_pelanggan', 'tb_pelanggan.id = tb_transaksi.id_pelanggan', 'left')
                ->orderBy($sort, $dir)
                ->findAll((int) $perPage - 1, $offset);
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
            'layanan'           => 'permit_empty',
            'tujuan'            => 'required',
            'jumlah_transaksi'  => 'required|numeric',
            'detail_transaksi'  => 'permit_empty|max_length[200]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $layanan = $this->request->getPost('layanan');
        $layanan = is_array($layanan) ? implode(',', $layanan) : null;

        $this->model->insert([
            'id_pelanggan'      => $this->request->getPost('id_pelanggan'),
            'tanggal_transaksi' => $this->request->getPost('tanggal_transaksi'),
            'layanan'           => $layanan,
            'tujuan'            => $this->request->getPost('tujuan'),
            'jumlah_transaksi'  => $this->request->getPost('jumlah_transaksi'),
            'detail_transaksi'  => $this->request->getPost('detail_transaksi'),
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
            'layanan'           => 'permit_empty',
            'tujuan'            => 'required',
            'jumlah_transaksi'  => 'required|numeric',
            'detail_transaksi'  => 'permit_empty|max_length[200]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $layanan = $this->request->getPost('layanan');
        $layanan = is_array($layanan) ? implode(',', $layanan) : null;

        $this->model->update($id, [
            'id_pelanggan'      => $this->request->getPost('id_pelanggan'),
            'tanggal_transaksi' => $this->request->getPost('tanggal_transaksi'),
            'layanan'           => $layanan,
            'tujuan'            => $this->request->getPost('tujuan'),
            'jumlah_transaksi'  => $this->request->getPost('jumlah_transaksi'),
            'detail_transaksi'  => $this->request->getPost('detail_transaksi'),
        ]);

        return redirect()->to('/transaksi')->with('success', 'Data transaksi diperbarui.');
    }

    public function delete(int $id)
    {
        $this->model->delete($id);
        return redirect()->to('/transaksi')->with('success', 'Transaksi dihapus.');
    }
}
