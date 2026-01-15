<?php
declare(strict_types=1);
namespace SchamsNet\AwsIamSignedRequests\Filesystem;

use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Filesystem;

class LocalFileSystem
{

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Get the file system operator
     */
    public function getFilesystem(string $directory): Filesystem
    {
        // The internal adapter
        $adapter = new LocalFilesystemAdapter($directory);

        // Return the file system operator
        return new Filesystem($adapter);
    }
}
