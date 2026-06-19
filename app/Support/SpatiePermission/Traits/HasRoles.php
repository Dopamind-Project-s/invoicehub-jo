<?php

declare(strict_types=1);

namespace Spatie\Permission\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->morphToMany(Role::class, 'model', 'model_has_roles')->withPivot('company_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->morphToMany(Permission::class, 'model', 'model_has_permissions')->withPivot('company_id');
    }

    public function assignRole(string|Role $role, ?int $teamId = null): void
    {
        $roleModel = is_string($role) ? Role::where('name', $role)->where('company_id', $teamId)->firstOrFail() : $role;
        $this->roles()->syncWithoutDetaching([$roleModel->id => ['company_id' => $teamId ?? $roleModel->company_id]]);
    }

    public function syncRoles(array $roles, ?int $teamId = null): void
    {
        $ids = Role::whereIn('id', $roles)->where('company_id', $teamId)->pluck('id')->all();
        $this->roles()->wherePivot('company_id', $teamId)->detach();
        $this->roles()->syncWithoutDetaching(collect($ids)->mapWithKeys(fn ($id) => [$id => ['company_id' => $teamId]])->all());
    }

    public function hasRole(string $role, ?int $teamId = null): bool
    {
        return $this->roles()->where('name', $role)->wherePivot('company_id', $teamId)->exists();
    }

    public function givePermissionTo(string|Permission $permission, ?int $teamId = null): void
    {
        $permissionModel = is_string($permission) ? Permission::where('name', $permission)->firstOrFail() : $permission;
        $this->permissions()->syncWithoutDetaching([$permissionModel->id => ['company_id' => $teamId]]);
    }

    public function canInCompany(string $permission, ?int $teamId = null): bool
    {
        if (method_exists($this, 'isSuperAdmin') && $this->isSuperAdmin()) {
            return true;
        }

        if ($this->permissions()->where('name', $permission)->wherePivot('company_id', $teamId)->exists()) {
            return true;
        }

        return $this->roles()
            ->wherePivot('company_id', $teamId)
            ->whereHas('permissions', fn ($query) => $query->where('name', $permission)->where('role_has_permissions.company_id', $teamId))
            ->exists();
    }
}
