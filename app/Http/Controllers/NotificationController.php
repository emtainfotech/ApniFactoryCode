<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Get live notifications data for the Seller Top Header Bell dropdown.
     */
    public function getSellerHeaderData()
    {
        if (!Auth::check()) {
            return response()->json(['count' => 0, 'notifications' => []]);
        }

        $userId = Auth::user()->id;

        // Fetch unread count
        $unreadCount = DB::table('notifications')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere(function ($sub) use ($userId) {
                      $sub->where('customer_id', $userId)
                          ->where('type', 'seller');
                  });
            })
            ->where('msgread', 0)
            ->count();

        // Fetch latest 10 notifications
        $notifications = DB::table('notifications')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere(function ($sub) use ($userId) {
                      $sub->where('customer_id', $userId)
                          ->where('type', 'seller');
                  });
            })
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $formatted = $notifications->map(function ($item) {
            $created = Carbon::parse($item->created_at);
            
            // Determine icon and color scheme based on title/msg
            $icon = 'bx bx-bell';
            $bgClass = 'bg-light-primary text-primary';
            $targetUrl = route('order.list');

            $titleLower = strtolower($item->title ?? '');
            $msgLower = strtolower($item->msg ?? '');

            if (str_contains($titleLower, 'order') || str_contains($msgLower, 'order')) {
                $icon = 'bx bx-cart-alt';
                $bgClass = 'bg-light-danger text-danger';
                
                // Extract order number if present
                preg_match('/#([A-Za-z0-9\-]+)/', ($item->title . ' ' . $item->msg), $matches);
                if (!empty($matches[1])) {
                    $targetUrl = route('order.detail', $matches[1]);
                }
            } elseif (str_contains($titleLower, 'expired') || str_contains($titleLower, 'reject')) {
                $icon = 'bx bx-error-circle';
                $bgClass = 'bg-light-warning text-warning';
            } elseif (str_contains($titleLower, 'price') || str_contains($titleLower, 'paint')) {
                $icon = 'bx bx-paint';
                $bgClass = 'bg-light-info text-info';
                $targetUrl = route('seller.paint-pricing.index');
            } elseif (str_contains($titleLower, 'success') || str_contains($titleLower, 'complete') || str_contains($titleLower, 'accept')) {
                $icon = 'bx bx-check-shield';
                $bgClass = 'bg-light-success text-success';
            }

            return [
                'id'         => $item->id,
                'title'      => $item->title ?? 'Notification',
                'msg'        => $item->msg ?? '',
                'msgread'    => (int) $item->msgread,
                'time_ago'   => $created->diffForHumans(),
                'icon'       => $icon,
                'bg_class'   => $bgClass,
                'target_url' => $targetUrl,
            ];
        });

        return response()->json([
            'count'         => $unreadCount,
            'notifications' => $formatted,
        ]);
    }

    /**
     * View all notifications full-page for Seller.
     */
    public function indexSeller(Request $request)
    {
        $userId = Auth::user()->id;

        $query = DB::table('notifications')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere(function ($sub) use ($userId) {
                      $sub->where('customer_id', $userId)
                          ->where('type', 'seller');
                  });
            });

        // Filter by unread
        if ($request->get('filter') === 'unread') {
            $query->where('msgread', 0);
        }

        // Search query
        if ($request->filled('q')) {
            $search = $request->get('q');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('msg', 'like', "%{$search}%");
            });
        }

        $notifications = $query->orderBy('id', 'desc')->paginate(15);
        $unreadCount = DB::table('notifications')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere(function ($sub) use ($userId) {
                      $sub->where('customer_id', $userId)
                          ->where('type', 'seller');
                  });
            })
            ->where('msgread', 0)
            ->count();

        return view('seller.notifications', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark a specific notification as read for Seller.
     */
    public function markSellerAsRead($id)
    {
        $userId = Auth::user()->id;

        DB::table('notifications')
            ->where('id', $id)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('customer_id', $userId);
            })
            ->update(['msgread' => 1, 'updated_at' => now()]);

        return response()->json(['status' => true, 'message' => 'Notification marked as read']);
    }

    /**
     * Mark all notifications as read for Seller.
     */
    public function markAllSellerAsRead()
    {
        $userId = Auth::user()->id;

        DB::table('notifications')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere(function ($sub) use ($userId) {
                      $sub->where('customer_id', $userId)
                          ->where('type', 'seller');
                  });
            })
            ->update(['msgread' => 1, 'updated_at' => now()]);

        return response()->json(['status' => true, 'message' => 'All notifications marked as read']);
    }

    /**
     * API for Customer mobile app to fetch notification feed.
     */
    public function customerNotificationList(Request $request)
    {
        $customerId = $request->get('customer_id') ?? $request->get('userid');

        if (!$customerId) {
            return response()->json([
                'status' => false,
                'code'   => 400,
                'msg'    => 'customer_id or userid parameter is required',
                'data'   => []
            ], 400);
        }

        $notifications = DB::table('notifications')
            ->where(function ($q) use ($customerId) {
                $q->where('customer_id', $customerId)
                  ->where(function ($sub) {
                      $sub->whereNull('type')
                          ->orWhere('type', 'customer');
                  });
            })
            ->orderBy('id', 'desc')
            ->get();

        $unreadCount = $notifications->where('msgread', 0)->count();

        $formatted = $notifications->map(function ($item) {
            $created = Carbon::parse($item->created_at);
            return [
                'id'         => $item->id,
                'title'      => $item->title ?? 'Order Update',
                'body'       => $item->msg ?? '',
                'msg'        => $item->msg ?? '',
                'msgread'    => (int) $item->msgread,
                'created_at' => $created->format('Y-m-d H:i:s'),
                'time_ago'   => $created->diffForHumans(),
            ];
        });

        return response()->json([
            'status'       => true,
            'code'         => 100,
            'msg'          => 'Notifications retrieved successfully',
            'unread_count' => $unreadCount,
            'data'         => $formatted,
        ]);
    }

    /**
     * API for Customer mobile app to mark notification(s) as read.
     */
    public function customerMarkAsRead(Request $request)
    {
        $id = $request->get('id') ?? $request->get('notification_id');
        $customerId = $request->get('customer_id') ?? $request->get('userid');
        $all = $request->get('all') ?? false;

        if ($all && $customerId) {
            DB::table('notifications')
                ->where('customer_id', $customerId)
                ->where(function ($sub) {
                    $sub->whereNull('type')->orWhere('type', 'customer');
                })
                ->update(['msgread' => 1, 'updated_at' => now()]);

            return response()->json(['status' => true, 'code' => 100, 'msg' => 'All notifications marked as read']);
        }

        if ($id) {
            DB::table('notifications')
                ->where('id', $id)
                ->update(['msgread' => 1, 'updated_at' => now()]);

            return response()->json(['status' => true, 'code' => 100, 'msg' => 'Notification marked as read']);
        }

        return response()->json(['status' => false, 'code' => 400, 'msg' => 'id or all=true parameter is required'], 400);
    }
}
