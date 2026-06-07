<?php

namespace App\Mail;


use Illuminate\Bus\Queueable;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CorreoDesdePlantilla extends configuracion_correo
{
    use Queueable, SerializesModels;

    public $asunto;
    public $contenido_html;

    /**
     * Crear una nueva instancia del mensaje.
     *
     * @param string $asunto
     * @param string $contenido_html
     */
    public function __construct($asunto, $contenido_html)
    {
        $this->asunto = $asunto;
        $this->contenido_html = $contenido_html;
        parent::__construct();
    }

    /**
     * Obtener el sobre del mensaje.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->asunto, // Establece el asunto dinámico
        );
    }

    /**
     * Obtener la definición del contenido del mensaje.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.plantilla_correo', // Vista donde se encuentra el contenido HTML
            with: [
                'contenido' => $this->contenido_html, // Pasamos el contenido HTML de la plantilla
            ],
        );
    }
}
