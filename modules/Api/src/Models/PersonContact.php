<?php


namespace Modules\Api\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonContact extends Model
{
    use SoftDeletes;

    protected $table = 'person_contacts';

    protected $fillable = [
        'person_id','type','value','is_primary','is_verified',
        'verified_at','verification_code','verification_code_expires_at','meta',
        'created_by','updated_by','deleted_by',
    ];

    protected $casts = [
        'is_primary' => 'bool',
        'is_verified' => 'bool',
        'verified_at' => 'datetime',
        'verification_code_expires_at' => 'datetime',
        'meta' => 'array',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
