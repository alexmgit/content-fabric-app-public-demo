<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'user_options' => fn () => [
                'has_new_events' => $request->user() ? $request->user()->hasNewEvents() : false,
                'searches_enabled' => config('services.searches.enabled'),
                'is_admin' => $request->user() ? $request->user()->isAdmin() : false,
                'version' => config('app.version'),
                'favicon' => config('app.favicon.path'),
                'show_spent_this_month' => config('app.is_show_spent_this_month'),
                'is_use_plans' => config('app.is_use_plans'),
                'balance' => $request->user() ? $request->user()->balance : 0,
                'partner_enabled' => config('services.partner.enabled'),
            ],
        ];
    }
}
