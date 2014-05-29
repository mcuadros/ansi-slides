ANSISlides
==============================

ANSISlides is a Terminal presentation application written in PHP.

The motivation of this tools was made shomething fun and fresh for my presentation in the [deSymfonyDay 2014](http://day.desymfony.com/). The abstraction not is very good for lack of time. Don't judge me for this code.

Requirements
------------

* PHP 5.4.x

Installation
------------

Assuming you have git and composer installed execute:

```sh
git clone git@github.com:mcuadros/ansi-slides.git
cd ansi-slides
composer install
```

Usage
-----

```./bin/ansislide play example/example.md```


Format
------

The format of the presentations is something similar to Markdown, with some tricks and hacks.

### Headers
```
# Header
```

![Header](example/images/header.png)

### Small headers
```
## Small headers
```

![Small header](example/images/small_header.png)

### Code Highlighting
<pre lang="no-highlight"><code>
```php
class BlockOfCode {
    public function __construct() {
        $this->foo = 2;
    }
}

$obj = new BlockOfCode();
```
</code></pre>

![Blockcode](example/images/blockcode.png)

### ANSI Colors
```
![white,dark_gray](Texts in )![red,dark_gray](many )![green,dark_gray](colors)

```

![Colors](example/images/colors.png)

### Emphasis
```
Emphasis: **underline**

```

![Colors](example/images/emphasis.png)

### Lists
```
- Lists
- Items are showed
- One by one

```

![Colors](example/images/list.png)

### Images
```
![Image](images/desymfonyday.008.jpg)
```

This only works at [iTerm2](http://www.iterm2.com/#/section/home), inspired by [tkn](https://github.com/fxn/tkn)

License
-------

MIT, see [LICENSE](LICENSE)
