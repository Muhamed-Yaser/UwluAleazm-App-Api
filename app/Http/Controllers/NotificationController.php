<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Session;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificationController extends Controller
{
    public function sendMeetingReminders()
    {
        // Get sessions that start in the next 5 minutes
        $fiveMinutesFromNow = Carbon::now()->addMinutes(5);
        $sessions = Session::where('scheduled_time', $fiveMinutesFromNow)->with(['student.user', 'teacher.user'])->get();

        foreach ($sessions as $session) {
            // Get student and teacher device tokens
            $studentDeviceToken = $session->student->user->device_token;
            $teacherDeviceToken = $session->teacher->user->device_token;

            // Notification message content
            $title = 'Meeting Reminder';
            $body = 'Your Quran memorization session is starting in 5 minutes.';

            // Send notifications
            if ($studentDeviceToken) {
                $this->sendNotification($studentDeviceToken, $title, $body);
            }
            if ($teacherDeviceToken) {
                $this->sendNotification($teacherDeviceToken, $title, $body);
            }
        }

        return response()->json(['message' => 'Meeting reminders sent successfully'], 200);
    }

    public function sendNotification($deviceToken, $title, $body)
    {
        if (!$deviceToken) {
            return;
        }
        // Get Firebase Messaging Instance
        $messaging = app('firebase.messaging');

        // Create Notification Content
        $notification = Notification::create($title, $body);

        // Build Cloud Message
        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification($notification)
            ->withData(['click_action' => 'FLUTTER_NOTIFICATION_CLICK']);

        // Send the Notification
        try {
            $messaging->send($message);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send notification: ' . $e->getMessage()], 500);
        }
    }
}
