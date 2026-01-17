<?php

namespace App\Mail;

use App\Models\Convite;
use App\Models\Empresa;
use App\Models\Filial;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmpresaInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Convite $convite,
        public Empresa $empresa,
        public Filial $filial
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Convite para acessar o sistema'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.empresa_invite',
            with: [
                'user' => $this->convite->user,
                'empresa' => $this->empresa,
                'filial' => $this->filial,
                'acceptUrl' => route('invites.show', $this->convite->token),
            ]
        );
    }
}
