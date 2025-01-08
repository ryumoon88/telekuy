<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["view_role","view_any_role","create_role","update_role","delete_role","delete_any_role","view_telegram::account","view_any_telegram::account","create_telegram::account","update_telegram::account","restore_telegram::account","restore_any_telegram::account","replicate_telegram::account","reorder_telegram::account","delete_telegram::account","delete_any_telegram::account","force_delete_telegram::account","force_delete_any_telegram::account","view_telegram::bundle","view_any_telegram::bundle","create_telegram::bundle","update_telegram::bundle","restore_telegram::bundle","restore_any_telegram::bundle","replicate_telegram::bundle","reorder_telegram::bundle","delete_telegram::bundle","delete_any_telegram::bundle","force_delete_telegram::bundle","force_delete_any_telegram::bundle","view_telegram::referral","view_any_telegram::referral","create_telegram::referral","update_telegram::referral","restore_telegram::referral","restore_any_telegram::referral","replicate_telegram::referral","reorder_telegram::referral","delete_telegram::referral","delete_any_telegram::referral","force_delete_telegram::referral","force_delete_any_telegram::referral","view_user","view_any_user","create_user","update_user","restore_user","restore_any_user","replicate_user","reorder_user","delete_user","delete_any_user","force_delete_user","force_delete_any_user","import_telegram::account","download_telegram::account","retrieve_telegram::account","bundle_telegram::account","add_referral_telegram::account","add_referral_any_telegram::account","view_account::transaction","view_any_account::transaction","create_account::transaction","update_account::transaction","restore_account::transaction","restore_any_account::transaction","replicate_account::transaction","reorder_account::transaction","delete_account::transaction","delete_any_account::transaction","force_delete_account::transaction","force_delete_any_account::transaction","view_referral","view_any_referral","create_referral","update_referral","restore_referral","restore_any_referral","replicate_referral","reorder_referral","delete_referral","delete_any_referral","force_delete_referral","force_delete_any_referral","page_Referrals"]},{"name":"admin","guard_name":"web","permissions":["view_telegram::account","view_any_telegram::account","download_telegram::account","retrieve_telegram::account","add_referral_telegram::account","add_referral_any_telegram::account"]}]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
