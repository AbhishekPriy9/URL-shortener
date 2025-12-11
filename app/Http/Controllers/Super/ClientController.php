<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\Mail\SendInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    public function create()
    {
        return view('super.clients.create');
    }

    public function store(ClientRequest $request)
    {
        $data = $request->validated();
        $data['role'] = 'Admin';
        $password = Str::random(8);
        $data['password'] = Hash::make($password);
        $user = User::create($data);
        Mail::to($user->email)->send(new SendInvitation($user, $password));

        return redirect()->route('super.dashboard.index')->with('success', 'Invitation sent successfully');
    }
}
