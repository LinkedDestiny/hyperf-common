<?php
/**
 * @author      lidanyang <danyangli@bitun.io>
 * @copyright   Copyright 2017-2018 BitUN Lab
 */

namespace Lib\Component\MongoDB;

use Lib\Component\Log\Log;
use Lib\Task\BaseTask;
use MongoClient;
use MongoCollection;
use MongoConnectionException;
use MongoDB;

/**
 * Class MongoDbClient
 */
class MongoDbClient extends BaseTask
{
    /**
     * @var array 当前要使用的MongoDB配置, [配置名，db名，collection名]
     */
    protected $mongoConf = [];

    /**
     * @var array 全局MongoDB配置
     */
    protected $config;

    /**
     * @var MongoClient MongoDB Client实例
     */
    protected $mongoClient;

    /**
     * @var MongoDB MongoDB实例
     */
    protected $mongoDb;

    /**
     * @var MongoCollection MongoCollection实例
     */
    protected $mongoCollection;

    /**
     * @var string 性能分析name
     */
    private $profileName = '';

    /**
     * 构造方法
     *
     * @throws \Exception
     */
    public function __construct()
    {
        if (empty($this->mongoConf)) {
            throw new MongoConnectionException('No $mongoConf in this class or no server config in $mongoConf');
        }

        if (!is_object($this->mongoClient)) {
            $this->prepare($this->mongoConf[0], $this->mongoConf[1] ?? '', $this->mongoConf[2] ?? '');
        }
    }

    /**
     * 独立选择DB和collection
     *
     * @param string $dbName MongoDB Name
     * @param string $collectionName MongoDB Collection Name
     * @return MongoCollection
     * @throws \Exception
     */
    public function setDbCollection(string $dbName, string $collectionName): MongoCollection
    {
        return $this->mongoClient->selectDB($dbName)->selectCollection($collectionName);
    }

    /**
     * 初始化链接 每个task进程内只初始化一次
     *
     * @param string $confKey 配置名称
     * @param string $db MongoDB Name
     * @param string $collection MongoDB Collection Name
     * @throws \Exception
     */
    public function prepare(string $confKey, string $db, string $collection)
    {
        $this->profileName = 'mongo.' . $db . '.';
        $this->config = config('mongodb', []);
        if (!isset($this->config[$confKey])) {
            throw new MongoConnectionException('No such a MongoDB config ' . $confKey);
        }
        $conf = $this->config[$confKey];
        $this->mongoClient = new MongoClient($conf['server'], $conf['options'], $conf['driverOptions']);
        $db && ($this->mongoDb = $this->mongoClient->selectDB($db));
        $collection && ($this->mongoCollection = $this->mongoDb->selectCollection($collection));
    }

    /**
     * 查询文档，返回二维数组的数据
     *
     * @param array $query 查询条件，如：['_id' => new \MongoId('0f6821586b9887e3174e7c78')]
     * @param array $fields 返回的字段列表，默认全部，如：['name' => true, 'age' => true]
     * @param array $sort 排序，如：['create_time' => 1, '_id' => -1]
     * @param int $limit 限制返回的数据文档数
     * @param int $skip 开始返回的offset
     * @param int $timeout 查询超时时间，default 2s, 0 wait forever.
     * @return array:
     * @throws \MongoException
     */
    public function query(
        $query = [],
        $fields = [],
        $sort = null,
        $limit = null,
        $skip = null,
        $timeout = 2000
    ) {
        $cursor = $this->mongoCollection->find($query, $fields);
        if (!is_null($sort)) {
            $cursor->sort($sort);
        }
        if (!is_null($limit)) {
            $cursor->limit($limit);
        }
        if (!is_null($skip)) {
            $cursor->skip($skip);
        }
        $cursor->maxTimeMS($timeout);
        $out = iterator_to_array($cursor);

        return $out;
    }

    /**
     * 查询返回一条数据
     *
     * @param array $query 查询条件，如：['_id' => new \MongoId('0f6821586b9887e3174e7c78')]
     * @param array $fields 返回的字段列表，默认全部，如：['name' => true, 'age' => true]
     * @param int $timeout 查询超时时间，default 2s, 0 wait forever.
     * @return array|null
     * @throws \MongoException
     */
    public function findOne(
        $query = [],
        $fields = [],
        $timeout = 2000
    ) {
        $options = [
            'socketTimeoutMS' => $timeout
        ];
        $out = $this->mongoCollection->findOne($query, $fields, $options);
        if ($out === null) {
            return [];
        }

        return $out;
    }

    /**
     * 返回符合条件的文档数
     *
     * @param array $query 查询条件，如：['_id' => new \MongoId('0f6821586b9887e3174e7c78')]
     * @param int|null $limit 限制返回的数据文档数
     * @param int|null $skip 开始查询的offset
     * @param int $timeout 查询超时时间，default 2s, 0 wait forever.
     * @return int
     * @throws \MongoException
     */
    public function count(
        $query = [],
        $limit = null,
        $skip = null,
        $timeout = 2000
    ) {
        $options = [
            'socketTimeoutMS' => $timeout
        ];
        if (!is_null($limit)) {
            $options['limit'] = $limit;
        }
        if (!is_null($skip)) {
            $options['skip'] = $skip;
        }
        $out = $this->mongoCollection->count($query, $options);
        if ($out === null) {
            return 0;
        }

        return $out;
    }

    /**
     * 新建文档
     *
     * @param array $doc 待创建的文档数据
     * @param int $timeout 查询超时时间，default 2s, 0 wait forever.
     * @param int $w http://php.net/manual/zh/mongo.writeconcerns.php
     * @param boolean $fsync 是否立即写入磁盘
     * @return boolean
     * @throws \MongoException
     */
    public function add($doc, $timeout = 2000, $w = 1, $fsync = false)
    {
        $options = [
            'w' => $w,
            'fsync' => $fsync,
            'socketTimeoutMS' => $timeout,
        ];
        $ret = $this->mongoCollection->insert($doc, $options);
        if ($w > 0) {
            if ($ret['ok'] && is_null($ret['err'])) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * 批量新建文档
     *
     * @param array $docs 新建文档的二维数组
     * @param boolean $continueOnError 出现错误是否继续插入
     * @param int $timeout 查询超时时间，default 2s, 0 wait forever.
     * @param int $w http://php.net/manual/zh/mongo.writeconcerns.php
     * @param boolean $fsync 是否立即写入磁盘
     * @return boolean
     * @throws \MongoException
     */
    public function batchAdd(
        $docs,
        $continueOnError = true,
        $timeout = 2000,
        $w = 1,
        $fsync = false
    ) {
        $options = [
            'w' => $w,
            'fsync' => $fsync,
            'continueOnError' => $continueOnError,
            'socketTimeoutMS' => $timeout,
        ];
        $ret = $this->mongoCollection->batchInsert($docs, $options);
        if ($w > 0) {
            if ($ret['ok'] && is_null($ret['err'])) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * 修改文档（包含set关键字）
     *
     * @param array $criteria 更新条件
     * @param array $doc 要更新的字段和值
     * @param boolean $multiple 是否更新所有符合条件的文档
     * @param boolean $upsert 没有符合条件的文档时，是否插入新文档
     * @param int $timeout 超时时间，单位ms
     * @param int $w 成功写入到多少个复制时返回
     * @param boolean $fsync 是否等待MongoDB将数据更新到磁盘
     * @return boolean
     * @throws \MongoException
     */
    public function modify(
        $criteria,
        $doc,
        $multiple = true,
        $upsert = false,
        $timeout = 2000,
        $w = 1,
        $fsync = false
    ) {

        $options = [
            'w' => $w,
            'fsync' => $fsync,
            'upsert' => $upsert,
            'multiple' => $multiple,
            'socketTimeoutMS' => $timeout,
        ];
        $ret = $this->mongoCollection->update($criteria, ['$set' => $doc], $options);

        if ($w > 0) {
            if ($ret['ok'] && is_null($ret['err'])) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * 修改文档
     *
     * @param array $criteria 更新条件
     * @param array $doc 要更新的字段和值
     * @param boolean $multiple 是否更新所有符合条件的文档
     * @param boolean $upsert 没有符合条件的文档时，是否插入新文档
     * @param int $timeout 超时时间，单位ms
     * @param int $w 成功写入到多少个复制时返回
     * @param boolean $fsync 是否等待MongoDB将数据更新到磁盘
     * @return boolean
     * @throws \MongoException
     */
    public function updateDoc(
        $criteria,
        $doc,
        $multiple = true,
        $upsert = false,
        $timeout = 2000,
        $w = 1,
        $fsync = false
    ) {
        $options = [
            'w' => $w,
            'fsync' => $fsync,
            'upsert' => $upsert,
            'multiple' => $multiple,
            'socketTimeoutMS' => $timeout,
        ];
        $ret = $this->mongoCollection->update($criteria, $doc, $options);
        if ($w > 0) {
            if ($ret['ok'] && is_null($ret['err'])) {
                return $ret['n'];
            } else {
                Log::error('update failed. criteria:' . json_encode($criteria) . ' doc:' . json_encode($doc) . ' err:' . $ret['err']);

                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * 删除文档
     *
     * @param array $criteria 删除条件
     * @param boolean $justOne 是否只删除符合条件的第一条
     * @param int $timeout 超时时间，单位ms
     * @param int $w 成功写入到多少个复制时返回
     * @param boolean $fsync 是否等待MongoDB将数据更新到磁盘
     * @return boolean
     * @throws \MongoCursorException
     */
    public function delete(
        $criteria,
        $justOne = false,
        $timeout = 5000,
        $w = 1,
        $fsync = false
    ) {
        $options = [
            'justOne' => $justOne,
            'w' => $w,
            'fsync' => $fsync,
            'socketTimeoutMS' => $timeout,
        ];
        $ret = $this->mongoCollection->remove($criteria, $options);
        if ($w > 0) {
            if ($ret['ok'] && is_null($ret['err'])) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * 对当前Collection所在Database上执行command
     *
     * @param $command
     * @param int $timeout 超时时间，单位ms
     * @return bool
     */
    public function command($command, $timeout = 5000)
    {
        $result = $this->mongoDb->command($command, ['socketTimeoutMS' => $timeout]);
        if ($result['ok'] == 1) {
            return $result['results'];
        } else {
            Log::error("mongo command failed: command-" . json_encode($command) . " result-"
                . json_encode($result));

            return false;
        }
    }
}