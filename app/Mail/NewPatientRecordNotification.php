<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewPatientRecordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $patient;
    protected $history;

    public function __construct($patient, $history)
    {
        $this->patient = $patient;
        $this->history = $history;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Ensure the ID name matches your database column
        $url = url('/dashboard/patients/' . ($this->history->patient_history_id ?? $this->history->id));

        return (new MailMessage)
            ->subject('New Patient Referral: ' . $this->patient->name)
            // Point to the blade file we just created
            ->view('emails.new_patient_notification', [
                'patient_name'  => $this->patient->name,
                'matibabu_card' => $this->patient->matibabu_card,
                'case_type'     => $this->history->case_type,
                'url'           => $url,
            ]);
    }
}
