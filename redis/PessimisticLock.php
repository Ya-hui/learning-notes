<?php
/**
 * 悲观锁
 * 每次处理数据时都会加锁处理结束后释放锁，同一时刻多个请求都会等待,拿到锁在执行
 *
 * @author wyh <https://github.com/Ya-hui>
 */
class PessimisticLock {
    private $config = [
        'host'    => 'localhost',
        'port'    => 6379,
        'auth'    => '',
        'timeout' => 3
    ];
    private $_redis;
    /**
     * 初始化
     * @param Array $config redis连接设定
     */
    public function __construct($config = []) {
        $this->config  = $config + $this->config;
        $this->_redis  = $this->connect();
    }
    /**
     * 获取锁
     * @param  String   $key     锁标识
     * @param  Function $success 锁获取成功回调函数
     * @param  Int      $expire  锁过期时间
     * @return Boolean
     */
    public function lock($key, $success = null, $expire = 5) {
        do {
            $is_lock = $this->_redis->set($key, time() + $expire, ['nx', 'ex' => $expire]);
            if ($is_lock && is_callable($success)) {
                call_user_func($success);
                // 释放锁
                $this->unlock($key);
            }
        } while (! $is_lock);
    }
    /**
     * 释放锁
     * @param  String $key 锁标示
     * @return void
     */
    public function unlock($key) {
        $this->_redis->del($key);
    }
    /**
     * 创建redis连接
     * @return Link
     */
    private function connect() {
        try {
            $redis = new Redis();
            $redis->connect($this->config['host'], $this->config['port'], $this->config['timeout']);
            $redis->auth($this->config['auth']);
        } catch (RedisException $e) {
            throw new Exception($e->getMessage());
        }
        return $redis;
    }
    public function test() {
        $a = $this->_redis->get('test');
        $this->_redis->set('test', $a + 1);
    }
}
$redisLocak = new PessimisticLock();
// 定义锁标识
$key        = 'mylock';
$redisLocak->lock($key, function() use ($redisLocak) {
    // 锁已成功拿到, 执行业务逻辑
    $redisLocak->test();
    echo '业务逻辑处理完成，锁自动释放';
});
