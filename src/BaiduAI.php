<?php

namespace Hfm\BaiduAi;

use Hfm\BaiduAi\Baidu\AipHttpClient;
use Hfm\BaiduAi\Baidu\AipImageSearch;
use Hfm\BaiduAi\Baidu\AipNlp;

class BaiduAI implements AI
{
  const BAIDU_TOKEN_API = 'https://aip.baidubce.com/oauth/2.0/token';

  protected $app_id;
  protected $app_key;
  protected $app_secret;

  /**
   * 构造函数
   * @param string $app_id
   * @param string $app_key
   * @param string $app_secret
   */
  public function __construct(string $app_id = '', string $app_key = '', string $app_secret = '')
  {
    $this->app_id = $app_id;
    $this->app_key = $app_key;
    $this->app_secret = $app_secret;
  }

  /**
   * 获取token
   * @return false|mixed
   */
  public function getToken()
  {
    $client = new AipHttpClient();
    $rs = $client->post(BaiduAI::BAIDU_TOKEN_API, [
      'grant_type'        => 'client_credentials',
      'client_id'         => $this->app_key,
      'client_secret'     => $this->app_secret,
    ]);
    if ($rs['code'] ===  200) {
      $data = json_decode($rs['content'], true);
      return $data['access_token'];
    }else {
      return false;
    }
  }

  /**
   * 提交入库
   * @param string $image_url
   * @param int[] $params
   * @return false|mixed
   */
  public function addImageToList(string $image_url = '', array $params = []) {
    $client = new AipImageSearch($this->app_id, $this->app_key, $this->app_secret);
    $image_content = $this->getImageContent($image_url);
    $rs = $client->similarAdd($image_content, [ 'brief' => json_encode($params) ]);
    if (isset($rs['log_id']) && $rs['log_id']) {
      if (!isset($rs['cont_sign'])) {
        $this->fail(json_encode($rs, JSON_UNESCAPED_UNICODE));
      }
      return true;
    }
    else
      return false;
  }

  /**
   * 查询图片
   * @param string $image_url
   * @return false|mixed
   */
  public function searchImage(string $image_url = '') {
    $client = new AipImageSearch($this->app_id, $this->app_key, $this->app_secret);
    $image_content = $this->getImageContent($image_url);
    $rs = $client->similarSearch($image_content, ['rn' => 20]);
    if (isset($rs['result_num']) && $rs['result_num'] > 0) {
      return $rs['result'];
    }else
      return false;
  }

  /**
   * 解析图片
   * @param string $image_url
   * @return false|string
   */
  public function getImageContent(string $image_url = '') {
    try {
      return file_get_contents($image_url);
    } catch (\Exception $exception) {
      return false;
    }
  }

  public function getAddressParams(string $address_text = '')
  {
    $client = new AipNlp($this->app_id, $this->app_key, $this->app_secret);
    $data = $client->address($address_text);
    return $data;
  }

  /**
   * 错误日志
   * @param $err
   */
  protected function fail($err) {
    $fp = fopen('ai.log', 'a+');
    fwrite($fp, $err."\n");
    fclose($fp);
  }
}