<?php

declare(strict_types=1);


namespace Lib\Constants;


use CC\Hyperf\Common\Framework\BaseEnum;
use Hyperf\Constants\Annotation\Constants;

/**
 * 软删除状态
 * @Constants()
 */
class SoftDeleted extends BaseEnum
{
    /**
     * @Message("正常")
     */
    const ENABLE = 1;

    /**
     * @Message("删除")
     */
    const DISABLE = 0;
}