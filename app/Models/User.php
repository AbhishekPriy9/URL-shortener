<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'role',
        'email',
        'password',
        'invited_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function urls()
    {
        return $this->hasMany(Shortener::class);
    }

    public function members()
    {
        return $this->hasMany(User::class, 'invited_by');
    }

    public function referredby()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    // Get the URLs belonging to the Members invited by this User
    public function memberUrls()
    {
        return $this->hasManyThrough(
            Shortener::class, // Final Target (URLs)
            User::class,      // Intermediate Model (Members)
            'invited_by',    // Foreign Key on User table (Member points to Admin)
            'user_id',      // Foreign Key on Shortener table (URL points to Member)
            'id',            // Local Key on Admin table
            'id'       // Local Key on Member table
        );
    }
}
