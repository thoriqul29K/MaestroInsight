<?php

namespace App\Libraries\Promosi;

use CodeIgniter\Email\Email;

class EmailChannel implements ChannelInterface
{
    public function send(array $pelanggan, string $subject, string $body, ?string $attachment = null): SendResult
    {
        if (empty($pelanggan['email'])) {
            return new SendResult(false, 'Alamat email pelanggan kosong.');
        }

        $email = service('email');

        $email->clear();
        $email->setFrom(
            env('email.fromEmail'),
            env('email.fromName', 'Maestro'),
        );
        $email->setTo($pelanggan['email']);
        $email->setSubject($subject);
        $email->setMessage($body);

        if ($attachment !== null && is_file($attachment)) {
            $email->attach($attachment);
        }

        if ($email->send(false)) {
            return new SendResult(true);
        }

        $debug = $email->getDebugger();
        $err   = $debug['smtp_msg']
            ?? $debug['error']
            ?? ($debug['debug'] ?? null)
            ?? 'Gagal mengirim email (tanpa detail).';

        if (is_array($err)) {
            $err = implode(' | ', array_map('strval', $err));
        }

        return new SendResult(false, $err);
    }
}
