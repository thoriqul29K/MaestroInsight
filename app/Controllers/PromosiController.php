<?php

namespace App\Controllers;

use App\Libraries\Promosi\PromosiSender;
use App\Models\PelangganModel;
use App\Models\PromosiLogModel;

class PromosiController extends BaseController
{
    protected PelangganModel $model;
    protected PromosiLogModel $logModel;
    protected PromosiSender $sender;

    public function __construct()
    {
        $this->model    = new PelangganModel();
        $this->logModel = new PromosiLogModel();
        $this->sender   = new PromosiSender();
    }

    public function index()
    {
        $data = [
            'title'     => 'Distribusi Promosi',
            'pageTitle' => 'Distribusi Promosi',
            'pageIcon'  => 'bi-megaphone',
            'pelanggan' => $this->model->orderBy('nama_pelanggan', 'ASC')->findAll(),
            'selected'  => $this->request->getGet('segment') ?? '',
        ];
        return view('pages/promosi/index', $data);
    }

    public function kirim()
    {
        $rules = [
            'id_pelanggan' => 'required',
            'channel'      => 'required|in_list[email]',
            'subject'      => 'required|max_length[255]',
            'pesan'        => 'required|min_length[3]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $ids     = (array) $this->request->getPost('id_pelanggan');
        $channel = $this->request->getPost('channel');
        $subject = trim((string) $this->request->getPost('subject'));
        $pesan   = (string) $this->request->getPost('pesan');

        $pelangganList = $this->model->whereIn('id', $ids)->findAll();
        $withEmail     = array_values(array_filter($pelangganList, static fn ($p) => ! empty($p['email'])));

        if (empty($withEmail)) {
            return redirect()->back()->withInput()->with('error', 'Tidak ada pelanggan yang memiliki alamat email valid pada pilihan Anda.');
        }

        $results = $this->sender->send('email', $withEmail, $subject, $pesan, [$this, 'personalize']);

        $sent   = 0;
        $failed = 0;
        $errorDetails = [];

        foreach ($results as $r) {
            $p     = $r['pelanggan'];
            $res   = $r['result'];
            $isOk  = $res->success;

            $this->logModel->insert([
                'id_pelanggan'   => $p['id'] ?? null,
                'nama_pelanggan' => $p['nama_pelanggan'] ?? null,
                'email_target'   => $p['email'] ?? null,
                'channel'        => $channel,
                'subject'        => $subject,
                'status'         => $isOk ? 'success' : 'failed',
                'error_message'  => $isOk ? null : ($res->errorMessage ?? 'Unknown error'),
            ]);

            if ($isOk) {
                $sent++;
            } else {
                $failed++;
                $errorDetails[] = ($p['nama_pelanggan'] ?? '?') . ': ' . ($res->errorMessage ?? 'gagal');
            }
        }

        if ($sent > 0 && $failed === 0) {
            $msg = "Email promosi berhasil dikirim ke {$sent} pelanggan.";
        } elseif ($sent > 0 && $failed > 0) {
            $preview = implode(' | ', array_slice($errorDetails, 0, 3));
            $msg     = "Email terkirim: {$sent}. Gagal: {$failed}. Detail: {$preview}";
        } else {
            $preview = implode(' | ', array_slice($errorDetails, 0, 3));
            $msg     = "Gagal mengirim ke semua {$failed} pelanggan. Cek konfigurasi SMTP. {$preview}";
        }

        return redirect()->back()
            ->with($sent > 0 ? 'success' : 'error', $msg)
            ->withInput();
    }

    public function riwayat()
    {
        $status  = $this->request->getGet('status');
        $channel = $this->request->getGet('channel');

        $data = [
            'title'         => 'Riwayat Promosi',
            'pageTitle'     => 'Riwayat Promosi',
            'pageIcon'      => 'bi-clock-history',
            'logs'          => $this->logModel->getFiltered(50, $status, $channel),
            'filterStatus'  => $status ?? '',
            'filterChannel' => $channel ?? '',
        ];
        return view('pages/promosi/riwayat', $data);
    }

    public function personalize(string $pesan, array $pelanggan): string
    {
        $nama = trim((string) ($pelanggan['nama_pelanggan'] ?? ''));
        if ($nama === '') {
            $nama = 'Bapak/Ibu';
        }
        $segmen = match ($pelanggan['segment']) {
            'loyal'     => 'pelanggan setia',
            'potential' => 'pelanggan potensial',
            'budget'    => 'pencari promo',
            'seasonal'  => 'pelanggan musiman',
            'at_risk'   => 'pelanggan kami',
            default     => 'Bapak/Ibu',
        };
        return str_replace(['{nama}', '{segmen}'], [$nama, $segmen], $pesan);
    }
}
