<?php

namespace App\Http\Middleware;

use Closure;
use Cron\CronExpression;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fills in the installation banner of the demo stand per request: the text in
 * the visitor's language and a countdown to the next scheduled reset. Both
 * are dynamic, so they cannot live in the (cacheable) config file.
 */
final class DemoNotice
{
    public function handle(Request $request, Closure $next): Response
    {
        $text = (string) config('admin.notice.text');
        if ($text !== '') {
            config(['admin.notice.text' => __($text)]);

            $cron = (string) config('demo.reset_cron');
            if ($cron !== '' && CronExpression::isValidExpression($cron)) {
                config([
                    'admin.notice.countdown_to' => (new CronExpression($cron))->getNextRunDate()->format(DATE_ATOM),
                    'admin.notice.countdown_label' => __((string) (config('admin.notice.countdown_label') ?: 'Next reset in')),
                ]);
            }
        }

        return $next($request);
    }
}
