<?php
namespace App\Support;

class ActivityApproval
{
    public static function branches($user = null): array
    {
        $user ??= auth()->user();
        return $user && $user->branch_id && ($user->can('activity.approve_own_branch') || $user->hasRole('branch_manager'))
            ? [(int) $user->branch_id] : [];
    }

    public static function allowed($activity, $user = null): bool
    {
        $user ??= auth()->user();
        if (!$user) return false;
        if ($user->hasRole('branch_manager')) {
            return $user->branch_id && (int) $activity->branch_id === (int) $user->branch_id
                && $user->canAny(['activity.approve_own_branch','activity.approve']);
        }
        return $user->can('activity.approve') || in_array((int) $activity->branch_id, self::branches($user), true);
    }
}
