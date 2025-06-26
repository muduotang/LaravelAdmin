<?php

namespace App\Exceptions;

use Exception;

class AdminException extends Exception
{
    /**
     * 不能删除自己
     */
    public static function cannotDeleteSelf(): self
    {
        return new self('不能删除自己', 400);
    }

    /**
     * 不能禁用自己
     */
    public static function cannotDisableSelf(): self
    {
        return new self('不能禁用自己', 400);
    }

    /**
     * 管理员不存在
     */
    public static function notFound(): self
    {
        return new self('管理员不存在', 404);
    }

    /**
     * 用户名已存在
     */
    public static function usernameExists(): self
    {
        return new self('用户名已存在', 422);
    }

    /**
     * 邮箱已存在
     */
    public static function emailExists(): self
    {
        return new self('邮箱已存在', 422);
    }
}