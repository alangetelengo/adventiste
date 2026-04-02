<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\NotificationInterne;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notificationsInternes()
            ->paginate(30);

        return view('notifications.index', compact('notifications'));
    }

    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();
        $items = $user->notificationsInternes()
            ->whereNull('read_at')
            ->limit(10)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => (int) $n->id,
                    'title' => (string) $n->title,
                    'message' => (string) ($n->message ?? ''),
                    'url' => (string) ($n->url ?? ''),
                    'open_url' => route('notifications.open', $n),
                    'created_at_human' => (string) optional($n->created_at)->diffForHumans(),
                    'is_read' => $n->read_at !== null,
                ];
            })
            ->values();

        $unreadCount = $user->notificationsInternes()
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'items' => $items,
        ]);
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()
            ->notificationsInternes()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()
            ->back()
            ->with('success', 'Notifications marquées comme lues.');
    }

    public function open(Request $request, NotificationInterne $notification): RedirectResponse
    {
        if ((int) $notification->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        if (! empty($notification->url)) {
            return redirect()->to($notification->url);
        }

        return redirect()->route('notifications.index');
    }
}
