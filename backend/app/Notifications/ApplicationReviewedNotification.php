<?php

namespace App\Notifications;

use App\Models\ApplicationReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationReviewedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ApplicationReview $review
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification (for the database channel).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $application = $this->review->application;

        return [
            'application_id'     => $application->id,
            'application_number' => $application->application_number,
            'decision'           => $this->review->decision?->value,
            'notes'              => $this->review->notes,
            'reviewer_name'      => $this->review->reviewer?->name,
            'reviewed_at'        => $this->review->created_at?->toISOString(),
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $decisionLabel = match ($this->review->decision?->value) {
            'approved'           => 'Disetujui',
            'rejected'           => 'Ditolak',
            'revision_requested' => 'Butuh Revisi',
            default              => 'Diproses',
        };

        return (new MailMessage)
            ->subject("Permohonan #{$this->review->application->application_number} – {$decisionLabel}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Permohonan Anda dengan nomor **{$this->review->application->application_number}** telah mendapatkan keputusan: **{$decisionLabel}**.")
            ->line("Catatan penilai: {$this->review->notes}")
            ->action('Lihat Permohonan', url('/applications/' . $this->review->application_id))
            ->line('Terima kasih telah menggunakan sistem kami.');
    }
}
