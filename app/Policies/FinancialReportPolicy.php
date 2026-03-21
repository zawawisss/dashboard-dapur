<?php

namespace App\Policies;

use App\Models\FinancialReport;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FinancialReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Admin & Investor bisa melihat halaman
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FinancialReport $financialReport): bool
    {
        if ($user->role === 'ADMIN') {
            return true;
        }
        return $user->id === $financialReport->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'ADMIN'; // Hanya admin yang bisa membuat laporan
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FinancialReport $financialReport): bool
    {
        return $user->role === 'ADMIN'; // Hanya admin
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FinancialReport $financialReport): bool
    {
        return $user->role === 'ADMIN'; // Hanya admin
    }
}
