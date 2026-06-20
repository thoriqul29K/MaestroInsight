<?php

namespace App\Controllers;

use App\Models\UserModel;

class ProfilController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper(['form', 'url']);
    }

    public function ubahPassword()
    {
        $data = [
            'title'     => 'Ubah Password',
            'pageTitle' => 'Ubah Password',
        ];
        return view('pages/profil/ubah-password', $data);
    }

    public function simpanPassword()
    {
        $rules = [
            'password_lama'     => 'required',
            'password_baru'     => 'required|min_length[3]|max_length[255]',
            'konfirmasi_password' => 'required|matches[password_baru]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = session()->get('user_id');
        $user   = $this->userModel->findById($userId);

        if (! $user || ! password_verify($this->request->getPost('password_lama'), $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Password lama salah.');
        }

        $this->userModel->updateUser($userId, [
            'password' => password_hash($this->request->getPost('password_baru'), PASSWORD_BCRYPT),
        ]);

        return redirect()->to('/profil/ubah-password')->with('success', 'Password berhasil diubah.');
    }
}
