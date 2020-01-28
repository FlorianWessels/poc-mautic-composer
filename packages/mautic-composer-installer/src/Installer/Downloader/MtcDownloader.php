<?php

namespace Mautic\Mautic\Composer\Installer\Downloader;


use Composer\Downloader\ArchiveDownloader;
use Composer\Downloader\ChangeReportInterface;

class MtcDownloader extends ArchiveDownloader implements ChangeReportInterface
{
    protected function extract($file, $path)
    {
        $fileContentStream = file_get_contents($file);

        // TODO: Implement extract() method.
    }
}
