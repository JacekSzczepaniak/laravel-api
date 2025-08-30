<?php


namespace Modules\Api\Models\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

trait Blameable
{
    public static function Blameable(): void
    {
        static::creating(function ($model) {
            if ($uid = Auth::id()) {
                $model->created_by ??= $uid;
                $model->updated_by ??= $uid;
            }
        });

        static::updating(function ($model) {
            if ($uid = Auth::id()) {
                $model->updated_by = $uid;
            }
        });

        static::deleting(function ($model) {
            $usesSoftDeletes = in_array(SoftDeletes::class, class_uses_recursive($model));
            $isSoftDelete = $usesSoftDeletes && method_exists($model, 'isForceDeleting') && !$model->isForceDeleting();

            if ($isSoftDelete && $uid = Auth::id()) {
                $model->deleted_by = $uid;
                $model->saveQuietly();
            }
        });
    }
}
