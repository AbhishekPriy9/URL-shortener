<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InviteRequest;
use App\Mail\SendInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(InviteRequest $request)
    {
        $data = $request->validated();
        $password = Str::random(8);
        $data['password'] = Hash::make($password);
        $data['invited_by'] = auth()->id();
        $user = User::create($data);
        Mail::to($user->email)->send(new SendInvitation($user, $password));

        return redirect()->route('admin.dashboard.index')->with('success', 'Invitation sent successfully');
    }
}
