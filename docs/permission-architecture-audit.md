# Permission Architecture Audit

## Removed custom Spatie emulation

- `database/migrations/2026_06_19_000006_create_permission_tables.php` previously hand-built the Spatie tables and seeded permissions in the same migration. It has been replaced with the official Spatie-style table structure driven by `config('permission.*')`; it no longer seeds business permission records.
- `database/migrations/2026_06_19_000009_adopt_official_spatie_permission_schema.php` attempted to manually reshape custom tables after the fact. It has been removed to avoid maintaining duplicate schema logic.
- `database/migrations/2026_06_19_000008_seed_default_company_roles.php` previously deleted company roles in `down()` and directly duplicated default-role logic. It now seeds permissions and default role-permission mappings idempotently without destructive rollback behavior.

## Official Spatie components in use

- Models: `Spatie\Permission\Models\Role` and `Spatie\Permission\Models\Permission` are configured in `config/permission.php`.
- User trait: `App\Models\User` uses `Spatie\Permission\Traits\HasRoles`.
- Middleware: the `permission` alias points to `Spatie\Permission\Middleware\PermissionMiddleware`; `permission.team` only sets the active Spatie team context.
- Registrar: migrations and seeders call `Spatie\Permission\PermissionRegistrar::forgetCachedPermissions()` after authorization data changes.
- Teams: `config/permission.php` enables teams and sets `column_names.team_foreign_key` to `company_id`.

## Data migration strategy

- Fresh installs create the official Spatie-compatible schema directly from `2026_06_19_000006_create_permission_tables.php`.
- Existing installs keep role, permission, and assignment data because the legacy table names match Spatie table names.
- The stabilization migration keeps credential columns wide enough for encrypted values and removes invalid teamless direct model assignments from team-aware pivot tables. Role-to-permission assignments are preserved because they are global in Spatie's team architecture.
- Seeders and authorization migrations use idempotent `updateOrInsert`/`updateOrCreate` operations and role-permission sync semantics to avoid duplicates on reruns.

## Non-permission stabilization

- All Blade `@vite` references were removed so the application does not require `public/build/manifest.json`.
- Company JoFotara credential columns are now TEXT/LONGTEXT capable for encrypted values.
- Seeder credential writes are conditional so blank environment variables do not erase existing encrypted credentials.
