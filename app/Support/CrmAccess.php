<?php

namespace App\Support;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CrmAccess
{
    /**
     * Backward-compatible helper. Treat a user as self-scoped when their
     * permissions only allow self-level CRM records. This avoids coupling
     * access rules to a hard-coded role name.
     */
    public static function isStaff(?User $user = null): bool
    {
        $user ??= auth()->user();
        if (!$user) return false;
        if ($user->hasRole('superadmin')) return false;

        $hasWide = $user->can('activity.view_all') || $user->can('activity.view_branch')
            || $user->can('lead.view_all_branches') || $user->can('lead.view_branch')
            || $user->can('quotation.view_all_branches') || $user->can('quotation.view_branch')
            || $user->can('sale.view_all_branches') || $user->can('sale.view_branch');

        $hasSelf = $user->can('activity.view_self') || $user->can('lead.view_self')
            || $user->can('quotation.view_self') || $user->can('sale.view_self');

        return $hasSelf && !$hasWide;
    }

    public static function hasAreaRestriction(?User $user = null): bool
    {
        $user ??= auth()->user();
        if (!$user || $user->hasRole('superadmin')) return false;
        return DB::table('user_area_assignments')->where('user_id', $user->id)->exists();
    }

    public static function applyOrganizationScope(Builder $query, ?User $user = null): Builder
    {
        $user ??= auth()->user();
        if (!$user || !self::hasAreaRestriction($user)) return $query;

        return $query->whereExists(function ($sub) use ($user) {
            $sub->select(DB::raw(1))
                ->from('user_area_assignments as uaa')
                ->where('uaa.user_id', $user->id)
                ->whereColumn('uaa.district_id', 'organizations.district_id')
                ->where(function ($q) {
                    $q->whereNull('uaa.upazila_id')
                      ->orWhereColumn('uaa.upazila_id', 'organizations.upazila_id');
                });
        });
    }

    public static function organizationAllowed(?int $districtId, ?int $upazilaId, ?User $user = null): bool
    {
        $user ??= auth()->user();
        if (!$user || !self::hasAreaRestriction($user)) return true;
        if (!$districtId) return false;

        return DB::table('user_area_assignments')
            ->where('user_id', $user->id)
            ->where('district_id', $districtId)
            ->where(function ($q) use ($upazilaId) {
                $q->whereNull('upazila_id');
                if ($upazilaId) $q->orWhere('upazila_id', $upazilaId);
            })->exists();
    }

    public static function ensureOrganizationAllowed(Organization $organization, ?User $user = null): void
    {
        abort_unless(self::organizationAllowed($organization->district_id, $organization->upazila_id, $user), 403, 'This organization is outside your assigned area.');
    }

    public static function ensureAreaAllowed(?int $districtId, ?int $upazilaId, ?User $user = null): void
    {
        abort_unless(self::organizationAllowed($districtId, $upazilaId, $user), 403, 'You can only work inside your assigned district/upazila.');
    }

    public static function applyOwnerScope(Builder $query, string $column, ?User $user = null): Builder
    {
        $user ??= auth()->user();
        if ($user && self::isStaff($user)) $query->where($column, $user->id);
        return $query;
    }
}
