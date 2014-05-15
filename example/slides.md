[green,black]
# The Hitchhiker's
# Guide to the HHVM
## Por Máximo Cuadros / @mcuadros_
---
[black,yellow]
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
┌──────────┬──────────┐
│ Version  │ Elapsed  │
├──────────┼──────────┤
│ 2.5.0    │ 0.012sec │
├──────────┼──────────┤
│ 2.6.0    │ 1.012sec │
├──────────┼──────────┤
│ 2.7.0    │ 2.312sec │
└──────────┴──────────┘
```
