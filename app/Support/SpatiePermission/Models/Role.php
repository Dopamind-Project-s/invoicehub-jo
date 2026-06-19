<?php

declare(strict_types=1);

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['name', 'guard_name', 'company_id'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions')->withPivot('company_id');
    }

    public function syncPermissions(array $permissions): void
    {
        $ids = Permission::whereIn('name', $permissions)->pluck('id')->all();
        $this->permissions()->syncWithPivotValues($ids, ['company_id' => $this->company_id]);
    }
}
