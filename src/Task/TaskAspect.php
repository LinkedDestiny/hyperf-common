<?php
declare(strict_types=1);


namespace Lib\Task;


use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Server\ServerFactory;
use Hyperf\Utils\ApplicationContext;
use Lib\Task\Annotation\AsyncTask;
use Lib\Task\Annotation\Task;

/**
 * @Aspect
 */
class TaskAspect extends AbstractAspect
{

    public $annotations = [
        Task::class,
        AsyncTask::class,
    ];

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed return the value from process method of ProceedingJoinPoint, or the value that you handled
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $meta = $proceedingJoinPoint->getAnnotationMetadata();
        /**
         * @var $serverFactory ServerFactory
         */
        $serverFactory = ApplicationContext::getContainer()->get(ServerFactory::class);
        $server = $serverFactory->getServer()->getServer();

        if (isset($meta["class"][Task::class])) {
            return $server->task(null, -1, function () use ($proceedingJoinPoint) {
                $result = $proceedingJoinPoint->process();
                return $result;
            });
        } else if (isset($meta["class"][AsyncTask::class])) {
            return $server->task(null, -1, function () use ($proceedingJoinPoint) {
                $result = $proceedingJoinPoint->process();
                return $result;
            });
        } else {
            $result = $proceedingJoinPoint->process();
            return $result;
        }
    }
}