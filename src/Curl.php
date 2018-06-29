<?php
namespace Sooh;
/**
 * curl
 * 
 * @author simon.wang
 *
 */
class Curl
{
    /**
     *
     * @var Curl 
     */
    protected static $_instance;
    protected $_addons=array();
    /**
     * 当有参数时，作为初始化用，后面就空参调用获取实例了
     * 
     * 参数个数不限，可以有一个是数组，会被转换成cookie这个addons，其他必须是addons的类
     * 
     * @return Curl
     */
    public static function getInstance()
    {
        if(self::$_instance===null) {
            self::$_instance = new Curl;
        }        
        $n = func_num_args();
        if($n>0){
            foreach(self::$_instance->_addons as $k=>$v){
                $v->free();
                unset(self::$_instance->_addons[$k]);
            }

            for ($i=0;$i<$n;$i++){
                $arg = func_get_arg($i);
                if(is_array($arg)){
                    $arg = new \Sooh\CurlClasses\Cookies($arg);
                }
                $addonName = $arg->getIdentifier();
                if(isset(self::$_instance->_addons[$addonName])){
                    throw new \ErrorException('duplicate addone '.$addonName.' given');
                }
                self::$_instance->_addons[$addonName] = $arg;
            }
        }
        return self::$_instance;
    }
    
    /**
     * 需要 define (SoohServicePorxyUsed) 指明微服务的proxy的地址
     * @param type $url
     * @return type
     */
    protected function getFinalUrl($url)
    {
        if(substr($url,0,4)=='http'){
            return $url;
        }else{
            if(defined('SoohServicePorxyUsed')){
                $serviceProxy = \Sooh\Ini::getInstance()->getIni(SoohServicePorxyUsed.'.LocalProxyIPPort');
                return $serviceProxy.$url;
            }else{
                throw new \ErrorException('invalid url given: '.$url);
            }
        }
    }
    /**
     * 
     * @param string $name
     * @return \Sooh\CurlClasses\Addons
     */
    public function getAddons($name)
    {
        return isset($this->_addons[$name])?$this->_addons[$name]:null;
    }

    /**
     * 设置一次性配置，这里的identifier使用要注意
     * @param string $addoneIdentifier
     * @param mix $arg
     * @return Curl
     */
    public function disposables($addoneIdentifier,$arg)
    {
        if(!isset($this->_addons[$addoneIdentifier])){
            $c = "\\Sooh\\CurlClasses\\$addoneIdentifier";
            $this->_addons[$addoneIdentifier] = new $c;
        }
        $this->_addons[$addoneIdentifier]->disposables($arg);
        return $this;
    }

    /**
     * http get 请求 （curl_init失败返回Null）
     * @param string $url
     * @param mixed $params
     * @param int $timeOut 默认5秒
     * @return \Sooh\CurlClasses\Ret
     */
    public  function httpGet($url,$params=null,$timeOut = 5)
    {
        if(!empty($params)){
            if(strpos($url, '?')){
                $url.='&'.(is_array($params)?http_build_query($params):$params);
            }else{
                $url.='?'.(is_array($params)?http_build_query($params):$params);
            }
        }
        $ch = curl_init();
        if($ch){
            curl_setopt($ch, CURLOPT_URL, $this->getFinalUrl($url));
            $this->common_setting($ch,$timeOut);            
            $ret = \Sooh\CurlClasses\Ret::facotryByRequest($ch);
            $this->freeAddons(false);
            return $ret;
        }else{
            return null;
        }
    }
    /**
     * http post json格式的raw数据 （curl_init失败返回Null）
     * @param string $url
     * @param mixed $params
     * @param int $timeOut 默认5秒
     * @return \Sooh\CurlClasses\Ret
     */
    public function httpRawJson($url, $params,$timeOut=5)
    {
        $this->disposables('Headers', 'Content-Type: application/json');

        $ch = curl_init();

        if($ch){
            curl_setopt($ch, CURLOPT_URL, $this->getFinalUrl($url));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($params)?json_encode($params):$params);
            $this->common_setting($ch,$timeOut);            
            $ret = \Sooh\CurlClasses\Ret::facotryByRequest($ch);
            $this->freeAddons(false);
            return $ret;
        }else{
            return null;
        }
    }
    /**
     * http post 数据 （curl_init失败返回Null）
     * @param string $url
     * @param mixed $params
     * @param int $timeOut 默认5秒
     * @return \Sooh\CurlClasses\Ret
     */
    public function httpPost($url,$params,$timeOut=5)
    {
        $ch = curl_init();

        if($ch){
            curl_setopt($ch, CURLOPT_URL, $this->getFinalUrl($url));

            if(is_array($params)){
                $tmp= http_build_query($params);
                curl_setopt($ch, CURLOPT_POST, 1);
                if(strlen($tmp)<1000){
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $tmp);
                }else{
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                }
            }else{
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            }

            $this->common_setting($ch,$timeOut);            
            
            $ret = \Sooh\CurlClasses\Ret::facotryByRequest($ch);
            $this->freeAddons(false);
            return $ret;
        }else{
            return null;
        }
    }
    
    protected function common_setting($ch,$timeOut)
    {
        foreach($this->plugins as $plugin){
            $plugin->onSetOpt($ch);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut ); //--tgh 160415
    }
    
    public function freeAddons($all=true)
    {
        foreach ($this->_addons as $i=>$o){
            $o->free($all);
            if($all){
                unset($this->_addons[$i]);
            }
        }
    }
}
