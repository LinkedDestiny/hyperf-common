<?php

declare(strict_types=1);


namespace Lib\Framework;

use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Model\Model;

class BaseModel extends Model
{
    const CREATED_AT = 'create_at';

    const UPDATED_AT = 'update_at';

    protected $dateFormat = 'U';

    /**
     * @param $key
     * @param $value
     * @return Builder|static|object|null
     */
    public static function findOne($key, $value)
    {
        return self::query()
            ->where($key, $value)
            ->first();
    }

    public function insert(array $data)
    {
        $this->fill($data);

        return $this->save();
    }

    public function disable()
    {
        $this->fill([
            'enable' => 1
        ]);
        return $this->save();
    }
}
