> 不借助php内置函数实现
```php
$str  = 'Hello world!';
$strs = '';
for ($i = 0; true; $i++) {
    if (!isset($str[$i])) {
        break;
    }
}
for ($j = $i - 1; $j >= 0; $j--) {
    $strs .= $str[$j];
}
echo $strs;
```
> 利用数组方式实现
```php
$strArr      = str_split($str);
$strArrCount = count($strArr);
for ($i = 0; $i < $strArrCount; $i++) {
    $strs .= $strArr[$strArrCount - $i - 1];
}
echo $strs;
```
> 以上两种方式均不支持中文，这里可采用 mb_strlen, mb_substr实现中文反转(使用mb_xx函数库前请确保已经在php.ini中加载该函数库)
> ps: php内置反转函数strrev也不支持中文反转
```php
$str  = "你好世界";
$strs = '';
$count = mb_strlen($str);
for ($i = 1; $i <= $count; $i++) {
    $strs .= mb_substr($str, -$i, 1);
}
echo $strs;
```
> 采用正则匹配实现中文反转
```php
preg_match_all('/./us', $str, $arr);
$strs = implode('', array_reverse($arr[0]));
echo $strs;
```
