<?php

namespace App\Libraries\Promosi;

use RuntimeException;

class PromosiSender
{
    protected array $channels = [];

    public function __construct()
    {
        $this->registerChannel('email', new EmailChannel());

        // Untuk tambah channel lain nanti (Telegram, WhatsApp gateway, SMS):
        //   $this->registerChannel('telegram', new TelegramChannel($token));
        //   $this->registerChannel('whatsapp',  new WhatsappChannel($token));
    }

    public function registerChannel(string $name, ChannelInterface $channel): void
    {
        $this->channels[$name] = $channel;
    }

    public function hasChannel(string $name): bool
    {
        return isset($this->channels[$name]);
    }

    /**
     * @param  array<int,array<string,mixed>> $pelangganList
     * @return array<int,array{pelanggan: array<string,mixed>, result: SendResult}>
     */
    public function send(string $channelName, array $pelangganList, string $subject, string $body, ?callable $personalizer = null, ?string $attachment = null): array
    {
        if (! isset($this->channels[$channelName])) {
            throw new RuntimeException("Channel '{$channelName}' belum terdaftar di PromosiSender.");
        }

        $channel = $this->channels[$channelName];
        $results = [];

        foreach ($pelangganList as $p) {
            $finalBody = $personalizer ? $personalizer($body, $p) : $body;
            $results[] = [
                'pelanggan' => $p,
                'result'    => $channel->send($p, $subject, $finalBody, $attachment),
            ];
        }

        return $results;
    }
}
