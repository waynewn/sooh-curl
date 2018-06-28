# curl 封装

ServiceProxy项目（基于http协议的微服务中间件）需要扩展curl,原版本扩展性较差，因此有了这次的重构

## 基本用法


先看两个例子

        $ret = \Sooh\Curl::getInstance()->httpGet('http://www.baidu.com');
        echo "httpCode:".$ret->httpCode."\n";
        echo "content:".$ret->body."\n";
        echo "new cookies set:".json_encode($ret->newCookies)."\n";

稍微复杂些的

        //前期初始化
        $curl = \Sooh\Curl::getInstance(array('SESSIONID'=>'afasdf'),new \Sooh\CurlClasses\Headers(array('some-header: value','other-header'=>'value')));

        //第一次请求，标准post
        $curl->disposables('Cookies',array('CookieTest'=>1))->httpPost('http://1.2.3.4/adsf',array('a'=>1));
        //此次服务器收到的请求，cookie 有两个SESSIONID=afasdf 和 CookieTest=1

        后面第二次请求，rawdata方式提交了个json数据
        $curl->disposables('Cookies',array('CookieAgain'=>2))->httpRawJson('http://1.2.3.4/adsf',array('a'=>1));
        //此次服务器收到的请求，cookie 有两个SESSIONID=afasdf 和 CookieAgain=2

### 初始化 及 设置

static function getInstance() 肩负初始化和获取实例的双重责任

初始化时会登记一些addons，比如cookie,此时记录的数据，在释放前全程有效

后面处理业务的时候，可以通过->disposables(addons名称，参数)，
调用指定addone的disposables方法，记录一些一次性的数据，在接下来的请求中用掉

说明：初始化的时候，getInstance()可以接收不定数量的参数，
除了cookie(可以给数组，内部转成Addons),其他都必须是Addons的实例

### 三种请求格式

public function httpGet($url,$params=null,$timeOut = 5)

public function httpPost($url,$params,$timeOut=5)

public function httpRawJson($url, $params,$timeOut=5)

函数名可以说明问题了，这里只说明一下，params 根据具体的函数情况，string 或 array 都支持

返回结果，下面的结构应该不用多解释了

        class Ret {
            public $httpCode;
            public $newCookies=array();
            public $body;
            public $error;
            ……

### addons

目前提供了四个Addons

1. Header的
2. proxy代理设置的
3. 标准Cookie的
4. ServiceProxy项目使用的处理Cookie的

curl 提供了 getAddons() 获取指定的Addons

### 其他

关于资源释放：

curl->freeAddons(false) 释放掉所有addons的disposables数据

curl->freeAddons(true)  连同addons一起，全部释放掉
