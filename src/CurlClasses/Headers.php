<?php
namespace Sooh\CurlClasses;
/* 
 * Headers 处理
 */
class Headers implements Addons{
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
        if(is_string($arr)){
            $arr = array($arr);
        }
        foreach($arr as $s){
            $pos = strpos($s, ':');
            $this->_disposables[trim(substr($s,0,$pos))]=$s;
        }
    }
    
    public function onSetOpt($ch)
    {
        $tmp = array_merge($this->_original,$this->_disposables);
        if(sizeof($tmp)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $tmp);
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
        return 'Headers';
    }    
}
