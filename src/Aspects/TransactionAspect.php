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
        try {
            Db::beginTransaction();
            $result = $proceedingJoinPoint->process();
            Db::commit();
            return $result;
        } catch (\Throwable $e) {
            Db::rollBack();
            throw $e;
        }
    }
}