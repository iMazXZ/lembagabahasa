<?php

namespace App\Policies;

use App\Models\User;
use App\Models\BasicListeningAttempt;
use Illuminate\Auth\Access\HandlesAuthorization;

class BasicListeningAttemptPolicy
{
    use HandlesAuthorization;

    /**
     * Catatan:
     * - Admin akan di-override oleh Gate::before di AuthServiceProvider (return true).
     * - Tutor: hanya boleh view/viewAny untuk attempts dari prodi yang dia ampu.
     * - Tutor tidak boleh create/update/delete attempts (dibuat sistem saat mahasiswa mengerjakan).
     */

    /**
     * Boleh lihat daftar attempts?
     * Tutor: hanya jika dia mengampu minimal 1 prodi.
     * Peran lain: false (kecuali admin via Gate::before).
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('tutor')) {
            return \count($user->assignedProdyIds()) > 0;
        }

        // fallback untuk peran lain (non-admin): gunakan izin Shield bila ada
        return $user->can('view_any_basic::listening::attempt');
    }

    /**
     * Boleh lihat 1 attempt?
     * Tutor: hanya jika attempt milik mahasiswa dari prodi yang dia ampu.
     */
    public function view(User $user, BasicListeningAttempt $attempt): bool
    {
        if ($user->hasRole('tutor')) {
            $prodyId = $attempt->user?->prody_id;
            return $prodyId !== null && \in_array($prodyId, $user->assignedProdyIds(), true);
        }

        // fallback untuk peran lain (non-admin)
        return $user->can('view_basic::listening::attempt');
    }

    /**
     * Attempt dibuat oleh sistem (mahasiswa mengerjakan).
     * Tutor tidak perlu membuat attempt manual.
     */
    public function create(User $user): bool
    {
        // blokir tutor walau Shield-nya centang create
        if ($user->hasRole('tutor')) {
            return false;
        }

        return $user->can('create_basic::listening::attempt');
    }

    public function update(User $user, BasicListeningAttempt $attempt): bool
    {
        if ($user->hasRole('tutor')) {
            return false;
        }

        return $user->can('update_basic::listening::attempt');
    }

    public function delete(User $user, BasicListeningAttempt $attempt): bool
    {
        if ($user->hasRole('tutor')) {
            return false;
        }

        return $user->can('delete_basic::listening::attempt');
    }

    public function deleteAny(User $user): bool
    {
        if ($user->hasRole('tutor')) {
            return false;
        }

        return $user->can('delete_any_basic::listening::attempt');
    }

    public function forceDelete(User $user, BasicListeningAttempt $attempt): bool
    {
        if ($user->hasRole('tutor')) {
            return false;
        }

        return $user->can('force_delete_basic::listening::attempt');
    }

    public function forceDeleteAny(User $user): bool
    {
        if ($user->hasRole('tutor')) {
            return false;
        }

        return $user->can('force_delete_any_basic::listening::attempt');
    }

    public function restore(User $user, BasicListeningAttempt $attempt): bool
    {
        if ($user->hasRole('tutor')) {
            return false;
        }

        return $user->can('restore_basic::listening::attempt');
    }

    public function restoreAny(User $user): bool
    {
        if ($user->hasRole('tutor')) {
            return false;
        }

        return $user->can('restore_any_basic::listening::attempt');
    }

    public function replicate(User $user, BasicListeningAttempt $attempt): bool
    {
        if ($user->hasRole('tutor')) {
            return false;
        }

        return $user->can('replicate_basic::listening::attempt');
    }

    public function reorder(User $user): bool
    {
        if ($user->hasRole('tutor')) {
            return false;
        }

        return $user->can('reorder_basic::listening::attempt');
    }
}
