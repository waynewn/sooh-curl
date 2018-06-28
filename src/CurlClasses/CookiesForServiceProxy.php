<?php
namespace Sooh\CurlClasses;
/**
 * 为了配合 ServiceProxy, 需要构造函数时要记录原始所有的cookie，并在每次请求前修正cookie
 * signKey 给空串，表示不用验签
 * 
 * 获取uid的url有格式要求,返回的json格式的数据中，根节点要有跟cookie里相同名称的节点，值分别对应uid和路由设置(默认default)
 */
class CookiesForServiceProxy extends \Sooh\CurlClasses\Cookies
{
    protected $_signKey;
    protected $_fieldSign;
    protected $_fieldSession;
    protected $_fieldUid;
    protected $_fieldRouteChose;
    protected $_urlForGetUidFormSession;
    protected $_fieldDt;
    protected $_fieldReqSN;
    protected $_reqSNOriginal;
    protected $_reqSnCounter=1;
    /**
     * 作者另一个ServiceProxy项目需要的额外设置
     * @return CookiesSupportProxy
     */
    public function initMoreForServiceProxy($signKey, $fieldSign,$fieldSession,$fieldUid,$fieldRouteChose,$urlForGetUidFromSession,$fieldDt,$fieldReqSN)
    {
        $this->_signKey = $signKey;
        $this->_fieldSign = $fieldSign;
        $this->_fieldSession = $fieldSession;
        $this->_fieldUid = $fieldUid;
        $this->_fieldRouteChose = $fieldRouteChose;
        $this->_urlForGetUidFormSession = $urlForGetUidFromSession;
        $this->_fieldDt = $fieldDt;
        $this->_fieldReqSN = $fieldReqSN;
        if($this->checkSign()){
            if(empty($this->_original[$this->_fieldReqSN])){
                $this->_reqSNOriginal=md5(gethostname().'-'. getmypid().'-'. microtime(true).'-'.rand(100000,999999));
            }else{
                $this->_reqSNOriginal=$this->_original[$this->_fieldReqSN];
            }
            if(empty($this->_original[$this->_fieldUid])){//没有uid
                if(!empty($this->_original[$this->_fieldSession]) && !empty($this->_original[$this->_urlForGetUidFormSession])){
                    $this->fetchUidBySession();
                }
            }
        }else{
            throw new \ErrorException("sign of proxy check failed for curl");
        }
        return $this;
    }
    protected function fetchUidBySession()
    {
        $ch = curl_init();
        if($ch){
            curl_setopt($ch, CURLOPT_URL, $this->_original[$this->_urlForGetUidFormSession].$this->_original[$this->_fieldSession]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1 ); 
            $tmp = curl_exec($ch);
            curl_close($ch);
            $arr = json_decode($tmp,true);
            if(is_array($arr)){
                $this->_original[$this->_fieldUid] = $arr[$this->_fieldUid];
                $this->_original[$this->_fieldRouteChose] = $arr[$this->_fieldRouteChose];
            }
        }
    }
    
    public function onSetOpt($ch)
    {
        $this->_disposables[$this->_fieldReqSN] = $this->_reqSNOriginal.'_'.$this->_reqSnCounter;
        $this->_reqSnCounter++;
        parent::onSetOpt($ch);
    }
    public function getTimestampStart()
    {
        return isset($this->_original[$this->_fieldDt])?$this->_original[$this->_fieldDt]:0;
    }
    public function getRequestSN()
    {
        return $this->_reqSNOriginal;
    }
    
    // ----------------------------------------------------签名相关
    protected function checkSign()
    {
        if(empty($this->_signKey)){
            return true;
        }
        if(empty($this->_original[$this->_fieldSign])){
            return false;
        }
        $func = "checkSign".substr($this->_original[$this->_fieldSign],0,1);
        return $this->$func();
        
    }
    protected function checkSign1()
    {
        $sign = $this->_original[$this->_fieldSign];
        $i = substr($sign,1,2);
        $k = substr($sign,-2);
        $chk = substr($sign,3,-2);
        return md5($i.$this->_signKey.$k)==$chk;
    }
    protected function sign1()
    {
        $i = rand(10,99);
        $k = rand(10,99);
        $sign = md5($i.self::$defined['ServcieProxySignkey'].$k);
        return "1".$i.$sign.$k;
    }
}
