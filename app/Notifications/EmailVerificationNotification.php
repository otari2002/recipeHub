<?php

namespace App\Notifications;

use App\Models\EmailOtp;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;


class EmailVerificationNotification extends Notification
{
    use Queueable;

    public $message;
    public $subject;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        $this->message = 'Use the code below for verification process';
        $this->subject = 'Verification Needed';

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $otp = rand(123456,999999);
        $expirationDate = Carbon::now()->addMinutes(10);
        EmailOtp::updateOrCreate(['email' => $notifiable->email] ,['otp' => Hash::make($otp), 'expiration_date' => $expirationDate]);
        return (new MailMessage)
            ->subject($this->subject)
            ->greeting('Hello '.$notifiable->fullName)
            ->line($this->message)
            ->line('Code : '.$otp);

    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
