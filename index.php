<?php

/*!
 * metowolf BilibiliHelper
 * https://i-meto.com/
 * Version 0.9.5
 *
 * Copyright 2019, metowolf
 * Released under the MIT license
 */

require 'vendor/autoload.php';

$plugins = [
    'auth',
    'capsule',
    'dailyBag',
    'giftSend',
    'group',
    'heart',
    'silver',
    'task',
];

$filename = isset($argv[1]) ? $argv[1] : 'config';

$app = new BilibiliHelper\Lib\Helper();
$t = $app->get('config');
$config = $t::parse($filename);

while (true) {
    foreach ($plugins as $plugin) {
        $t = $app->get($plugin);
        $t::run($config);
    }
    sleep(10);
}
