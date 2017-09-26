<?php

namespace Hydra;

interface IConsumer
{
    public function cmd(Cmd $cmd);
    public function consume($topic, $workFun, $stopFun, $timeout=5);
}
