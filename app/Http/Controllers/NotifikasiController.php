<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index(Request $request)
    {
        $query = Notifikasi::where('user_id', Auth::id());

        // Filter by read status
        if ($request->has('filter')) {
            if ($request->filter === 'unread') {
                $query->where('dibaca', false);
            } elseif ($request->filter === 'read') {
                $query->where('dibaca', true);
            }
        }

        // Filter by type
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        $notifikasi = $query->latest()->paginate(20);

        $unreadCount = Notifikasi::where('user_id', Auth::id())
            ->where('dibaca', false)
            ->count();

        return view('notifikasi.index', compact('notifikasi', 'unreadCount'));
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notifikasi $notifikasi)
    {
        // Check if notification belongs to current user
        if ($notifikasi->user_id !== Auth::id()) {
            abort(403);
        }

        $notifikasi->update([
            'dibaca' => true,
            'dibaca_pada' => now(),
            'read_at' => now(),
        ]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        // Redirect to link if exists
        if ($notifikasi->link) {
            return redirect($notifikasi->link);
        }

        return back()->with('success', 'Notifikasi ditandai sebagai dibaca');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Notifikasi::where('user_id', Auth::id())
            ->where('dibaca', false)
            ->update([
                'dibaca' => true,
                'dibaca_pada' => now(),
                'read_at' => now(),
            ]);

        return back()->with('success', 'Semua notifikasi ditandai sebagai dibaca');
    }

    /**
     * Delete a notification.
     */
    public function destroy(Notifikasi $notifikasi)
    {
        // Check if notification belongs to current user
        if ($notifikasi->user_id !== Auth::id()) {
            abort(403);
        }

        $notifikasi->delete();

        return back()->with('success', 'Notifikasi berhasil dihapus');
    }

    /**
     * Delete all read notifications.
     */
    public function deleteAllRead()
    {
        Notifikasi::where('user_id', Auth::id())
            ->where('dibaca', true)
            ->delete();

        return back()->with('success', 'Semua notifikasi yang sudah dibaca telah dihapus');
    }

    /**
     * Get unread count (for AJAX)
     */
    public function getUnreadCount()
    {
        $count = Notifikasi::where('user_id', Auth::id())
            ->where('dibaca', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications (for dropdown)
     */
    public function getRecent()
    {
        $notifikasi = Notifikasi::where('user_id', Auth::id())
            ->latest()
            ->take(5)
            ->get();

        return response()->json($notifikasi);
    }
}
