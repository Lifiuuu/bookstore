<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Session;

class UserSessionHelper
{
    /**
     * Get user ID dari session
     */
    public static function getUserId()
    {
        return Session::get('user_id');
    }

    /**
     * Get user name dari session
     */
    public static function getUserName()
    {
        return Session::get('user_name');
    }

    /**
     * Get user email dari session
     */
    public static function getUserEmail()
    {
        return Session::get('user_email');
    }

    /**
     * Get user role dari session
     */
    public static function getUserRole()
    {
        return Session::get('user_role');
    }

    /**
     * Get login time dari session
     */
    public static function getLoginTime()
    {
        return Session::get('logged_in_at');
    }

    /**
     * Check apakah user terautentikasi dan sessionnya valid
     */
    public static function isSessionValid()
    {
        return Session::has('user_id') && Session::has('user_role');
    }

    /**
     * Get semua user data dari session
     */
    public static function getUserData()
    {
        if (!self::isSessionValid()) {
            return null;
        }

        return [
            'id' => self::getUserId(),
            'name' => self::getUserName(),
            'email' => self::getUserEmail(),
            'role' => self::getUserRole(),
            'logged_in_at' => self::getLoginTime(),
        ];
    }
}
