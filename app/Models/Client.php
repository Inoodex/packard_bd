<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'highest_designation',
        'phone',
        'email',
        'address'
    ];

    /**
     * Find existing client by phone or email
     * Returns null if no duplicate found
     */
    public static function findByPhoneOrEmail($phone, $email)
    {
        // Skip search for placeholder values
        if (!empty($email) && !preg_match('/^no-email-\d+@local\.test$/', $email)) {
            $client = static::where('email', $email)->first();
            if ($client) return $client;
        }

        // Skip search for placeholder values like '-'
        if (!empty($phone) && $phone !== '-') {
            $client = static::where('phone', $phone)->first();
            if ($client) return $client;
        }

        return null;
    }
}