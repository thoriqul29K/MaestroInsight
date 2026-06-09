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

    public function data()
    {
        $allowedSegments = ['loyal', 'potential', 'budget', 'seasonal', 'at_risk'];
        $allowedPerPage  = [25, 50, 100, 'all'];
        $allowedSort     = [
            'no'      => 'id',
            'nama'    => 'nama_pelanggan',
            'kontak'  => 'telepon',
            'agama'   => 'agama',
            'segment' => 'segment',
        ];
        $allowedDir = ['asc', 'desc'];

        $segment = (string) ($this->request->getGet('segment') ?? '');
        if (! in_array($segment, $allowedSegments, true)) {
            $segment = '';
        }

        $perPageRaw = $this->request->getGet('per_page');
        if (is_numeric($perPageRaw)) {
            $perPage = (int) $perPageRaw;
        } else {
            $perPage = (string) $perPageRaw;
        }
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 50;
        }

        $sortKey = (string) ($this->request->getGet('sort') ?? 'no');
        $sort    = $allowedSort[$sortKey] ?? 'nama_pelanggan';

        $dir = strtolower((string) ($this->request->getGet('dir') ?? 'asc'));
        if (! in_array($dir, $allowedDir, true)) {
            $dir = 'asc';
        }

        $page = max((int) ($this->request->getGet('page') ?: 1), 1);

        $base = $this->model;
        if ($segment !== '') {
            $base = $base->where('segment', $segment);
        }

        $total = $base->countAllResults(false);

        if ($perPage === 'all') {
            $rows       = $base->orderBy($sort, $dir)->findAll();
            $totalPages = $total > 0 ? 1 : 0;
            $page       = 1;
        } else {
            $perPageInt = (int) $perPage;
            $offset     = ($page - 1) * $perPageInt;
            $rows       = $base->orderBy($sort, $dir)->findAll($perPageInt - 1, $offset);
            $totalPages = (int) ceil($total / $perPageInt);
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

    public function kirim()
    {
        $rules = [
            'id_pelanggan' => 'required',
            'channel'      => 'required|in_list[email]',
            'subject'      => 'required|max_length[255]',
            'pesan'        => 'required|min_length[3]',
            'gambar'       => 'permit_empty|is_image[gambar]|max_size[gambar,5120]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $ids     = (array) $this->request->getPost('id_pelanggan');
        $channel = $this->request->getPost('channel');
        $subject = trim((string) $this->request->getPost('subject'));
        $pesan   = (string) $this->request->getPost('pesan');

        $pelangganList = $this->model->whereIn('id', $ids)->findAll();
        $withEmail     = array_values(array_filter($pelangganList, static fn($p) => ! empty($p['email'])));

        if (empty($withEmail)) {
            return redirect()->back()->withInput()->with('error', 'Tidak ada pelanggan yang memiliki alamat email valid pada pilihan Anda.');
        }

        $attachmentPath = null;
        $attachmentName = null;

        $fileGambar = $this->request->getFile('gambar');
        if ($fileGambar !== null && $fileGambar->isValid() && ! $fileGambar->hasMoved()) {
            $attachmentName = $fileGambar->getRandomName();
            $fileGambar->move(WRITEPATH . 'uploads/promosi', $attachmentName);
            $attachmentPath = WRITEPATH . 'uploads/promosi' . DIRECTORY_SEPARATOR . $attachmentName;
        }

        $results = $this->sender->send('email', $withEmail, $subject, $pesan, [$this, 'personalize'], $attachmentPath);

        $sent   = 0;
        $failed = 0;
        $errorDetails = [];

        foreach ($results as $r) {
            $p     = $r['pelanggan'];
            $res   = $r['result'];
            $isOk  = $res->success;

            $this->logModel->insert([
                'id_pelanggan'         => $p['id'] ?? null,
                'nama_pelanggan'       => $p['nama_pelanggan'] ?? null,
                'email_target'         => $p['email'] ?? null,
                'channel'              => $channel,
                'subject'              => $subject,
                'status'               => $isOk ? 'success' : 'failed',
                'error_message'        => $isOk ? null : ($res->errorMessage ?? 'Unknown error'),
                'attachment_filename'  => $attachmentName,
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
        $perPage = $this->request->getGet('per_page');

        $hasFilter = ($status !== null && $status !== '') || ($channel !== null && $channel !== '');

        $allowedPerPage = [25, 50, 100, 200, 500, 'all'];
        if (is_numeric($perPage)) {
            $perPage = (int) $perPage;
        }
        $perPage = in_array($perPage, $allowedPerPage, true) ? $perPage : 50;

        if ($perPage === 'all') {
            $logs  = $this->logModel->getFiltered(null, $status, $channel);
            $pager = null;
        } else {
            $page  = max((int) ($this->request->getGet('page') ?: 1), 1);
            $total = $this->logModel->countFiltered($status, $channel);
            $logs  = $this->logModel->getFiltered((int) $perPage, $status, $channel, ($page - 1) * (int) $perPage);

            $pager = service('pager');
            $pager->store('default', $page, (int) $perPage, $total, 0);
            $pager->only(['status', 'channel', 'per_page']);
        }

        $data = [
            'title'         => 'Riwayat Promosi',
            'pageTitle'     => 'Riwayat Promosi',
            'pageIcon'      => 'bi-clock-history',
            'logs'          => $logs,
            'filterStatus'  => $status ?? '',
            'filterChannel' => $channel ?? '',
            'hasFilter'     => $hasFilter,
            'filterCount'   => $hasFilter ? $this->logModel->countFiltered($status, $channel) : 0,
            'pager'         => $pager,
            'perPage'       => $perPage,
        ];
        return view('pages/promosi/riwayat', $data);
    }

    public function hapusLog()
    {
        $mode = $this->request->getPost('mode');

        if ($mode === 'filtered') {
            $status  = $this->request->getPost('filter_status');
            $channel = $this->request->getPost('filter_channel');
            $deleted = $this->logModel->deleteFiltered(
                ($status  !== null && $status  !== '') ? $status  : null,
                ($channel !== null && $channel !== '') ? $channel : null,
            );
            $msg = "Berhasil menghapus {$deleted} riwayat promosi (sesuai filter aktif).";
        } else {
            $ids = (array) $this->request->getPost('id_log');
            $ids = array_values(array_filter($ids, static fn($v) => ctype_digit((string) $v)));

            if (empty($ids)) {
                return redirect()->to('/promosi/riwayat')
                    ->with('error', 'Pilih minimal satu riwayat untuk dihapus.');
            }

            $deleted = $this->logModel->whereIn('id', $ids)->delete();
            $msg     = "Berhasil menghapus {$deleted} riwayat promosi.";
        }

        return redirect()->to('/promosi/riwayat')
            ->with($deleted > 0 ? 'success' : 'error', $msg);
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
