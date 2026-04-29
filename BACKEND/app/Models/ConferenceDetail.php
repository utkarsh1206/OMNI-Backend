<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConferenceDetail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'caller_username',
        'caller_user_extension',
        'call_unique_id',
        'parent_uid',
        'conf_state',
        'conf_event',
        'parent_user_id',
        'original_called_phone_number',
        'actual_url_parameter',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'actual_url_parameter' => 'array',
        ];
    }

    /**
     * Get the user that owns the conference detail.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent user.
     */
    public function parentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_uid');
    }
}
