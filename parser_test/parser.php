<?php

const FILEEXTENSIONIMPORT = ".log";
const APPCODEFILE = "appCodes.ini";
const EXPORTDIR = "exports";
const FILEEXTENSIONEXPORT = ".csv";

/**
 * Class Row
 */
class Row
{
    const activeSubscriber = "active_subscriber";
    const expiredSubscriber = "expired_subscriber";
    const neverSubscribed = "never_subscribed";
    const subscriptionUnknown = "subscription_unknown";

    const hasDownloadedFreeProduct = "has_downloaded_free_product";
    const downloadedFreeProductUnknown = "downloaded_free_product_unknown";

    const hasDownloadedIapProduct = "has_downloaded_iap_product";
    const downloadedIapProductUnknown = "downloaded_iap_product_unknown";

    const notDownloadedFreeProduct = "not_downloaded_free_product";

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $appCode;

    /**
     * @var string
     */
    private $deviceId;

    /**
     * @var bool
     */
    private $contactable;

    /**
     * @var string
     */
    private $subscriptionStatus;

    /**
     * @var string
     */
    private $hasDownloadedFreeProductStatus;

    /**
     * @var string
     */
    private $hasDownloadedIapProductStatus;

    /**
     * Line constructor.
     * @param int $id
     * @param string $appCode
     * @param string $deviceId
     * @param bool $contactable
     * @param array $tags
     */
    public function __construct(
        int $id,
        string $appCode,
        string $deviceId,
        bool $contactable,
        array $tags
    )
    {
        $this->id = $id;
        $this->appCode = $appCode;
        $this->deviceId = $deviceId;
        $this->contactable = $contactable;
        $this->subscriptionStatus = $this->findSubscriptionStatus($tags);
        $this->hasDownloadedFreeProductStatus = $this->findHasDownloadedFreeProductStatus($tags);
        $this->hasDownloadedIapProductStatus = $this->findHasDownloadedIapProductStatus($tags);
    }

    /**
     * @param array $tags
     * @return string|null
     */
    private static function findHasDownloadedFreeProductStatus(array $tags): ?string
    {
        if (in_array(Row::hasDownloadedFreeProduct, $tags)) {
            return Row::hasDownloadedFreeProduct;
        } elseif (in_array(Row::downloadedFreeProductUnknown, $tags)){
            return Row::downloadedFreeProductUnknown;
        } elseif (in_array(Row::notDownloadedFreeProduct, $tags)){
            return Row::notDownloadedFreeProduct;
        }
        return null;
    }

    /**
     * @param array $tags
     * @return string|null
     */
    private static function findHasDownloadedIapProductStatus(array $tags): ?string
    {
        if (in_array(Row::hasDownloadedIapProduct, $tags)) {
            return Row::hasDownloadedIapProduct;
        } elseif (in_array(Row::downloadedIapProductUnknown, $tags)){
            return Row::downloadedIapProductUnknown;
        } elseif (in_array(Row::notDownloadedFreeProduct, $tags)){
            return Row::notDownloadedFreeProduct;
        }
        return null;
    }

    /**
     * @param array $tags
     * @return string|null
     */
    private static function findSubscriptionStatus(array $tags): ?string
    {
        if (in_array(Row::activeSubscriber, $tags)) {
            return Row::activeSubscriber;
        } elseif (in_array(Row::expiredSubscriber, $tags)){
            return Row::expiredSubscriber;
        } elseif (in_array(Row::neverSubscribed, $tags)){
            return Row::neverSubscribed;
        } elseif (in_array(Row::subscriptionUnknown, $tags)){
            return Row::subscriptionUnknown;
        }
        return null;
    }
}

/**
 * @param $string
 * @param $endString
 * @return bool
 */
function endsWith($string, $endString): bool
{
    $len = strlen($endString);
    if ($len == 0) {
        return true;
    }
    return (substr($string, -$len) === $endString);
}

/**
 * @param $folders
 * @param $path
 * @param $appCodes
 */
function getFolder($folders, $path, $appCodes) {

    foreach ($folders as $fileOrFolders) {
        getFile($fileOrFolders, $path, $appCodes);
    }
}

/**
 * @param $fileOrFolders
 * @param $path
 * @param $appCodes
 */
function getFile($fileOrFolders, $path, $appCodes) {
    if (is_dir($fileOrFolders)) {
        $path = ($path ? $path . '/' : null) . $fileOrFolders . '/';
        $folders = scandir($fileOrFolders);
        $folders = array_diff($folders, ['.', '..']);

        foreach ($folders as $folder) {
            if (is_dir($folder)) {
                $subFolders = scandir($folder);
                $subFolders = array_diff($subFolders, ['.', '..']);
                getFolder($subFolders, $path, $appCodes);
            } else {
                createCsvFromFile($folder, $path, $appCodes);
            }
        }

    } else {
        createCsvFromFile($fileOrFolders, $path, $appCodes);
    }
}

/**
 * @param $file
 * @param $path
 * @param $appCodes
 */
function createCsvFromFile($file, $path, $appCodes) {
    $pathFile = $path . $file;
    if (str_ends_with($pathFile, FILEEXTENSIONIMPORT)) { // if you are < Php 8.0 use endsWith($pathFile, '.log')
        $getContentOfFile = explode(PHP_EOL, file_get_contents($pathFile, false));
        array_shift($getContentOfFile);
        $getContentOfFile = array_filter($getContentOfFile);
        $id = 1;
        $arrayExport = [];

        foreach ($getContentOfFile as $importedRow) {
            $importedRow = explode(",", $importedRow);
            $indexAppCode = array_search($importedRow[0], $appCodes) - 1;
            $tags = !empty($importedRow[3]) ? explode('|', $importedRow[3]) : [];
            $row = new Row(
                $id,
                $appCodes[$indexAppCode],
                $importedRow[1],
                $importedRow[2],
                $tags
            );
            $arrayExport[] = $row;
            $id++;
        }

        $fileInfos = [
            'Id',
            'appCode',
            'deviceId',
            'contactable',
            'subscription_status',
            'has_downloaded_free_product_status',
            'has_downloaded_iap_product_status'
        ];

        $file = EXPORTDIR .'/' . substr($file, 0, -4) . FILEEXTENSIONEXPORT;
        file_put_contents( $file, null, FILE_APPEND);
        $parserCsv = fopen($file, 'w');
        fputcsv($parserCsv, $fileInfos);

        foreach($arrayExport as $feedRow){
            fputcsv($parserCsv, (array)$feedRow);
        }

        fclose($parserCsv);
    }
}

$folders = scandir(getcwd());
$folders = array_diff($folders, ['.', '..']);
if (!is_dir(EXPORTDIR)) {
    mkdir(EXPORTDIR, 0777);
}

if ($folders) {
    $getAppCodes = preg_split
    (
        "/(=|\n)/",
        file_get_contents(APPCODEFILE, false)
    );
    $getAppCodes = str_replace('"', "", array_map('trim', $getAppCodes));
    getFolder($folders, null, $getAppCodes);
} else {
    echo 'Your directory is empty.';
}