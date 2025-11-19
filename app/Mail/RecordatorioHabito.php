<?php

namespace App\Mail;

use App\Models\Habito;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecordatorioHabito extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public Habito $habito,
        public ?string $mensajePersonalizado = null
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "⏰ Recordatorio: {$this->habito->nombre}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.recordatorio-habito',
            with: [
                'userName' => $this->user->name,
                'habitoNombre' => $this->habito->nombre,
                'habitoDescripcion' => $this->habito->descripcion,
                'habitoEmoji' => $this->habito->emoji ?? '⭐',
                'rachaActual' => $this->habito->racha_actual,
                'rachaMaxima' => $this->habito->racha_maxima,
                'mensajePersonalizado' => $this->mensajePersonalizado,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
