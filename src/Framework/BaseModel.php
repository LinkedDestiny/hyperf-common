<?php

declare(strict_types=1);


namespace CC\Hyperf\Common\Framework;

use Lib\Constants\SoftDeleted;
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
     * @param array $field
     * @return Builder|static|object|null
     */
    public static function findOne($key, $value, $field = ['*'])
    {
        return self::query()
            ->where($key, $value)
            ->where('enable', SoftDeleted::ENABLE)
            ->first($field);
    }

    /**
     * @param array $condition
     * @param array $field
     * @return Builder|static|object|null
     */
    public static function findOneCondition(array $condition, $field = ['*'])
    {
        return self::query()
            ->where($condition)
            ->where('enable', SoftDeleted::ENABLE)
            ->first($field);
    }

    public function insertData(array $data)
    {
        $this->fill($data);
        return $this->save();
    }

    public function updateData(array $data)
    {
        $this->fill($data);
        return $this->save();
    }

    public function disable()
    {
        $this->fill([
            'enable' => SoftDeleted::DISABLE
        ]);
        return $this->save();
    }

    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
}
