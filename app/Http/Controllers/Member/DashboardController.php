<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Shortener;

class DashboardController extends Controller
{
    public function index()
    {
        $shortURLs = Shortener::latest('id')
            ->where('user_id', auth()->user()->id)
            ->paginate(5, ['*'], 'short_url_page');

        return view('member.dashboard.index', compact('shortURLs'));
    }
}
