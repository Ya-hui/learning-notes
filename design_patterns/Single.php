<?php
/**
 * 单例模式
 *
 * @author wyh <https://github.com/Ya-hui>
 */
class Single {
    /**
     * 当前类实例
     * @var Object
     */
    protected static $conn;
    /**
     * 构造函数为private 防止创建对象
     */
    private function __construct() {

    }
    /**
     * 获取当前类实例
     * 如果没有类实例就创建，如果有就直接返回
     * @return Object 当前类实例
     */
    public static function getInstance() {
        if (!self::$conn) {
            self::$conn = new self;
        }
        return self::$conn;
    }
    /**
     * 防止对象被克隆
     * @return [type] [description]
     */
    public function __clone(){
        trigger_error('Clone is not allowed !');
    }
}
//只能这样取得实例，不能new 和 clone
$conn = Single::getInstance();
