<?php

declare(strict_types=1);


namespace Lib\Framework;

use Hyperf\DbConnection\Model\Model as BaseModel;

class Model extends BaseModel
{
    const CREATED_AT = 'create_at';

    const UPDATED_AT = 'update_at';

    protected $dateFormat = 'U';
}
