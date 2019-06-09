<?php
/**
 * 链式操作
 *
 * 可能大多数人大概都是从Jquery最先熟悉的，ORM的一系列sql操作，也是链式操作，特点是每次都返回一个对象指针
 * @author wyh <https://github.com/Ya-hui>
 */
class ChainType {
    protected $name   = '';
    protected $age    = '';
    protected $salary = '';
    public function setName($name) {
        $this->name = $name;
        return $this;
    }
    public function setAge($age) {
        $this->age = $age;
        return $this;
    }
    public function setSalary($salary) {
        $this->salary = $salary;
        return $this;
    }
    public function __toString() {
        $str =  'Name：' . $this->name . PHP_EOL;
        $str .= 'Age：' . $this->age . PHP_EOL;
        $str .= 'Salary：' . $this->salary . PHP_EOL;
        return $str;
    }
}
$chainType = (new ChainType)->setName('小明')->setAge(18)->setSalary(1000);
echo $chainType;
