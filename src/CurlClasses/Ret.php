<?php
namespace Sooh\CurlClasses;

class Ret {

    public $httpCode;
    public $newCookies=array();
    //public $newHeaders;
    public $body;
    public $error;
    
    public static function facotryByRequest($ch)
    {
        $o = new Ret;
        $tmp = curl_exec($ch);
        $o->error = curl_error($ch);
        $o->httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $posEnd = strpos($tmp, "\r\n\r\n",strpos($tmp, "HTTP/1.1 200"));
        $headerReceived = substr($tmp, 0,$posEnd);
        
        $m = null;
        preg_match_all('/^Set-Cookie: (.*?);/m',$headerReceived,$m);
        foreach($m[1] as $s){
            $posEq = strpos($s, '=');
            $o->newCookies [ substr($s, 0,$posEq) ] = substr($s, $posEq+1);
        }
        $o->body = substr($tmp, $posEnd+4);
        
        return $o;
    }

    public function strResult()
    {
        return $this->body.$this->error;
    }
}

