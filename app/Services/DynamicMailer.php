<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class DynamicMailer
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    protected function mailer()
    {

        config([
            'mail.mailers.smtp.host' => $this->config->host,
            'mail.mailers.smtp.port' => $this->config->port,
            'mail.mailers.smtp.encryption' => $this->config->encryption ?: null,
            'mail.mailers.smtp.username' => $this->config->username,
            'mail.mailers.smtp.password' => $this->config->password,
            'mail.from.address' => $this->config->from_address,
            'mail.from.name' => $this->config->from_name,
        ]);

        Mail::purge('smtp');
        return Mail::mailer('smtp');
    }

    public function send($to, $mailable)
    {
        $this->mailer()->to($to)->send($mailable);
    }
}
