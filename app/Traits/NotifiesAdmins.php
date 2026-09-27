<?php

namespace App\Traits;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Notification;

trait NotifiesAdmins
{
    /**
     * Send a notification to all administrators and gestionnaires.
     *
     * @param string $title
     * @param string $message
     * @param string $type
     * @param string|null $actionUrl
     * @return void
     */
    protected function notifyAdmins(string $title, string $message, string $type = 'info', ?string $actionUrl = null)
    {
        $usersToNotify = User::whereIn('role', ['admin', 'gestionnaire'])
            ->where('id', '!=', auth()->id())
            ->get();
        
        if ($usersToNotify->isNotEmpty()) {
            Notification::send($usersToNotify, new SystemNotification($title, $message, $type, $actionUrl));
        }
    }
}
