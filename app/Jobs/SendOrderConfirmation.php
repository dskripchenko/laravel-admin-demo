<?php

namespace App\Jobs;

use App\Models\Shop\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

/**
 * Emails the customer a summary of the order they have just placed.
 *
 * The demo delivers no mail: the message goes through the `array` mailer and
 * stays in memory, so retrying the job from the Jobs page is harmless.
 */
final class SendOrderConfirmation implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        $order = $this->order->loadMissing('customer', 'items');
        $email = $order->customer?->email;
        if ($email === null) {
            return;
        }

        $lines = [
            "Thank you for your order {$order->number}.",
            sprintf('%d item(s), %s in total.', $order->items->count(), number_format((float) $order->total, 2)),
        ];

        Mail::mailer('array')->raw(implode("\n", $lines), function (Message $message) use ($email, $order): void {
            $message->to($email)->subject("Your order {$order->number}");
        });
    }
}
