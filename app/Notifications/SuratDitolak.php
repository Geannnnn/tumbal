<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SuratDitolak extends Notification
{
    use Queueable;

    protected $surat;

    public function __construct($surat)
    {
        $this->surat = $surat;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'id_surat' => $this->surat->id_surat,
            'judul_surat' => $this->surat->judul_surat,
            'pesan' => "Surat dengan judul <b>{$this->surat->judul_surat}</b> telah <span class='text-red-600 font-bold'>ditolak</span>.",
            'tanggal' => now(),
        ];
    }
}
