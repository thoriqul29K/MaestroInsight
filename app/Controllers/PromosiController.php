<?php

namespace App\Controllers;

use App\Models\PelangganModel;

class PromosiController extends BaseController
{
    protected PelangganModel $model;

    public function __construct()
    {
        $this->model = new PelangganModel();
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
        $ids     = $this->request->getPost('id_pelanggan') ?? [];
        $channel = $this->request->getPost('channel') ?? 'whatsapp';
        $pesan   = $this->request->getPost('pesan') ?? '';

        if (empty($ids) || empty($pesan)) {
            return redirect()->back()->with('error', 'Pilih minimal satu pelanggan dan isi pesan.');
        }

        $kontak = $this->model->whereIn('id', $ids)->findAll();

        $sent = 0;
        foreach ($kontak as $k) {
            if ($channel === 'whatsapp' && ! empty($k['telepon'])) {
                $phone = preg_replace('/[^0-9]/', '', $k['telepon']);
                if (substr($phone, 0, 1) === '0') {
                    $phone = '62' . substr($phone, 1);
                }
                $url = 'https://wa.me/' . $phone . '?text=' . rawurlencode($this->personalize($pesan, $k));
                $sent++;
            } elseif ($channel === 'email' && ! empty($k['email'])) {
                $url = 'mailto:' . $k['email'] . '?subject=' . rawurlencode('Promo Spesial Maestro') . '&body=' . rawurlencode($this->personalize($pesan, $k));
                $sent++;
            }
        }

        return redirect()->back()->with('success', "Tautan promosi siap untuk {$sent} pelanggan. Klik untuk membuka aplikasi.");
    }

    private function personalize(string $pesan, array $pelanggan): string
    {
        $nama = explode(' ', $pelanggan['nama_pelanggan'])[0] ?? $pelanggan['nama_pelanggan'];
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
