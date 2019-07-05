<?php

declare(strict_types=1);


namespace Lib\Framework;

use Hyperf\DbConnection\Model\Model;

class BaseModel extends Model
{
    const CREATED_AT = 'create_at';

    const UPDATED_AT = 'update_at';

    protected $dateFormat = 'U';
}
