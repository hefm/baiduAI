<?php
/**
 * 测试文件
 */
require 'vendor/autoload.php';

use Hfm\BaiduAi\BaiduAI;

$AI = new BaiduAI('25871049', '9S5kIWFbaGezK5tVhngS3kzA', 'YjZwm6Y0G3oe24ESNe8jKwpbAuBpPQwq');
$data = $AI->getAddressParams('上海市浦东新区纳贤路701号百度上海研发中心 F4A000 张三');
echo "<pre>";
var_dump($data);
