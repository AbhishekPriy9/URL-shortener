<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\Shortener;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $admins = User::where('role', 'Admin')
            ->latest('id')->withCount('urls', 'members', 'memberUrls')
            ->paginate(5, ['*'], 'admins_page');

        $shortURLs = Shortener::with('user', 'user.referredby')
            ->latest('id')
            ->paginate(5, ['*'], 'short_url_page');

        return view('super.dashboard.index', compact('admins', 'shortURLs'));
    }
}
