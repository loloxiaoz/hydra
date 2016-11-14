<?php
interface HydraStat
{
    public function stat($name,$data="");

}
class EmptyStat implements HydraStat
{
    public function stat($name,$data="") {}
}

class HydraSetting
{
    static $settings = array() ;
    static $isInit   = false ;
    public static function init()
    {
        static::$settings['stat'] = new EmptyStat();
        static::$isInit = true ;
    }
    public static function __callStatic($name,$args)
    {
        if(! static::$isInit)
        {
            static::init();
        }
        if( strlen($name) < 5 )
        {
            throw new LogicException("HydraSetting::$name is not allowed") ;
        }
        $op = substr($name,0,4) ;
        $name = substr($name,4)  ;

        if ($op  == "get_")
        {
            return static::$settings[$name] ;
        }
        if ($op == "set_")
        {
            return static::$settings[$name]  = $args[0];
        }

    }

}
