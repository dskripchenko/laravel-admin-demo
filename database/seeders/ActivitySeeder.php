<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PostStatus;
use App\Models\Blog\Post;
use App\Models\Shop\Customer;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use Closure;
use Dskripchenko\LaravelAdmin\Panel\Panels;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * A week of the staff's work, so the audit log has a history to show from the
 * first visit: on working days support moves orders along in the morning, the
 * editor tops up stock and polishes the blog, and the administrator looks in
 * now and then.
 *
 * The changes go through Eloquent while the author is signed in, at their
 * moment of the week (Carbon's test clock), so the core's Loggable trait and
 * its auth listener write the entries — actor, diff and time — exactly as
 * the panel would.
 */
class ActivitySeeder extends Seeder
{
    private string $guard;

    public function run(): void
    {
        $this->guard = Panels::currentGuard();
        $users = config('admin.auth.model')::query()->get()->keyBy('email');
        $ada = $users['admin@demo.test'];
        $eddie = $users['editor@demo.test'];
        $support = $users->first(fn (Model $user) => str_ends_with($user->email, '@staff.demo.test')
            && $user->roles()->where('slug', 'support')->exists() && $user->is_active);

        $now = Carbon::now();
        try {
            for ($days = 6; $days >= 0; $days--) {
                $day = $now->copy()->subDays($days)->startOfDay();
                $weekday = ! $day->isWeekend();

                if (! $weekday) {
                    continue;
                }

                $this->as($support, $day->copy()->setTime(8, 52 + $days % 5), $now, function (Carbon $at) use ($days): void {
                    $pending = Order::query()->where('status', OrderStatus::Pending->value)->where('placed_at', '<', $at)->orderBy('placed_at')->limit(2 + $days % 2)->get();
                    $pending->each(fn (Order $order) => $order->transitionTo(OrderStatus::Paid));
                    Order::query()->where('status', OrderStatus::Paid->value)->where('paid_at', '<', $at->copy()->subDay())->orderBy('paid_at')->limit(2)->get()
                        ->each(fn (Order $order) => $order->transitionTo(OrderStatus::Shipped));
                });

                $this->as($eddie, $day->copy()->setTime(9, 40 + 3 * ($days % 4)), $now, function () use ($days): void {
                    Product::query()->where('stock', '<', 10)->orderBy('stock')->orderBy('id')->limit(2)->get()
                        ->each(fn (Product $product) => $product->update(['stock' => $product->stock + 25 + 5 * ($days % 3)]));

                    if ($days % 2 === 0) {
                        $post = Post::query()->where('status', PostStatus::Review->value)->orderBy('id')->first();
                        $post?->update(['status' => PostStatus::Published->value, 'published_at' => now()]);
                    } else {
                        $product = Product::query()->orderBy('id')->skip(10 + $days)->first();
                        $product?->update(['price' => round((float) $product->price * 0.9, 2)]);
                    }
                });

                if ($days % 3 === 1) {
                    $this->as($ada, $day->copy()->setTime(11, 15), $now, function () use ($days): void {
                        $customer = Customer::query()->orderBy('id')->skip(20 + $days)->first();
                        $customer?->update(['segment' => 'vip', 'notes' => 'Moved to VIP after a call with the account manager.']);
                    });
                }
            }
        } finally {
            Carbon::setTestNow();
            Auth::guard($this->guard)->forgetUser();
        }
    }

    /**
     * Signs the user in at the given moment (the login is audited too) and
     * runs their changes a few minutes later. Moments still ahead of now are
     * skipped.
     *
     * @param  Closure(Carbon): void  $work
     */
    private function as(?Model $user, Carbon $at, Carbon $now, Closure $work): void
    {
        if ($user === null || $at->copy()->addMinutes(10) > $now) {
            return;
        }

        Carbon::setTestNow($at);
        Auth::guard($this->guard)->setUser($user);
        event(new Login($this->guard, $user, false));

        Carbon::setTestNow($at->copy()->addMinutes(4)->addSeconds(17));
        $work(Carbon::now());
    }
}
