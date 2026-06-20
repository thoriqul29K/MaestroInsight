<?php

namespace App\Controllers;

use App\Models\UserModel;

class UserController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $data = [
            'title'     => 'Kelola Akun',
            'pageTitle' => 'Manajemen Akun Pengguna',
        ];
        return view('pages/user/index', $data);
    }

    public function data()
    {
        $allowedSort = [
            'id'            => 'id',
            'username'      => 'username',
            'nama_lengkap'  => 'nama_lengkap',
            'role'          => 'role',
        ];
        $allowedPerPage = [25, 50, 100, 'all'];
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

        $total = $this->userModel->countAllResults(true);

        if ($perPage === 'all') {
            $rows       = $this->userModel->orderBy($sort, $dir)->findAll();
            $totalPages = $total > 0 ? 1 : 0;
            $page       = 1;
        } else {
            $offset     = ($page - 1) * (int) $perPage;
            $rows       = $this->userModel->orderBy($sort, $dir)->findAll((int) $perPage, $offset);
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
            'title'     => 'Tambah Akun',
            'pageTitle' => 'Tambah Akun Pengguna',
            'user'      => null,
        ];
        return view('pages/user/form', $data);
    }

    public function store()
    {
        $rules = [
            'username'     => 'required|min_length[3]|max_length[50]|is_unique[tb_user.username]',
            'nama_lengkap' => 'required|min_length[3]|max_length[100]',
            'password'     => 'required|min_length[3]|max_length[255]',
            'role'         => 'required|in_list[admin,superadmin]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->userModel->createUser([
            'username'     => $this->request->getPost('username'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'password'     => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'role'         => $this->request->getPost('role'),
        ]);

        return redirect()->to('/user')->with('success', 'Akun berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $user = $this->userModel->findById($id);
        if (! $user) {
            return redirect()->to('/user')->with('error', 'Akun tidak ditemukan.');
        }
        $data = [
            'title'     => 'Edit Akun',
            'pageTitle' => 'Edit Akun Pengguna',
            'user'      => $user,
        ];
        return view('pages/user/form', $data);
    }

    public function update(int $id)
    {
        $user = $this->userModel->findById($id);
        if (! $user) {
            return redirect()->to('/user')->with('error', 'Akun tidak ditemukan.');
        }

        $isUnique = $this->request->getPost('username') === $user['username']
            ? ''
            : '|is_unique[tb_user.username]';

        $rules = [
            'username'     => "required|min_length[3]|max_length[50]{$isUnique}",
            'nama_lengkap' => 'required|min_length[3]|max_length[100]',
            'role'         => 'required|in_list[admin,superadmin]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'username'     => $this->request->getPost('username'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'role'         => $this->request->getPost('role'),
        ];

        $newPassword = $this->request->getPost('password');
        if ($newPassword !== '') {
            $updateData['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        $this->userModel->updateUser($id, $updateData);

        if ($id === session()->get('user_id')) {
            session()->set([
                'username'     => $updateData['username'],
                'nama_lengkap' => $updateData['nama_lengkap'],
                'role'         => $updateData['role'],
            ]);
        }

        return redirect()->to('/user')->with('success', 'Akun berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if ($id === session()->get('user_id')) {
            return redirect()->to('/user')->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $this->userModel->deleteUser($id);
        return redirect()->to('/user')->with('success', 'Akun berhasil dihapus.');
    }

    public function resetPassword(int $id)
    {
        $user = $this->userModel->findById($id);
        if (! $user) {
            return redirect()->to('/user')->with('error', 'Akun tidak ditemukan.');
        }

        $rules = [
            'password' => 'required|min_length[3]|max_length[255]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->userModel->updateUser($id, [
            'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
        ]);

        return redirect()->to('/user')->with('success', 'Password akun "' . $user['username'] . '" berhasil direset.');
    }
}
