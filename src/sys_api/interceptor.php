<?php


class  AccessAllow  extends XInterceptor
{

    public function _after($xcontext,$request,$response)
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    }
}


