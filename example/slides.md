# The Hitchhiker's
# Guide to the HHVM
## Por Máximo Cuadros / @mcuadros_
---
# Cuanto cabe
* Una prueba
* Otra prueba
* Y terminando
---
![Example Image](/../../example/images/9151048607_b5a552c4dd_c.jpg)
---
# algo de codigo

```php
protected function align($text, $path = STR_PAD_RIGHT)
{
    $result = [];
    foreach (explode("\n", $text) as $line) {
        $result[] = $line;
    }

    return implode("\n", $result);
}
```
---
# Test
1. Una prueba
2. Otra prueba
---
# A table
```
┌───────────────────────────────┬──────────┬───────┐
│ Code @ VM                     │ Elapsed  │ Ratio │
├───────────────────────────────┼──────────┼───────┤
│ Pimple @ PHP 5.3.10           │ 0m4.753s │ 1.00x │
├───────────────────────────────┼──────────┼───────┤
│ Pimple @ HipHop VM 3.0.0      │ 0m2.458s │ 1.91X │
├───────────────────────────────┼──────────┼───────┤
│ pimple-hack @ HipHop VM 3.0.0 │ 0m1.729s │ 2.74x │
└───────────────────────────────┴──────────┴───────┘
```
