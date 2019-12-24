<?php
declare(strict_types=1);

namespace CC\Hyperf\Common\Constants;

use Hyperf\Utils\Str;
use Hyperf\Constants\ConstantsCollector;
use Hyperf\Constants\Annotation\Constants;
use Hyperf\Constants\Exception\ConstantsException;

/**
 * @Constants
 * @method string getMessage(int $code)
 */
class Error
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
     * @param $name
     * @param $arguments
     * @return string
     * @throws ConstantsException
     */
    public function __call($name, $arguments)
    {
        if (! Str::startsWith($name, 'get')) {
            throw new ConstantsException('The function is not defined!');
        }

        if (! isset($arguments) || count($arguments) === 0) {
            throw new ConstantsException('The Code is required');
        }

        $code = $arguments[0];
        $name = strtolower(substr($name, 3));
        $class = get_called_class();

        $message = ConstantsCollector::getValue($class, $code, $name);

        array_shift($arguments);

        if (count($arguments) > 0) {
            return sprintf($message, ...$arguments);
        }
        return $message;
    }
}
