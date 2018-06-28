<?php
namespace Sooh\CurlClasses;
/**
 * 为了配合 ServiceProxy, 需要记录原始所有的cookie，并在每次请求前修正cookie
 */
class CookiesSupportProxy extends \Sooh\CurlClasses\Cookies
{
    /**
     * 作者另一个ServiceProxy项目需要的额外设置
     */
    public function initForServiceProxy()
    {
        $tmp = \SingleService\ReqEnvCookie::getInstance()->getCookieArrForServiceProxy();
        //$this->_original[]='';
        return $this;
    }
    public static function init($signKey,$SessionName,$arrMore=null)
    {
        self::$defined=array(
            'ServcieProxySignkey'=>$signKey,
            'CookieNameForSession'=>$SessionName,
            'CookieNameForUserID'=>(is_array($arrMore)?$arrMore['CookieNameForUserID']:'UidSetBySerivceProxy'),
            'CookieNameForExtRouteId'=>(is_array($arrMore)?$arrMore['CookieNameForExtRouteId']:'RouteChoseBySerivceProxy'),
            'CookieNameForDtStart'=>(is_array($arrMore)?$arrMore['CookieNameForDtStart']:'TimeStampOnBegin'),
            'CookieNameForSign'=>(is_array($arrMore)?$arrMore['CookieNameForSign']:'SignForSerivceProxy'),
            'RequestSNTransferByCookie'=>(is_array($arrMore)?$arrMore['RequestSNTransferByCookie']:'ReqSNAddByServiceProxy'),
        );
    }    
}
