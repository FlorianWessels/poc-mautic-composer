<?php

if (!getenv('MAUTIC_PATH_ROOT')) {
    putenv('MAUTIC_PATH_ROOT= ' . '{$root-dir}');
    $_ENV['MAUTIC_PATH_ROOT'] = '{$root-dir}';
}
// '{$composer-mode}'
