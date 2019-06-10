<?php
/**
 * 乐观锁
 *
 * 每次去拿数据的时候都认为别人不会修改，所以不会上锁，但是在更新的时候会判断一下在此期间别人有没有去更新这个数据
 * redis中可以使用watch命令会监视给定的key，当exec时候如果监视的key从调用watch后发生过变化，则整个事务会失败。也可以调用watch多次监视多个key。这样就可以对指定的key加乐观锁了
 * @author wyh <https://github.com/Ya-hui>
 */
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$redis->auth('');
// 监视 count 值
$redis->watch('test');
// 开启事务
$redis->multi();
// 操作count
$time = time();
$redis->set('test', $time);
/**
 * 模拟并发下其他进程进行set count操作 请执行下面操作
 *
 * redis-cli 执行 $redis->set('count', 'is simulate'); 模拟其他终端
 */
sleep(10);
// 提交事务
echo $redis->exec() ? 'success' : 'fail';
