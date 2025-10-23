<?php

namespace App\Policies;

use App\Models\User;
use App\Models\BasicListeningConnectCode;
use Illuminate\Auth\Access\HandlesAuthorization;

class BasicListeningConnectCodePolicy
{
    use HandlesAuthorization;

    /**
     * Catatan:
     * - Admin di-override oleh Gate::before (AuthServiceProvider) -> selalu true.
     * - Tutor: boleh viewAny. Untuk view/update/delete pada record tertentu,
     *   dibatasi ke (a) prodi yang dia ampu, atau (b) code yang dia buat (created_by).
     * - Peran lain: fallback ke izin Shield seperti sebelumnya.
     */

    public function viewAny(User $user): bool
    {
        if ($user->hasRole('tutor')) {
            return true; // list akan tetap di-scope di Resource (To-Do 4)
        }

        return $user->can('view_any_basic::listening::connect::code');
    }

    public function view(User $user, BasicListeningConnectCode $code): bool
    {
        if ($user->hasRole('tutor')) {
            return (
                ($code->prody_id !== null && \in_array($code->prody_id, $user->assignedProdyIds(), true))
                || ($code->created_by !== null && $code->created_by === $user->id)
            );
        }

        return $user->can('view_basic::listening::connect::code');
    }

    public function create(User $user): bool
    {
        // Tutor boleh membuat connect code (pilihan prodi akan kita batasi di Resource)
        if ($user->hasRole('tutor')) {
            return true;
        }

        return $user->can('create_basic::listening::connect::code');
    }

    public function update(User $user, BasicListeningConnectCode $code): bool
    {
        if ($user->hasRole('tutor')) {
            return (
                ($code->prody_id !== null && \in_array($code->prody_id, $user->assignedProdyIds(), true))
                || ($code->created_by !== null && $code->created_by === $user->id)
            );
        }

        return $user->can('update_basic::listening::connect::code');
    }

    public function delete(User $user, BasicListeningConnectCode $code): bool
    {
        if ($user->hasRole('tutor')) {
            return (
                ($code->prody_id !== null && \in_array($code->prody_id, $user->assignedProdyIds(), true))
                || ($code->created_by !== null && $code->created_by === $user->id)
            );
        }

        return $user->can('delete_basic::listening::connect::code');
    }

    public function deleteAny(User $user): bool
    {
        // Tutor boleh mass delete hanya jika dia memang punya izin Shield itu,
        // tetapi tiap record masih disaring oleh kebijakan update()/delete() di atas.
        if ($user->hasRole('tutor')) {
            return true;
        }

        return $user->can('delete_any_basic::listening::connect::code');
    }

    public function forceDelete(User $user, BasicListeningConnectCode $code): bool
    {
        if ($user->hasRole('tutor')) {
            return false; // tidak perlu force delete untuk tutor
        }

        return $user->can('force_delete_basic::listening::connect::code');
    }

    public function forceDeleteAny(User $user): bool
    {
        if ($user->hasRole('tutor')) {
            return false;
        }

        return $user->can('force_delete_any_basic::listening::connect::code');
    }

    public function restore(User $user, BasicListeningConnectCode $code): bool
    {
        if ($user->hasRole('tutor')) {
            // umumnya tidak dipakai; sesuaikan jika kamu pakai soft deletes
            return (
                ($code->prody_id !== null && \in_array($code->prody_id, $user->assignedProdyIds(), true))
                || ($code->created_by !== null && $code->created_by === $user->id)
            );
        }

        return $user->can('restore_basic::listening::connect::code');
    }

    public function restoreAny(User $user): bool
    {
        if ($user->hasRole('tutor')) {
            return false;
        }

        return $user->can('restore_any_basic::listening::connect::code');
    }

    public function replicate(User $user, BasicListeningConnectCode $code): bool
    {
        if ($user->hasRole('tutor')) {
            // izinkan replikasi bila dia berhak view record sumber
            return $this->view($user, $code);
        }

        return $user->can('replicate_basic::listening::connect::code');
    }

    public function reorder(User $user): bool
    {
        // umumnya tidak relevan; fallback ke Shield
        return $user->can('reorder_basic::listening::connect::code');
    }
}
