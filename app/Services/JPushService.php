<?php


namespace App\Services;
use JPush\Client as JPush;
use Log;

class JPushService
{
    protected static $client = null;
    //推送类型
    const PUSH_TYPE_ALL = 1;
    const PUSH_TYPE_TAG = 2;
    const PUSH_TYPE_ALIAS = 3;
    const PUSH_TYPE_REG_ID = 4;
    private function __construct()
    {
    }
    private function __clone()
    {
    }
    /**
     * 获取实例
     */
    public static function getInstance()
    {
        if (!self::$client) {
            self::$client = new JPush(config('jpush.app_key'), config('jpush.master_secret'), null);
        }
        return self::$client;
    }
    /**
     * 给android或ios推送消息
     */
    public static function pushNotify($params)
    {
        //推送平台
        $platform = $params['platform'] ?? 'all';
        //推送标题
        $title = $params['title'] ?? '';
        //推送内容
        $content = $params['content'] ?? '';
        //通知栏样式ID
        $builder_id = $params['builder_id'] ?? 0;
        //附加字段
        $extras = $params['extras'] ?? '';
        //推送类型
        $type = $params['type'] ?? '';
        //推送目标(注册ID)
        $reg_id = $params['reg_id'] ?? '';
        //推送目标(标签)
        $tag = $params['tag'] ?? '';
        //推送目标(别名)
        $alias = $params['alias'] ?? '';
        try {
            $push = self::getInstance()->push();
            //设置平台
            $push->setPlatform($platform);
            switch ($type) {
                case self::PUSH_TYPE_ALL:
                    $push->addAllAudience();
                    break;
                case self::PUSH_TYPE_TAG:
                    $push->addTag($tag);
                    break;
                case self::PUSH_TYPE_ALIAS:
                    $push->addAlias($alias);
                    break;
                case self::PUSH_TYPE_REG_ID:
                    $push->addRegistrationId($reg_id);
                    break;
            }
            $push->androidNotification($content, [
                'title' =>  $title,
                'builder_id' =>  $builder_id,
                'extras' =>  $extras,
            ])->iosNotification($content, [
                'sound' =>  'sound',
                'badge' =>  '+1',
                'extras' =>  $extras
            ])->options([
                'apns_production' =>  config('jpush.apns_production', true),
                //表示离线消息保留时长(秒)
                'time_to_live' =>  86400,
            ]);
            $response = $push->send();
            if ($response['http_code'] != 200) {
                Log::channel('jpush')->error(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
            return $response;
        } catch (Throwable $e) {
            echo 9999;die;
            Log::channel('jpush')->error(json_encode([
                'file' =>  $e->getFile(),
                'line' =>  $e->getLine(),
                'message' =>  $e->getMessage(),
                'params' =>  $params,
            ], JSON_UNESCAPED_UNICODE));
        }
    }
    /**
     * 获取指定设备的别名和标签
     */
    public static function getDevices($reg_id)
    {
        $response = self::getInstance()->device()->getDevices($reg_id);
        if ($response['http_code'] == 200) {
            return $response['body'];
        }
        return [];
    }
    /**
     * 给指定设备添加标签
     */
    public static function addTags($reg_id, $tags = [])
    {
        $response = self::getInstance()->device()->addTags($reg_id, $tags);
        if ($response['http_code'] == 200) {
            return true;
        }
        return false;
    }
    /**
     * 清空指定设备的标签
     */
    public static function clearTags($reg_id)
    {
        $response = self::getInstance()->device()->clearTags($reg_id);
        if ($response['http_code'] == 200) {
            return true;
        }
        return false;
    }
    /**
     * 清空指定设备的标签
     */
    public static function removeTags($reg_id, $tags = [])
    {
        $response = self::getInstance()->device()->removeTags($reg_id, $tags);
        if ($response['http_code'] == 200) {
            return true;
        }
        return false;
    }
    /**
     * 更新指定设备的别名
     */
    public static function updateAlias($reg_id, $alias)
    {
        $response = self::getInstance()->device()->updateAlias($reg_id, $alias);
        if ($response['http_code'] == 200) {
            return true;
        }
        return false;
    }
}
