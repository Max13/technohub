<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    /** @inheritdoc */
    protected $fillable = [
        'is_from_ypareo',
        'name',
    ];

    /** @inheritdoc */
    protected $casts = [
        'is_from_ypareo' => 'boolean',
    ];

    /** @inheritdoc */
    protected $appends = [
        'bgColor',
    ];

    /**
     * Retrieve users having this role
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users() : BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Return a light background color for this role
     *
     * @return string
     */
    public function getBgColorAttribute() : string
    {
        $colors = [
            'pink' => '#fabed4',
            'beige' => '#fffac8',
            'mint' => '#aaffc3',
            'cyan' => '#42d4f4',
            'lavender' => '#dcbeff',
            'apricot' => '#ffd8b1',
            'white' => '#ffffff',
        ];

        return $colors[array_keys($colors)[($this->id - 1) % count($colors)]];
    }
}
