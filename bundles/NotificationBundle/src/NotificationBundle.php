<?php
namespace corite\NotificationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class NotificationBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

}