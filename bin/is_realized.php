<?php

declare(strict_types=1);

const SWAGGER_URL = 'https://docs.ozon.ru/api/seller/swagger.json';
const SWAGGER_LOCAL = __DIR__.'/../var/swagger.json';

$whereIsAutoloader = [
    dirname(__DIR__).'/vendor/autoload.php',
    dirname(__DIR__).'/autoload.php',
];

foreach ($whereIsAutoloader as $filepath) {
    if (file_exists($filepath)) {
        require_once $filepath;
        break;
    }
}

use Gam6itko\OzonSeller\Service\V1\ActionsService;
use Gam6itko\OzonSeller\Service\V1\CargoesService;
use Gam6itko\OzonSeller\Service\V1\CarriageService;
use Gam6itko\OzonSeller\Service\V1\DeliveryMethodService;
use Gam6itko\OzonSeller\Service\V1\DescriptionCategoryService;
use Gam6itko\OzonSeller\Service\V1\Posting\FbsService as V1FbsService;
use Gam6itko\OzonSeller\Service\V1\ProductService as V1ProductService;
use Gam6itko\OzonSeller\Service\V1\ReportService;
use Gam6itko\OzonSeller\Service\V1\ReturnService as V1ReturnService;
use Gam6itko\OzonSeller\Service\V1\SearchQueriesService;
use Gam6itko\OzonSeller\Service\V1\SellerService;
use Gam6itko\OzonSeller\Service\V1\SupplyOrderService;
use Gam6itko\OzonSeller\Service\V2\SupplyOrderService as V2SupplyOrderService;
use Gam6itko\OzonSeller\Service\V3\SupplyOrderService as V3SupplyOrderService;
use Gam6itko\OzonSeller\Service\V2\DeliveryMethodService as V2DeliveryMethodService;
use Gam6itko\OzonSeller\Service\V2\Posting\FbsService;
use Gam6itko\OzonSeller\Service\V2\ProductService as V2ProductService;
use Gam6itko\OzonSeller\Service\V2\WarehouseService;
use Gam6itko\OzonSeller\Service\V3\Posting\FbsService as V3FbsService;
use Gam6itko\OzonSeller\Service\V3\ProductService as V3ProductService;
use Gam6itko\OzonSeller\Service\V4\ProductService as V4ProductService;
use Gam6itko\OzonSeller\Service\V4\Posting\FbsService as V4FbsService;
use Gam6itko\OzonSeller\Service\V5\Posting\FbsService as V5FbsService;
use Gam6itko\OzonSeller\Service\V5\ProductService as V5ProductService;
use Gam6itko\OzonSeller\Service\V6\Posting\FbsService as V6FbsService;
use GuzzleHttp\Client;

const MAPPING = [
    // Здесь только те URL, для которых не работает конвенция findMethod()
    // (имя метода не выводится из пути). Остальное резолвится автоматически.

    // V1
    '/v1/actions'                                    => [ActionsService::class, 'list'],
    '/v1/roles'                                      => [SellerService::class, 'roles'],
    '/v1/cargoes-label/create'                       => [CargoesService::class, 'labelCreate'],
    '/v1/cargoes-label/get'                          => [CargoesService::class, 'labelGet'],
    '/v1/cargoes-label/file/{file_guid}'             => [CargoesService::class, 'labelFile'],
    '/v1/posting/carriage-available/list'            => [CarriageService::class, 'availableList'],
    '/v1/supply-order/status/counter'          => [SupplyOrderService::class, 'statusCounter'],
    '/v1/supply-order/bundle'                  => [SupplyOrderService::class, 'bundle'],
    '/v1/supply-order/details'                 => [SupplyOrderService::class, 'details'],
    '/v1/supply-order/cancel'                  => [SupplyOrderService::class, 'cancel'],
    '/v1/supply-order/cancel/status'           => [SupplyOrderService::class, 'cancelStatus'],
    '/v1/supply-order/timeslot/get'            => [SupplyOrderService::class, 'timeslotGet'],
    '/v1/supply-order/timeslot/update'         => [SupplyOrderService::class, 'timeslotUpdate'],
    '/v1/supply-order/timeslot/status'         => [SupplyOrderService::class, 'timeslotStatus'],
    '/v1/supply-order/pass/create'             => [SupplyOrderService::class, 'passCreate'],
    '/v1/supply-order/pass/status'             => [SupplyOrderService::class, 'passStatus'],
    '/v1/supply-order/content/update'          => [SupplyOrderService::class, 'contentUpdate'],
    '/v1/supply-order/content/update/status'   => [SupplyOrderService::class, 'contentUpdateStatus'],
    '/v1/supply-order/content/update/validation'  => [SupplyOrderService::class, 'contentUpdateValidation'],
    '/v1/supply-order/act/summary/get'         => [SupplyOrderService::class, 'actSummaryGet'],
    '/v1/supply-order/act/product/get'         => [SupplyOrderService::class, 'actProductGet'],
    '/v1/supply-order/act/accept'              => [SupplyOrderService::class, 'actAccept'],
    '/v1/supply-order/act/accept/status'       => [SupplyOrderService::class, 'actAcceptStatus'],
    '/v2/supply-order/timeslot/list'                  => [V2SupplyOrderService::class, 'timeslotList'],
    '/v3/supply-order/list'                          => [V3SupplyOrderService::class, 'list'],
    '/v3/supply-order/get'                           => [V3SupplyOrderService::class, 'get'],
    '/v1/delivery-method/list'                       => [DeliveryMethodService::class, 'list'],
    '/v1/delivery-method/return/settings/get'        => [DeliveryMethodService::class, 'returnSettingsGet'],
    '/v1/search-queries/text'                        => [SearchQueriesService::class, 'text'],
    '/v1/search-queries/top'                         => [SearchQueriesService::class, 'top'],
    // multipart/form-data, транспорт библиотеки отправляет только JSON
    '/v1/receipts/upload'                            => null,
    '/v1/description-category/tree'                    => [DescriptionCategoryService::class, 'getCategoryTree'],
    '/v1/description-category/attribute'               => [DescriptionCategoryService::class, 'getCategoryAttributes'],
    '/v1/description-category/attribute/values'        => [DescriptionCategoryService::class, 'getAttributeValues'],
    '/v1/description-category/attribute/values/search' => [DescriptionCategoryService::class, 'searchAttributeValues'],
    '/v1/product/info/description'                   => [V1ProductService::class, 'infoDescription'],
    '/v1/product/info/stocks-by-warehouse/fbs'       => [V1ProductService::class, 'infoStocksByWarehouseFbs'],
    '/v1/product/update/discount'                    => [V1ProductService::class, 'updateDiscount'],
    '/v1/posting/fbs/package-label/get'              => [V1FbsService::class, 'packageLabelGet'],
    '/v1/posting/fbs/cancel-reason'                  => [V1FbsService::class, 'cancelReason'],
    '/v1/posting/cancel'                             => [V1FbsService::class, 'cancel'],
    '/v1/posting/cancel/status'                      => [V1FbsService::class, 'cancelStatus'],
    '/v1/posting/marks'                              => [V1FbsService::class, 'marks'],
    '/v1/posting/cutoff/set'                         => [V1FbsService::class, 'cutoffSet'],
    '/v1/posting/unpaid-legal/product/list'          => [V1FbsService::class, 'unpaidLegalProductList'],
    '/v1/posting/global/etgb'                        => [V1FbsService::class, 'globalEtgb'],
    '/v1/posting/digital/codes/upload'               => [V1FbsService::class, 'digitalCodesUpload'],
    '/v1/fbs/posting/product/exemplar/update'        => [V1FbsService::class, 'productExemplarUpdate'],
    '/v1/report/products/create'                     => [ReportService::class, 'products'],
    '/v1/returns/list'                               => [V1ReturnService::class, 'list'],

    // V3
    '/v3/posting/multiboxqty/set'                    => [V3FbsService::class, 'multiBoxQtySet'],

    // V2
    '/v2/delivery-method/list'                       => [V2DeliveryMethodService::class, 'list'],
    '/v2/fbs/posting/delivered'                      => [FbsService::class, 'delivered'],
    '/v2/fbs/posting/delivering'                     => [FbsService::class, 'delivering'],
    '/v2/fbs/posting/last-mile'                      => [FbsService::class, 'lastMile'],
    '/v2/fbs/posting/tracking-number/set'            => [FbsService::class, 'setTrackingNumber'],
    '/v2/posting/fbs/cancel-reason/list'             => [FbsService::class, 'cancelReasons'],
    '/v2/posting/fbs/product/country/list'           => [FbsService::class, 'productCountryList'],
    '/v2/posting/fbs/product/country/set'            => [FbsService::class, 'productCountrySet'],
    '/v2/posting/fbs/package-label/create'           => [FbsService::class, 'packageLabelCreate'],
    '/v2/product/info/stocks-by-warehouse/fbs'       => [V2ProductService::class, 'infoStocksByWarehouseFbs'],
    '/v2/products/delete'                            => [V2ProductService::class, 'delete'],
    '/v2/products/stocks'                            => [V2ProductService::class, 'importStocks'],
    '/v2/warehouse/list'                             => [WarehouseService::class, 'list'],

    // V3
    '/v3/product/info/list'                          => [V3ProductService::class, 'infoList'],
    '/v3/product/list'                               => [V3ProductService::class, 'list'],
    '/v3/product/import'                             => [V3ProductService::class, 'import'],

    // V4
    '/v4/product/info/stocks'                        => [V4ProductService::class, 'infoStocks'],
    '/v4/product/info/attributes'                    => [V4ProductService::class, 'infoAttributes'],
    '/v4/posting/fbs/list'                           => [V4FbsService::class, 'list'],
    '/v4/posting/fbs/unfulfilled/list'               => [V4FbsService::class, 'unfulfilledList'],

    // V5
    '/v5/product/info/prices'                        => [V5ProductService::class, 'infoPrices'],

    // V6
    '/v5/fbs/posting/product/exemplar/status'        => [V5FbsService::class, 'productExemplarStatus'],
    '/v5/fbs/posting/product/exemplar/validate'      => [V5FbsService::class, 'productExemplarValidate'],
    '/v6/fbs/posting/product/exemplar/create-or-get' => [V6FbsService::class, 'productExemplarCreateOrGet'],
    '/v6/fbs/posting/product/exemplar/set'           => [V6FbsService::class, 'productExemplarSet'],

    // Убрано из MAPPING: Ozon удалил эти URL из спеки, в MAPPING они были мертвы,
    // потому что скрипт обходит только пути из swagger.json. Реализации в библиотеке
    // остались (помечены @deprecated) — что пришло на замену:
    //   /v1/category/tree, /v1/category/attribute,
    //   /v1/categories/tree/{category_id}, /v1/categories/{category_id}/attributes  -> /v1/description-category/*
    //   /v1/products/info/{product_id}                                              -> /v3/product/info/list
    //   /v1/product/list/price, /v1/products/prices                                 -> /v1/product/prices/details
    //   /v1/products/list                                                           -> /v3/product/list
    //   /v1/products/stocks, /v1/products/update                                    -> /v2/products/stocks
    //   /v1/product/prepayment/set                                                  -> удалено без замены
    //   /v2/products/info/attributes                                                -> /v4/product/info/attributes
    //   /v4/product/info/prices                                                     -> /v5/product/info/prices
    //   /v2/returns/company/fbo, /v2/returns/company/fbs                            -> /v1/returns/list
    //   /v2/posting/crossborder/*                                                   -> схема crossborder закрыта
    //   /v5/fbs/posting/product/exemplar/{create-or-get,set}                        -> /v6/fbs/posting/product/exemplar/*
];

$json = loadSwagger(array_slice($argv, 1));
$swagger = json_decode($json, true);
if (JSON_ERROR_NONE !== json_last_error()) {
    fwrite(STDERR, 'Invalid swagger json: '.json_last_error_msg().PHP_EOL);
    exit(1);
}

foreach ($swagger['paths'] as $path => $confArr) {
    if (array_key_exists($path, MAPPING)) {
        $classMethod = MAPPING[$path];
    } else {
        $classMethod = findMethod($path);
    }

    echo "$path: ";

    // mark as deprecated
    $conf = reset($confArr);
    if (!empty($conf['deprecated']) && isDeprecated($path)) {
        echo "\033[01;33mdeprecated \033[0m";
    }

    if (empty($classMethod)) {
        echo "\033[01;31mNotRealized\033[0m";
    } else {
        // show class::method
        echo "\033[01;32m".implode('::', $classMethod)."\033[0m";
    }

    echo PHP_EOL;
}

/**
 * Спека читается из файла: аргумент командной строки или var/swagger.json.
 * С опцией --download сначала пытается скачать её в var/swagger.json.
 *
 * @param string[] $argv
 */
function loadSwagger(array $argv): string
{
    $download = false;
    $filepath = null;
    foreach ($argv as $arg) {
        switch (true) {
            case '--download' === $arg:
                $download = true;
                break;
            case '--help' === $arg || '-h' === $arg:
                echo usage();
                exit(0);
            case 0 === strpos($arg, '-'):
                fwrite(STDERR, "Unknown option `$arg`".PHP_EOL.usage());
                exit(1);
            default:
                $filepath = $arg;
        }
    }

    if (null === $filepath) {
        $filepath = SWAGGER_LOCAL;
    }

    if ($download) {
        download($filepath);
    }

    if (!is_readable($filepath)) {
        fwrite(STDERR, "Swagger file `$filepath` not found.".PHP_EOL.usage());
        exit(1);
    }

    fwrite(STDERR, "Using swagger file: $filepath".PHP_EOL);

    return (string) file_get_contents($filepath);
}

function download(string $filepath): void
{
    fwrite(STDERR, 'Downloading '.SWAGGER_URL.PHP_EOL);

    try {
        $contents = (new Client())
            ->get(SWAGGER_URL)
            ->getBody()
            ->getContents();
    } catch (Throwable $e) {
        fwrite(STDERR, <<<TEXT
Download failed: {$e->getMessage()}
docs.ozon.ru is protected by an anti-bot challenge, so downloading usually fails.
Save the spec from a browser to `$filepath` manually.

TEXT
        );
        exit(1);
    }

    if (null === json_decode($contents, true)) {
        fwrite(STDERR, <<<TEXT
Downloaded content is not a valid json (anti-bot challenge?).
Save the spec from a browser to `$filepath` manually.

TEXT
        );
        exit(1);
    }

    $dir = dirname($filepath);
    if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
        fwrite(STDERR, "Can't create directory `$dir`".PHP_EOL);
        exit(1);
    }

    if (false === file_put_contents($filepath, $contents)) {
        fwrite(STDERR, "Can't write `$filepath`".PHP_EOL);
        exit(1);
    }

    fwrite(STDERR, 'Saved to '.$filepath.PHP_EOL);
}

function usage(): string
{
    $local = SWAGGER_LOCAL;
    $url = SWAGGER_URL;

    return <<<TEXT
Usage: php bin/is_realized.php [--download] [path/to/swagger.json]

  path/to/swagger.json  OpenAPI spec to check against, default `$local`
  --download            download the spec from $url before checking
                        and store it to the target file

TEXT;
}

function isDeprecated(string $path): bool
{
    if (null === ($arr = findMethod($path))) {
        return true;
    }

    [$class, $method] = $arr;

    $refClass = new ReflectionClass($class);
    $refMethod = $refClass->getMethod($method);
    if (!$docComment = $refMethod->getDocComment()) {
        return false;
    }

    return false !== strpos($docComment, '@deprecated');
}

/**
 * @return array|null
 */
function findMethod(string $path)
{
    $prefix = 'Gam6itko\\OzonSeller\\Service\\';
    $arr = array_map('ucfirst', array_filter(explode('/', $path)));
    do {
        $key = array_shift($arr);
        $class = $prefix.$key.'Service';
        if (class_exists($class)) {
            break;
        }

        $prefix .= $key.'\\';
    } while (!empty($arr));

    if (empty($arr)) {
        return null;
    }

    $arr = array_map(static function (string $string): string {
        return implode('', array_map('ucfirst', preg_split('/(_|-)/', $string)));
    }, $arr);
    $method = lcfirst(implode('', $arr));

    if (method_exists($class, $method)) {
        return [$class, $method];
    }

    return null;
}
