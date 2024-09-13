<?php


namespace VaahCms\Themes\BulmaBlogTheme\Mail;


use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;


class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;


    public $request;

    /**
     * Create a new message instance.
     *
     * @param $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('bulmablogtheme::frontend.emails.contact-form');
    }

}
