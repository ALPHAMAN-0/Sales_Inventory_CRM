<?php

namespace App\Domain\Crm\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\VonageMessage;
use Illuminate\Notifications\Notification;

/**
 * Win-back offer for lost customers. Multi-channel via the Notification
 * abstraction: SMS + email when a phone is on file, email otherwise. The
 * `database` channel doubles as a sent-message audit log. Queued, so a bulk
 * campaign never blocks a request.
 */
class ReengagementOffer extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $campaign = 'We miss you!') {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Email + a database audit row are always sent. SMS is added only when a
        // phone is on file AND Vonage credentials are configured, so a dev/test
        // environment with no SMS keys degrades gracefully to email-only instead
        // of failing the whole queued notification on the missing channel.
        $channels = ['mail', 'database'];

        // The vonage package publishes its config under the top-level `vonage`
        // key (VONAGE_KEY -> vonage.api_key), NOT services.vonage — so gate on
        // that, otherwise SMS could never activate even when fully configured.
        if ($notifiable->phone && config('vonage.api_key')) {
            array_unshift($channels, 'vonage');
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->campaign)
            ->greeting("Hi {$notifiable->name},")
            ->line("We've missed you — here's 15% off your next order.")
            ->action('Shop now', config('app.url'))
            ->line('Hope to see you again soon!');
    }

    public function toVonage(object $notifiable): VonageMessage
    {
        return (new VonageMessage)
            ->content("Hi {$notifiable->name}, we miss you! Enjoy 15% off your next order.");
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'reengagement_offer',
            'campaign' => $this->campaign,
        ];
    }
}
