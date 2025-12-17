<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Session;

class RoleHelper
{
    /**
     * Get all roles from session
     *
     * @return array
     */
    public static function getRoles()
    {
        return Session::get('user_roles', []);
    }

    /**
     * Check if user has a specific role
     *
     * @param string $role
     * @return bool
     */
    public static function hasRole($role)
    {
        $roles = self::getRoles();
        return in_array($role, $roles);
    }

    /**
     * Check if user has any of the given roles
     *
     * @param array $roles
     * @return bool
     */
    public static function hasAnyRole($roles)
    {
        $userRoles = self::getRoles();
        return count(array_intersect($userRoles, $roles)) > 0;
    }

    /**
     * Check if user has all of the given roles
     *
     * @param array $roles
     * @return bool
     */
    public static function hasAllRoles($roles)
    {
        $userRoles = self::getRoles();
        return count(array_diff($roles, $userRoles)) === 0;
    }

    /**
     * Get user ID from session
     *
     * @return mixed
     */
    public static function getUserId()
    {
        return Session::get('user_id');
    }
}
