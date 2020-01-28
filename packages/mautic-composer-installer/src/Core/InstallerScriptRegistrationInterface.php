<?php

namespace Mautic\Mautic\Composer\Core;


use Composer\Script\Event;

interface InstallerScriptRegistrationInterface
{
    public static function register(Event $event, ScriptDispatcher $scriptDispatcher);
}
