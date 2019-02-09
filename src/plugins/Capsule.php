<?php

/*!
 * metowolf BilibiliHelper
 * https://i-meto.com/
 *
 * Copyright 2018, metowolf
 * Released under the MIT license
 */

namespace BilibiliHelper\Plugin;

use BilibiliHelper\Lib\Log;
use BilibiliHelper\Lib\Curl;

class Capsule extends Base
{
    const PLUGIN_NAME = 'capsule';

    protected static function init()
    {
        if (!static::data('lock')) {
            static::data('lock', time());
        }
    }

    protected static function work()
    {
        if (static::data('lock') > time()) {
            return;
        }

        $count = static::info();
        $step = 100;
        while ($count && $step) {
            while ($count >= $step) {
                $count = static::open($step);
                sleep(mt_rand(0, 5));
            }
            $step = intval($step / 10);
        }

        static::data('lock', time() + 86400);
    }

    public static function info()
    {
        $payload = [];
        $data = Curl::get('https://api.live.bilibili.com/xlive/web-ucenter/v1/capsule/get_detail', static::sign($payload));
        $data = json_decode($data, true);

        if (isset($data['code']) && $data['code']) {
            Log::warning("扭蛋币余额查询异常");
            return 0;
        }
        Log::info("当前还有 {$data['data']['normal']['coin']} 枚扭蛋币");

        return $data['data']['normal']['coin'];
    }

    public static function open($num)
    {
        $csrf = Curl::getCsrf();

        $payload = [
            'csrf' => $csrf,
            'csrf_token' => $csrf,
            'count' => $num,
            'type' => 'normal',
            'platform' => 'h5',
        ];
        $data = Curl::post('https://api.live.bilibili.com/xlive/web-ucenter/v1/capsule/open_capsule', $payload);
        $data = json_decode($data, true);

        if (isset($data['code']) && $data['code']) {
            Log::warning("扭蛋失败，稍后重试");
            return 0;
        }

        if (isset($data['data']['awards'])) {
            foreach ($data['data']['awards'] as $vo) {
                Log::notice("扭蛋成功，获得 {$vo['num']} 个{$vo['name']}");
            }
        }

        return isset($data['data']['coin']) ? $data['data']['coin'] : 0;
    }
}
