<?php

declare(strict_types=1);


namespace CC\Hyperf\Common\Aspects\Annotations;


use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"METHOD","CLASS"})
 */
class Transactional extends AbstractAnnotation
{

}