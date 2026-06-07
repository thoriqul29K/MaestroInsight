<?php

namespace App\Libraries\Promosi;

interface ChannelInterface
{
    public function send(array $pelanggan, string $subject, string $body, ?string $attachment = null): SendResult;
}
