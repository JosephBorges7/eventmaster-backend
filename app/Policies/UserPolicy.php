<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): Response
    {
        // Prevent deleting the root user
        if ($model->isRoot()) {
            return Response::deny(__('You cannot delete the root user.'));
        }

        // Prevent deleting yourself when used through user management
        if ($user->id === $model->id) {
            return Response::deny(__('You cannot delete yourself from the user management panel.'));
        }

        // Ensure at least one admin remains
        if ($model->isAdmin()) {
            $adminCount = User::whereHas('role', fn ($q) => $q->where('name', 'admin'))->count();

            if ($adminCount <= 1) {
                return Response::deny(__('You cannot delete the last admin.'));
            }
        }

        return $user->isAdmin()
            ? Response::allow()
            : Response::deny(__('Only admins can delete users.'));
    }

    public function deleteSelf(User $user): Response
    {
        // Block root user from deleting themselves
        if ($user->isRoot()) {
            return Response::deny(__('You cannot delete the root user.'));
        }

        return Response::allow();
    }
}
