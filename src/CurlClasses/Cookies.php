<?php

namespace Sooh\CurlClasses;

/** 
 * Cookie管理
 */

class Cookies  implements Addons{
    protected $_original=array();
    protected $_disposables=array();
    public function __construct($cookieOriginal=array()) {
        if(!is_array($cookieOriginal)){
            throw new \ErrorException('arg for CurlClasses\Cookies should be array, given: '. var_export($cookieOriginal,true));
        }
        $this->_original = $cookieOriginal;
    }

    public function disposables($arr)
    {
        if(!is_array($arr)){
            throw new \ErrorException('arg for CurlClasses\Cookies should be array, given: '. var_export($arr,true));
        }
        if(sizeof($this->_disposables)==0){
            $this->_disposables = $arr;
        }else{
            $this->_disposables = array_merge($this->_disposables,$arr);
        }
    }
    
    public function onSetOpt($ch)
    {
        $tmp = array_merge($this->_original,$this->_disposables);
        
        if(sizeof($tmp)){
            curl_setopt($ch, CURLOPT_COOKIE, str_replace('&', ';', http_build_query($tmp)));
        }
    }

    public function free($all=true)
    {
        $this->_disposables = array();
        if($all){
            $this->_original=array();
        }
    }
    
    final public function getIdentifier() {
        return 'Cookies';
    }
}

