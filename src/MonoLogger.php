<?php

namespace Hydra;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class MonoLogger implements ILogger
{
    private $logger;

    public function __construct($channel,$logPath,$level)
    {
        $this->logger     = new Logger($channel);
        $this->logger->pushHandler(new StreamHandler($logPath, $level));
    }

    public function info($str,$tag='info')
    {
        $this->logger->addInfo($str,array("tag"=>$tag));
    }

    public function debug($str,$tag='debug')
    {
        $this->logger->addInfo($str,array("tag"=>$tag));
    }

    public function warn($str,$tag='warn')
    {
        $this->logger->addWarning($str,array("tag"=>$tag));
    }

    public function error($str,$tag='error')
    {
        $this->logger->addError($str,array("tag"=>$tag));
    }
}
