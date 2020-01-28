<?php

namespace Mautic\Mautic\Composer\Core;


use Composer\Script\Event;

interface InstallerScriptInterface
{
    public function execute(Event $event): bool;
}
