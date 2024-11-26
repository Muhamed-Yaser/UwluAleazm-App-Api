<?php

namespace App\Console;

use Carbon\Carbon;
use App\Models\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use App\Notifications\MeetingReminderNotification;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    // protected function schedule(Schedule $schedule): void
    // {
    //     // $schedule->command('inspire')->hourly();
    //     $schedule->command('send:meeting-reminders')->everyMinute();
    // }

    protected function schedule(Schedule $schedule): void
    {
        // Schedule to check every minute if any session needs reminders
        $schedule->call(function () {
            // $fiveMinutesFromNow = Carbon::now('Africa/Cairo')->addMinutes(5);

            // $sessions = Session::where('scheduled_time', $fiveMinutesFromNow)
            //     ->with(['student.user', 'teacher.user'])
            //     ->get();

            $fiveMinutesFromNow = Carbon::now('Africa/Cairo')->addMinutes(5);
            $sessions = Session::whereBetween('scheduled_time', [
                Carbon::now('Africa/Cairo'),
                $fiveMinutesFromNow,
            ])
                ->where('reminder_sent', false) //to send notification only one time before 5 min not every minute in these five minutes!
                ->with(['student.user', 'teacher.user'])
                ->get();


            foreach ($sessions as $session) {
                $meetingDetails = [
                    'surah_name' => $session->surah_name,
                    'verses_from' => $session->verses_from,
                    'verses_to' => $session->verses_to,
                    'scheduled_time' => $session->scheduled_time,
                ];

                if ($session->student) {
                    $session->student->user->notify(new MeetingReminderNotification($meetingDetails));
                }

                if ($session->teacher) {
                    $session->teacher->user->notify(new MeetingReminderNotification($meetingDetails));
                }

                $session->reminder_sent = true;
                $session->save();
            }
        })->everyMinute(); //php artisan schedule:work >>>>>>>>>>!
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
