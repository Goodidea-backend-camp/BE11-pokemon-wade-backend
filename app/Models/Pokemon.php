<?php

namespace App\Models;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pokemon extends Model
{
    use SoftDeletes;
    use HasFactory;
    const MIN_LEVEL = 1;
    const MAX_LEVEL = 100;
    const MAX_SKILLS_COUNT = 4;
    const DEFAULT_NAME = '';
    protected $table = 'pokemons';
    protected $casts = [
        'skills' => 'array',
    ];

    protected $fillable = [
        'name',
        'race_id',
        'ability_id',
        'nature_id',
        'level',
        'skills',
        'user_id'

        // ... 其他允許的屬性 ...
    ];

    public function race()
    {
        return $this->belongsTo(Race::class);
    }

    public function nature()
    {
        return $this->belongsTo(Nature::class);
    }

    public function ability()
    {
        return $this->belongsTo(Ability::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'pokemon_skill');
    }

    /**
     * Retrieve the model for a bound value.
     *
     */
    // public function resolveRouteBinding($value, $field = null)
    // {
    //     $result = $this->where($field ?? $this->getRouteKeyName(), $value)->first();

    //     if (! $result) {
    //         // 拋出您自己的異常或者使用 Laravel 預設的 ModelNotFoundException
    //         throw new ModelNotFoundException("Pokemon with ID {$value} not found.");
    //     }

    //     return $result;
    // }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

   
}
