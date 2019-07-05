<?php

declare(strict_types=1);


namespace Lib\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 *
 * @method static string getMessage(int $code)
 */
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("Server Error！")
     */
    const SERVER_ERROR = 500;

    /**
     * @Message("错误的请求参数")
     */
    const INVALID_PARAMS = 1001;

    /**
     * @Message("您还未登录！请登录后重试")
     */
    const INVALID_TOKEN = 1002;

    /**
     * @Message("您还未登录！请登录后重试")
     */
    const TOKEN_EXPIRE = 1003;

    /**
     * @Message("创建失败")
     */
    const ERR_CREATE_ERROR = 1101;

    /**
     * @Message("更新失败")
     */
    const ERR_UPDATE_ERROR = 1102;

    /**
     * @Message("未知错误，请重试")
     */
    const ERR_NOT_FOUND = 1103;
}
