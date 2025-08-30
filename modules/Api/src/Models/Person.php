<?php

namespace Modules\Api\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Api\Models\Concerns\Blameable;

class Person extends Model
{
    use HasFactory, SoftDeletes, Blameable;

    protected $fillable = ['first_name', 'last_name'];

    public function contacts()
    {
        return $this->hasMany(PersonContact::class, 'person_id');
    }
}
