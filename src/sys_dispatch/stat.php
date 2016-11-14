<?php
class  SentryStat
{
    public function __construct()
    {
        $DSN=getenv('SENTRY_DSN') ;
        $this->client = new Raven_Client($DSN);
    }
    public function stat($name,$data="")
    {
        $this->client->captureMessage($name,array($data),array("level" => 'info'));
    }
}
