17mon PHP IP Library
==============================

Installation
------------------------------

Install via composer ::

    composer require wenbinye/ipip

Usage
---------------------------------

code example ::

    <?php
    use ipip\Ip;
    
    var_dump(Ip::getInstance()->find('1.192.94.203'));

```
// 返回结果
array (size=4)
  0 => string '中国' (length=6)
  1 => string '河南' (length=6)
  2 => string '郑州' (length=6)
  3 => string '' (length=0)
```
