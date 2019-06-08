1.比如有一个数组是sql in值(select * from table where name in('a', 'b', 'c', 'd'))
```php
    $data = ['a', 'b', 'c', 'd'];
    $str  = '';
    foreach($data as $value) {
        $str .= "'{$value}',";
    }
    echo rtrim($str, ','); // 'a','b','c','d'
```
巧用implode方法
```php
$data = ['a', 'b', 'c', 'd'];
$str  = "'" . implode("','", $data) . "'";
echo $str; // 'a','b','c','d'
```
