<?php

function feedFiles($files) {
    foreach ($files as $file) {
        if (is_dir($file))
            feedFiles(scandir($file));
        createCsv($file);
    }
}

function createCsv($file) {
    if (strpos('.log', $file)) {
        $fileCreated = fopen($file, 'r') or die ('File opening failed');
        print_r($fileCreated);
        $fileInfos = [
            'Id',
            'appCode',
            'deviceId',
            'contactable',
            'subscription_status',
            'has_downloaded_free_product_status',
            'has_downloaded_iap_product_status'
        ];

        file_put_contents('parser.csv', null, FILE_APPEND);
        $parserCsv = fopen('parser.csv', 'w');
        foreach($fileInfos as $fileInfo){
            $val = explode(",",$fileInfo);
            fputcsv($parserCsv, $val);
        }
        fclose($parserCsv);
        fclose($fileCreated);
    }
}

if (!empty($argv[1])) {
    array_shift($argv);
    foreach ($argv as $dir) {
        $files = scandir($dir);
        if ($files) {
            feedFiles($files);
        } else {
            echo 'No file or directory.';
        }
    }
} else {
    echo 'You need to enter a file/directory.';
}