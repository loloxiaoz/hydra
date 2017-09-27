<?php

namespace Hydra;

interface IConsumer
{
    public function consume(MsgDTO $dto);
    public function needStop($job);
}
