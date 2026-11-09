<?php

namespace App\Console\Commands;

use App\Models\PaymentReminder;
use App\Services\ReminderDispatchService;
use Illuminate\Console\Command;

class SendScheduledRemindersCommand extends Command
{
    protected $signature = 'reminders:send-due';

    protected $description = 'Kirim otomatis reminder pembayaran umum yang berstatus scheduled dan waktunya sudah tiba.';

    public function handle(ReminderDispatchService $dispatchService): int
    {
        $dueReminders = PaymentReminder::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->get();

        if ($dueReminders->isEmpty()) {
            $this->info('Tidak ada reminder terjadwal yang jatuh tempo saat ini.');

            return self::SUCCESS;
        }

        foreach ($dueReminders as $reminder) {
            $result = $dispatchService->dispatchReminder($reminder);
            $this->line("[Reminder #{$reminder->id} - {$reminder->title}] Status: {$result['status']} ({$result['notice']})");
        }

        return self::SUCCESS;
    }
}

