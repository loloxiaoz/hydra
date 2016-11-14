<?php
class  SentryStat
{
    public function __construct()
    {
        $DSN="http://a846ab3cb69d47e98ca528211d6e7e13:142174c3e27f4e148b9b4e505e33d26a@sentry.mararun.cn/9";
        $this->client = new Raven_Client($DSN);
    }
    public function stat($name,$data="")
    {
        $this->client->captureMessage($name,array($data),array("level" => 'info'));
    }
}
