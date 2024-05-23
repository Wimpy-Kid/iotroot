### 先决条件

- PHP >= 5.3

### 安装

```shell
$ composer require cherrylu/iotroot
```

### 使用

```php
use Cherrylu\iotroot\iotroot;
use Cherrylu\iotroot\encrypter;

$iotroot = new iotroot('input-your-client-id-here', 'input-your-key-here');

$templates = $iotroot->getTemplates();

var_dump([
    'client_id' => encrypter::getClientId(),
    'key' => encrypter::getKey(),
    'time_stamp' => encrypter::getTimeStamp(),
    'sign' => encrypter::getSignString(),
    'templates' => $templates,
]);

```