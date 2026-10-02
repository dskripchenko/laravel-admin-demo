<?php

namespace App\Jobs;

use App\Models\Shop\Customer;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

/**
 * Sends one issue of the newsletter to a chunk of subscribers. An issue is a
 * batch of these, released a minute apart to stay under the mail provider's
 * rate limit.
 *
 * The demo delivers no mail: the messages go through the `array` mailer and
 * stay in memory.
 */
final class SendNewsletterChunk implements ShouldQueue
{
    use Batchable, Queueable;

    /** @param  list<int>  $customerIds */
    public function __construct(public string $issue, public array $customerIds) {}

    public function handle(): int
    {
        if ($this->batch()?->cancelled()) {
            return 0;
        }

        $sent = 0;
        Customer::query()->whereKey($this->customerIds)->where('accepts_marketing', true)
            ->each(function (Customer $customer) use (&$sent): void {
                Mail::mailer('array')->raw("Hello {$customer->name}, here is {$this->issue}.", function (Message $message) use ($customer): void {
                    $message->to($customer->email)->subject($this->issue);
                });
                $sent++;
            });

        return $sent;
    }
}
