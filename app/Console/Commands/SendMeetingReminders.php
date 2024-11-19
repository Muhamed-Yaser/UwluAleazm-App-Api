<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Http\Controllers\NotificationController;

class SendMeetingReminders extends Command
{
    protected $signature = 'send:meeting-reminders';
    protected $description = 'Send reminders to students and teachers about upcoming meetings';

    public function handle()
    {
        // Instantiate the NotificationController and call the method
        $notificationController = new NotificationController();
        $notificationController->sendMeetingReminders();

        return Command::SUCCESS;
    }
}
