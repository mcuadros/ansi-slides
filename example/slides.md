![green,black]
# The Hitchhiker's
# Guide to the HHVM
## Por Máximo Cuadros / @mcuadros_
---
![yellow,black]
# Cuanto cabe
![green,black]* Una prueba
* Otra prueba
* Y terminando
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
![Example Image](images/9151048607_b5a552c4dd_c.jpg)
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
