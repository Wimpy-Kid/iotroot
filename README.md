### 先决条件

- PHP >= 5.3

### 安装

```shell
$ composer require cherrylu/iotroot
```

### 使用

- [初始声明](#declare)
- [编码申请](#ecodeApply)
- [编码下载-获取已审批编码](#ecodeDownload)
- [编码退回-解绑编码](#ecodeRelease)
- [获取模板](#getTemplates)
- [过滤模板字段](#getTemplateItems)
- [产品回传-上传产品](#returnProduct)
- [查询产品审核状态](#productStatus)
- [批次回传-绑定编码](#returnBatch)
- [企业Logo、营业执照数据补录](#tempCompanyData)
- [产品图片数据补录](#tempProductData)

### <span id="declare">初始声明</span>

```php
use Cherrylu\iotroot\iotroot;
use Cherrylu\iotroot\encrypter;

$iotroot = new iotroot('input-your-client-id-here', 'input-your-key-here');
```

### <span id="ecodeApply">编码申请</span>

```php
$res = $iotroot->ecodeApply(1000);
```

### <span id="ecodeDownload">编码下载-获取已审批编码</span>

```php
/** string 编码申请(ecodeApply)后返回的文件ID */
$fileId = '123123';
$res = $iotroot->queryEcodes($fileId);
```

### <span id="ecodeRelease">编码退回-解绑编码</span>

```php
/** string 编码下载(queryEcodes)返回的数据中获取 */
$ecodes = '123123';
$res = $iotroot->releaseEcodes($ecodes);
```

### <span id="getTemplates">获取模板</span>

```php
$res = $iotroot->queryTemplateInfo();
```

### <span id="getTemplateItems">过滤模板字段</span>

```php
// 获取通用模板数据
$templates = $iotroot->queryTemplateInfo();

// 必填的产品字段
$requiredProductItems = $iotroot->productItemFilter($templates['result'][0]);

// 所有产品字段
$allProductItems = $iotroot->productItemFilter($templates['result'][0], false);

// 必填的非产品字段
$requiredNonProductItems = $iotroot->nonProductItemFilter($templates['result'][0]);

// 所有的非产品字段
$allNonProductItems = $iotroot->nonProductItemFilter($templates['result'][0], false);
```

### <span id="returnProduct">产品回传-上传产品</span>

```php

/** string 模板ID，从获取模板(queryTemplateInfo)获取 */
$templateId  = '123123';
$productName = '辰砂手串';
$typeNumber  = '4208';
$img         = base64_encode(file_get_contents('img.jpg'));

/** array 产品信息，productItemFilter获取产品信息所需字段 */
$typeList = [
    [
        "type" => "销售企业信息",
        "list" => [
            [ "name" => "统一社会信用代码", "info" => 'test' ],
            [ "name" => "法人信息", "info" => 'test' ],
            [ "name" => "机构地址", "info" => 'test' ],
            [ "name" => "联系方式", "info" => 'test' ],
            [ "name" => "机构名称", "info" => 'test' ],
            [ "name" => "CNAS检测机构", "info" => 'test' ],
            [ "name" => "CMA检测机构", "info" => 'test' ],
        ]
    ],
    [
        "type" => "认证信息",
        "list" => [
            [ "name" => "认证单位", "info" => 'test' ],
            [ "name" => "认证标准", "info" => 'test' ]
        ]
    ],
];
$res = $iotroot->returnProduct($productName, $typeNumber, $templateId, $img, $typeList);
```

### <span id="productStatus">查询产品审核状态</span>

```php
/** string 产品回传(returnProduct)后返回的returnId */
$returnId = '123123';
$res = $iotroot->queryProductStatus($returnId);
```

### <span id="returnBatch">批次回传-绑定编码</span>

```php

/** string 产品审核状态(queryProductStatus)返回的数据中获取 */
$productCode = '123123';

/** array 编码下载(queryEcodes)返回的数据中获取 */
$ecodes = ['123123'];

/** array 从nonProductItemFilter获取所需字段 */
$moduleList = [
    [
        'type' => '监督检查信息',
        'detailList' => [
            ['name' => '检测日期', 'info' => '2023-09-09'],
            ['name' => '产品图片', 'info' => 'https://img.zbtesting.cn/319/2024/0127/ZSJ32401460511-ssdWUF.jpg'],
            ['name' => '检测标准', 'info' => 'CMA'],
            ['name' => '检测员', 'info' => '李四'],
            ['name' => '追溯码', 'info' => '5609'],
            ['name' => '授权签字人', 'info' => '张三'],
            ['name' => '证书编号', 'info' => '10101010101'],
            ['name' => '检测结论', 'info' => '辰砂手串'],
        ]
    ],
    [
        'type' => '检验检测信息',
        'detailList' => [
            [ 'name' => '多色性', 'info' => '未检'],
            [ 'name' => '双折射率', 'info' => '未检'],
            [ 'name' => '折射率', 'info' => '1.53（点测）'],
            [ 'name' => '荧光观察', 'info' => '未检'],
            [ 'name' => '红外光谱', 'info' => '未检'],
            [ 'name' => '紫外可见光谱', 'info' => '未检'],
            [ 'name' => '密度', 'info' => '因绳未测'],
            [ 'name' => '质量', 'info' => '41.16g'],
            [ 'name' => '外观描述', 'info' => '红色'],
            [ 'name' => '光性特征', 'info' => '未检'],
            [ 'name' => '放大检查', 'info' => '粒状结构'],
        ]
    ]
];

$iotroot->ecodeBatchReturn($productCode, $ecodes, $moduleList);
```

### <span id="tempCompanyData">企业Logo、营业执照数据补录</span>

```php
/** string 产品回传(returnProduct)后返回的returnId */
$logoBase64 = base64_encode(file_get_contents('logo.jpg')); // 可以为空字符
$licenseBase64 = base64_encode(file_get_contents('license.jpg')); // 可以为空字符
$res = $iotroot->queryProductStatus($logoBase64, $licenseBase64);

```

### <span id="tempProductData">产品图片数据补录</span>

##### <span style="color:red;">切勿泄露产品code码，有漏洞可以利用产品code码篡改数据</span>

```php
/** string 产品回传(returnProduct)后返回的returnId，也可以通过查看网页上产品列表api返回的数据获取，目前没有api能直接获取所有产品的code */
$productCode = '123123123123';
$imgBase64 = base64_encode(file_get_contents('img.jpg'));
$res = $res = $iotroot->tempProductData($productCode, $imgBase64);

```