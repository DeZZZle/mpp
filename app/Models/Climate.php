<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $device_uuid
 * @property string $type
 * @property float $value
 * @property Carbon|null $last_updated_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @use Eloquent
 */
class Climate extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'value' => 'float',
        'last_updated_at' => 'datetime',
    ];
}
