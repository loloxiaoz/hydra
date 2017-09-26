<?php

namespace Hydra;

class ConfLoader
{
    //hash(i) = hash(i-1) * 33 + str[i]
    //(hash << 5) + hash 相当于 hash * 33
    static private function hash($str)
    {
        $hash = 0;
        $s    = md5($str);
        $seed = 5;
        $len  = 32;
        for($i = 0; $i < $len; $i++){
            $hash = ($hash << $seed) + $hash + ord($s{$i});
        }
        return $hash & 0x7FFFFFFF;
    }

    static public function getSubConf($topic)
    {
        $hVal    = static::hash($topic);
        $subs    = static::getSubscribers();
        $start   = 0;
        foreach($subs as $sub){
            $max = hexdec($sub['max']);
            if($hVal > $start && $hVal < $max){
                return $sub['addr'];
            }
            $start = $max;
        }
        return null;
    }

    static public function loadConf()
    {
        static $confs = array();
        if(empty(static::$confs)){
            $confPath = $GLOBALS["PRJ_ROOT"] ."config.json";
            $strs = file_get_contents($confPath);
            $confs = @json_decode($strs,true);
            if(empty($confs)){
                throw new \RuntimeException("config file is bad");
            }
        }
        return static::$confs;
    }

    static public function getSubscribers()
    {
        $confs = static::loadConf();
        return $confs["hydra"]["subscibes"];
    }

    static public function getCollectors()
    {
        $confs = static::loadConf();
        return $confs["hydra"]["collectors"];
    }

    static public function getEvents()
    {
        $confs = static::loadConf();
        return $confs["hydra"]["events"];
    }
}
