<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * @var list<string>
     */
    public const SUPPORTED = ['en', 'ps', 'fa'];

    /**
     * @var list<string>
     */
    public const RTL = ['ps', 'fa'];

    public static function isRtl(?string $locale = null): bool
    {
        return in_array($locale ?? App::getLocale(), self::RTL, true);
    }

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', config('app.locale'));

        if (in_array($locale, self::SUPPORTED, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
