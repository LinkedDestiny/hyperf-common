<?php

declare(strict_types=1);


namespace CC\Hyperf\Common\Component\Log;

use Hyperf\Logger\LoggerFactory as HyperfLoggerFactory;
use Psr\Container\ContainerInterface;

class LoggerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return $container->get(HyperfLoggerFactory::class)->make();
    }
}
