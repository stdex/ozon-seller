# Ozon-seller API client
[![tests](https://github.com/gam6itko/ozon-seller/actions/workflows/tests.yaml/badge.svg)](https://github.com/gam6itko/ozon-seller/actions/workflows/tests.yaml)
[![Coverage Status](https://coveralls.io/repos/github/gam6itko/ozon-seller/badge.svg?branch=master)](https://coveralls.io/github/gam6itko/ozon-seller?branch=master)

[![Latest Stable Version](https://poser.pugx.org/gam6itko/ozon-seller/v)](//packagist.org/packages/gam6itko/ozon-seller) 
[![Total Downloads](https://poser.pugx.org/gam6itko/ozon-seller/downloads)](//packagist.org/packages/gam6itko/ozon-seller) 
[![Latest Unstable Version](https://poser.pugx.org/gam6itko/ozon-seller/v/unstable)](//packagist.org/packages/gam6itko/ozon-seller) 
[![License](https://poser.pugx.org/gam6itko/ozon-seller/license)](//packagist.org/packages/gam6itko/ozon-seller)

## Документация Ozon Api 

<https://docs.ozon.ru/api/seller> 


## Установка

```shell
composer require gam6itko/ozon-seller
```

Для взаимодействия библиотеки с Ozon-Api нужно дополнительно установить реализации [PSR-18: HTTP Client](https://packagist.org/providers/psr/http-client-implementation) и [PSR-17: HTTP Factories](https://packagist.org/providers/psr/http-factory-implementation).

### Использование с Symfony
https://symfony.com/doc/current/components/http_client.html#psr-18-and-psr-17

```shell
composer require symfony/http-client
composer require nyholm/psr7
```

```php
use Gam6itko\OzonSeller\Service\V1\ProductService;
use Symfony\Component\HttpClient\Psr18Client;

$config = [$_SERVER['CLIENT_ID'], $_SERVER['API_KEY'], $_SERVER['API_URL']];
$client = new Psr18Client();
$svc = new ProductService($config, $client);
//do stuff
```

### Использование без Symfony

```shell
composer require php-http/guzzle6-adapter
```

```php
use Gam6itko\OzonSeller\Service\V1\CategoriesService;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;

$config = [
    'clientId' => '<ozon seller client-id>',
    'apiKey' => '<ozon seller api-key>',
    // 'host' по умолчанию — https://api-seller.ozon.ru
];
$client = new GuzzleAdapter(new GuzzleClient());
$requestFactory = new RequestFactory();
$streamFactory = new StreamFactory();

$svc = new CategoriesService($config, $client, $requestFactory, $streamFactory);
//do stuff
```

## local dev

```shell
composer tests   # phpunit, тесты полностью замоканы, сеть не нужна
composer psalm   # psalm --no-cache, errorLevel 4, только src
composer csfix   # php-cs-fixer (@Symfony + declare_strict_types)
```

Или в docker — на минимальной и на актуальной поддерживаемой версии PHP:

```shell
docker compose build
docker compose up php71
docker compose up php80
```

## Реализованные методы

Библиотека покрывает **457 из 463** путей текущей спецификации Ozon Seller API.

Чтобы узнать какой класс и метод реализуют запрос на нужный URL, воспользуйтесь скриптом `bin/is_realized.php`:

```shell script
php bin/is_realized.php | grep /v2/posting/fbs/get
```
output
```shell script
/v2/posting/fbs/get: Gam6itko\OzonSeller\Service\V2\Posting\FbsService::get
```

Скрипт сверяет спеку с библиотекой. Спека читается из файла: по умолчанию `var/swagger.json` (в
`.gitignore`), можно передать путь аргументом. Опция `--download` пробует скачать спеку, но обычно
не срабатывает — docs.ozon.ru закрыт JS-антиботом, поэтому `swagger.json` нужно сохранить из
браузера. Неудачная загрузка не портит уже сохранённый файл.

```shell script
php bin/is_realized.php path/to/swagger.json
php bin/is_realized.php --download
php bin/is_realized.php | grep NotRealized
```

Сознательно не реализованы:

- `/v1/review/{list,info,count,change-status,comment/delete}` — Ozon помечает их `deprecated`,
  вместо них реализованы `/v2/review/*`;
- `/v1/receipts/upload` — принимает `multipart/form-data`, а транспорт библиотеки отправляет только JSON.

Если нужного вам метода нет в библиотеке — не стесняйтесь открыть issue или PR.

## Сервисы

Класс сервиса соответствует версии URL: `/v3/product/import` → `Service\V3\ProductService::import`.
Одна и та же сущность живёт в нескольких версиях одновременно — берите ту, которая указана в
документации Ozon для нужного метода.

| Что | Классы |
|---|---|
| Товары | `V1\ProductService`, `V2\ProductService`, `V3\ProductService`, `V4\ProductService`, `V5\ProductService` |
| Категории и характеристики | `V1\DescriptionCategoryService` |
| Отправления FBS/FBO/FBP | `V{1..6}\Posting\FbsService`, `V{1,2,3}\Posting\FboService`, `V1\Posting\FbpService`, `V{1,2}\Posting\DigitalService` |
| Перевозки и грузоместа | `V{1,2}\CarriageService`, `V{1,2}\CargoesService`, `V1\AssemblyService` |
| Поставки FBO | `V{1,2,3}\SupplyOrderService`, `V{1,2}\DraftService`, `V1\FbpService`, `V1\SupplierService` |
| Склады и доставка | `V{1,2}\WarehouseService`, `V{1,2}\DeliveryMethodService`, `V{1,2}\DeliveryService`, `V{1,2}\PolygonService`, `V{1,2}\ClusterService`, `V1\PassService` |
| Заказы и отмены | `V{1,2}\OrderService`, `V1\CancelReasonService`, `V2\ConditionalCancellationService` |
| Возвраты | `V1\ReturnService`, `V1\ReturnsService`, `V2\ReturnsService`, `V3\ReturnService`, `V1\RemovalService` |
| Финансы и отчёты | `V{1,2,3}\FinanceService`, `V{1,2}\ReportService`, `V1\ReceiptsService`, `V{1,2}\InvoiceService` |
| Акции | `V{1,2}\ActionsService`, `V1\SellerActionsService`, `V1\DiscountsTaskService`, `V1\PricingStrategyService` |
| Отзывы, вопросы, чаты | `V{1,2}\ReviewService`, `V1\QuestionService`, `V{1,2,3}\ChatService` |
| Аналитика и рейтинги | `V{1,2}\AnalyticsService`, `V1\RatingService`, `V1\SearchQueriesService` |
| Аккаунт | `V1\SellerService`, `V1\BarcodeService`, `V1\NotificationService`, `V1\BrandService` |

## Примеры использования
Больше примеров смотрите в папке `tests/Service/`

### Категории и характеристики

`/v1/description-category/tree`, `/v1/description-category/attribute`

```php
use Gam6itko\OzonSeller\Enum\Language;
use Gam6itko\OzonSeller\Service\V1\DescriptionCategoryService;
use Symfony\Component\HttpClient\Psr18Client;

$config = [
    'clientId' => '<ozon seller client-id>',
    'apiKey'   => '<ozon seller api-key>',
];
$svc = new DescriptionCategoryService($config, new Psr18Client());

// дерево категорий и типов товаров
$tree = $svc->getCategoryTree(['language' => Language::RU]);

// характеристики конкретной пары «категория + тип»
$attributes = $svc->getCategoryAttributes(17028922, 91565, ['language' => Language::RU]);

// значения справочной характеристики
$values = $svc->getAttributeValues(17028922, 91565, 85, ['limit' => 100]);
```

Старые `V1\CategoriesService` и `V{2,3}\CategoryService` работают со снятыми Ozon
`/v1/category/*` и помечены `@deprecated` — в новом коде используйте `DescriptionCategoryService`.

### Отправления FBS

`/v3/posting/fbs/list`, `/v3/posting/fbs/get`

```php
use Gam6itko\OzonSeller\Service\V3\Posting\FbsService;
use Symfony\Component\HttpClient\Psr18Client;

$svc = new FbsService($config, new Psr18Client());

$result = $svc->list([
    'filter' => [
        'since'  => '2026-08-01T00:00:00.000Z',
        'to'     => '2026-08-08T00:00:00.000Z',
        'status' => 'awaiting_packaging',
    ],
    'limit' => 100,
]);

foreach ($result['postings'] as $posting) {
    echo $posting['posting_number'], PHP_EOL;
}

$posting = $svc->get('33920474-0032-1', ['analytics_data' => true]);
```

Схему crossborder Ozon закрыл: `V2\Posting\CrossborderService` оставлен для обратной
совместимости, соответствующих путей в спеке больше нет.

### Товары

#### import

`/v3/product/import`

```php
use Gam6itko\OzonSeller\Service\V3\ProductService;
use Symfony\Component\HttpClient\Psr18Client;

$config = [
    'clientId' => '<ozon seller client-id>',
    'apiKey'   => '<ozon seller api-key>',
    // 'host' по умолчанию — https://api-seller.ozon.ru
];
$svcProduct = new ProductService($config, new Psr18Client());

$product = [
    'offer_id'                => 'REDSGS9-512',
    'name'                    => 'Samsung Galaxy S9',
    'description_category_id' => 17028922,
    'type_id'                 => 91565,
    'barcode'                 => '8801643566784',
    'price'                   => '79990',
    'old_price'               => '89990',
    'currency_code'           => 'RUB',
    'vat'                     => '0',
    'height'                  => 77,
    'depth'                   => 11,
    'width'                   => 120,
    'dimension_unit'          => 'mm',
    'weight'                  => 120,
    'weight_unit'             => 'g',
    'primary_image'           => 'https://ozon-st.cdn.ngenix.net/multimedia/c1200/1022555115.jpg',
    'images'                  => [
        'https://ozon-st.cdn.ngenix.net/multimedia/c1200/1022555110.jpg',
        'https://ozon-st.cdn.ngenix.net/multimedia/c1200/1022555111.jpg',
    ],
    'attributes'              => [
        [
            'id'     => 8229,
            'values' => [['dictionary_value_id' => 971082156, 'value' => 'Смартфон']],
        ],
        [
            'id'     => 9048,
            'values' => [['value' => 'Samsung Galaxy S9']],
        ],
        [
            'id'     => 4742,
            'values' => [['value' => '512 ГБ']],
        ],
    ],
];

$res = $svcProduct->import($product);
// или пачкой (до 100 товаров за запрос)
$res = $svcProduct->import([$product, $product1, $product2]);
// или как в документации Ozon
$res = $svcProduct->import(['items' => [$product, $product1, $product2]]);

echo $res['task_id']; // сохраните, чтобы проверить результат через V1\ProductService::importInfo
```

Список полей, которые принимает `import`, задаётся в `src/config/product_validator_v3.php` —
всё лишнее отбрасывается до отправки запроса.

#### остатки и цены

```php
use Gam6itko\OzonSeller\Service\V1\ProductService as V1ProductService;
use Gam6itko\OzonSeller\Service\V5\ProductService as V5ProductService;

// цены товаров: /v5/product/info/prices
$prices = (new V5ProductService($config, $client))->infoPrices(['visibility' => 'ALL'], '', 100);

// остатки по складам FBS: /v1/product/info/stocks-by-warehouse/fbs
$stocks = (new V1ProductService($config, $client))->infoStocksByWarehouseFbs(['sku' => [160249683]]);
```
