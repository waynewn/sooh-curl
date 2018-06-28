<?php
namespace Sooh\CurlClasses;

/** 
 * 代理设置
 */

class Proxy  implements Addons{
    public $proxyType;
    public $proxyServer;
    public $proxyPort;
    public $proxyUser;
    public $proxyPass;
    
    public function __construct($serv,$port,$user=null,$pass=null,$type='http'){
        $this->proxyServer = $serv;
        $this->proxyPort = $port;
        $this->proxyUser = $user;
        $this->proxyPass = $pass;
        $this->proxyType = $type;
    }
    public function disposables($arr)
    {
        throw new \ErrorException('todo(curl->proxy->disposables)');
    }
    
    public function onSetOpt($ch)
    {
        if($this->proxyType == 'http'){
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
            curl_setopt($ch, CURLOPT_PROXY,$this->proxyServer.':'.$this->proxyPort);
            if(!empty($this->proxyPass)){
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyUser.':'.$this->proxyPass);
            }
        }
    }
    public function free($all=true)
    {

    }
    final public function getIdentifier() {
        return 'Proxy';
    }
}