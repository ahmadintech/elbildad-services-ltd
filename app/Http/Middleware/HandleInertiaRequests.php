<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $notifications = [];
        if ($request->user()) {
            if ($request->user()->hasRole(['admin', 'owner'])) {
                $notifications = \Illuminate\Notifications\DatabaseNotification::latest()->limit(5)->get()->map(function ($n) {
                    return [
                        'id' => $n->id,
                        'type' => $n->data['type'] ?? 'System Activity',
                        'message' => $n->data['message'] ?? 'Activity occurred',
                        'action_url' => $n->data['action_url'] ?? '#',
                        'time' => $n->created_at->diffForHumans(),
                    ];
                });
            } else {
                $notifications = $request->user()->unreadNotifications()->limit(5)->get()->map(function ($n) {
                    return [
                        'id' => $n->id,
                        'type' => $n->data['type'] ?? 'Notification',
                        'message' => $n->data['message'] ?? 'New notification',
                        'action_url' => $n->data['action_url'] ?? '#',
                        'time' => $n->created_at->diffForHumans(),
                    ];
                });
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? array_merge(
                    $request->user()->toArray(),
                    ['roles' => $request->user()->getRoleNames()->toArray()]
                ) : null,
                'notifications' => $notifications,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'currency' => [
                'symbol' => env('APP_CURRENCY_SYMBOL', '₦'),
                'code'   => env('APP_CURRENCY_CODE', 'NGN'),
            ],
        ];
    }
}
