<?php
namespace Sooh\CurlClasses;
interface Addons {
    /**
     * 设置curl参数
     * @param type $ch
     */
    public function onSetOpt($ch);
    /**
     * 一次性参数设置
     */
    public function disposables($arg);
    
    /**
     * 释放资源
     * $all = true 时，包括初始化的配置一并释放
     */
    public function free($all=true);
    
    /**
     * 获取用于管理的唯一标识，比如 cookies, headers
     */
    public function getIdentifier();
}
