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
        $qConfs  = static::getSubscibes();
        $start   = 0;
        foreach($qConfs as $conf){
            $max = hexdec($conf['max']);
            if($hVal > $start && $hVal < $max){
                return $conf['addr'];
            }
            $start = $max;
        }
        return null;
    }

    static public function getSubscibes()
    {
        $confObj = XConfLoader::load(XConfLoader::ENV);
        return $confObj->xpath("/hydra/subscibes");
    }

    static public function getCollectors()
    {
        $confObj = XConfLoader::load(XConfLoader::ENV);
        return $confObj->xpath("/hydra/collectors");
    }

    static public function getEvents()
    {
        $confObj = XConfLoader::load(XConfLoader::ENV);
        return $confObj->xpath("/hydra/events");
    }
}
