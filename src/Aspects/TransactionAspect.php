<?php

declare(strict_types=1);


namespace CC\Hyperf\Common\Aspects;

use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use CC\Hyperf\Common\Aspects\Annotations\Transactional;

/**
 * 自动事务注解
 * @Aspect
 */
class TransactionAspect extends AbstractAspect
{

    public $annotations = [
        Transactional::class,
    ];

    /**
     * @inheritDoc
     * @throws \Throwable
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return Db::transaction(
            function () use ($proceedingJoinPoint) {
                return $proceedingJoinPoint->process();
            }
        );
    }
}