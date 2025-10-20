<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Led Strip model
 *
 * @property-read int    $length
 * @property-read string $power_class
 * @property-read float  $limiting_factor
 */
class LedStrip extends Model
{
    use HasFactory;

    /** @inheritdoc */
    protected $casts = [
        'length' => 'integer',
        'power_supply' => 'integer',
        'limiting_factor' => 'decimal:2',
    ];

    /** @inheritdoc */
    protected $fillable = [
        'name',
        'topic',
        'length',
        'power_supply',
    ];

    /** @inheritdoc */
    protected $appends = [
        'power_necessary',
    ];

    /**
     * Returns the CSS class for the power currently set,
     * related to the necessary power for this strip length
     *
     * @return string|null
     */
    public function getPowerClassAttribute()
    {
        if ($this->power_necessary > $this->power_supply) {
            return 'warning';
        }

        return null;
    }

    /**
     * Calculate minimum power necessary for this strip length
     *
     * @return int
     */
    public function getPowerNecessaryAttribute()
    {
        return $this->length * 0.02 * 3;
    }

    /**
     * Calculate the limiting factor for the power supply
     *
     * @return float
     */
    public function getLimitingFactorAttribute()
    {
        return round(min(1, $this->power_supply / $this->power_necessary), 2, PHP_ROUND_HALF_DOWN);
    }

    /**
     * Calculate the given power limited for the power supply
     *
     * @param  string $color
     *
     * @return string
     */
    public function getLimitedColor(string $color)
    {
        [$r, $g, $b] = sscanf(strtolower($color), '#%02x%02x%02x');

        $r = floor(min(255 * $this->limiting_factor, $r));
        $g = floor(min(255 * $this->limiting_factor, $g));
        $b = floor(min(255 * $this->limiting_factor, $b));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
