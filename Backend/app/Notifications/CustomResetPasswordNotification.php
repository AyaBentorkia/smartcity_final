<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        $url = "{$frontendUrl}/reset-password?token={$this->token}&email={$notifiable->email}";

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe')
            ->greeting('Bonjour,')
            ->line('Vous recevez cet email car une demande de réinitialisation de mot de passe a été effectuée pour votre compte.')
            ->action('Réinitialiser mon mot de passe', $url)
            ->line('Ce lien expirera dans 60 minutes.')
            ->line('Si vous n\'êtes pas à l\'origine de cette demande, ignorez cet email.')
            ->salutation('L\'équipe Incitya');
    }
}