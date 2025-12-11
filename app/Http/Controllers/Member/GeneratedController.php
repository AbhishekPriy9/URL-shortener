<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Shortener;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GeneratedController extends Controller
{
    public function create()
    {
        return view('member.urls.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'long_url' => 'required|url',
        ]);

        do {
            $short = strtolower(Str::random(8));
        } while (Shortener::where('short_url', $short)->exists());

        Shortener::create([
            'long_url' => $request->long_url,
            'short_url' => $short,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('member.dashboard.index')
            ->with('success', 'Short URL generated successfully.');
    }
}
