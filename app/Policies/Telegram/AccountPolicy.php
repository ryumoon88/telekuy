<?php

namespace App\Policies\Telegram;

use App\Models\User;
use App\Models\Telegram\Account;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_telegram::account');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Account $account): bool
    {
        return $user->can('view_telegram::account');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_telegram::account');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Account $account): bool
    {
        return $user->can('update_telegram::account');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Account $account): bool
    {
        return $user->can('delete_telegram::account');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_telegram::account');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Account $account): bool
    {
        return $user->can('force_delete_telegram::account');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_telegram::account');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Account $account): bool
    {
        return $user->can('restore_telegram::account');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_telegram::account');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Account $account): bool
    {
        return $user->can('replicate_telegram::account');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_telegram::account');
    }

    /**
     * Determine whether the user can import account.
     */
    public function import(User $user): bool
    {
        return $user->can('import_telegram::account');
    }

    /**
     * Determine whether the user can download account.
     */
    public function download(User $user): bool
    {
        return $user->can('download_telegram::account');
    }

    /**
     * Determine whether the user can retrieve downloaded account.
     */
    public function retrieve(User $user): bool
    {
        return $user->can('retrieve_telegram::account');
    }

    /**
     * Determine whether the user can bundle account.
     */
    public function bundle(User $user): bool
    {
        return $user->can('bundle_telegram::account');
    }

    /**
     * Determine whether the user can add referral transaction.
     */
    public function addReferral(User $user): bool
    {
        return $user->can('add_referral_telegram::account');
    }

    /**
     * Determine whether the user can add bulk referral transaction.
     */
    public function addReferralAny(User $user): bool
    {
        return $user->can('add_referral_any_telegram::account');
    }
}
