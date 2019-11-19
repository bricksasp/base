# base

## 简介
bricksasp 基础模块

安装
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist bricksasp/base: "~1.0"
```

or add

```json
"bricksasp/base": "~1.0"
```

to the require section of your composer.json.


Configuration
-------------

To use this extension, you have to configure the Connection class in your application configuration:

```php
return [
    //....
    'components' => [
        'base' => [
            'class' => 'bricksasp\base\Module',
        ],
    ]
];
```