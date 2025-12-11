<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shortener;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $shortURLs = Shortener::with('user')
            ->where(function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('invited_by', auth()->id());
                })->orWhere('user_id', auth()->id());
            })
            ->latest('id')
            ->paginate(5, ['*'], 'short_url_page');

        $members = User::where('invited_by', auth()->id())
            ->latest('id')->withCount('urls')
            ->paginate(5, ['*'], 'members_page');

        return view('admin.dashboard.index', compact('shortURLs', 'members'));
    }
}
