<?php
declare(strict_types=1);
namespace SchamsNet\AwsIamSignedRequests\Core;

use Dotenv\Dotenv;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use League\Flysystem\Filesystem\FilesystemException;
use League\Flysystem\Filesystem\UnableToReadFile;
use SchamsNet\AwsIamSignedRequests\Filesystem\LocalFileSystem;
use SchamsNet\AwsIamSignedRequests\Request\RequestHandler;

class Bootstrap
{
    private array $arguments;
    private string $baseDirectory;

    private ?string $apiKey;
    private ?string $apiEndpointUri;
    private ?string $apiEndpointPath;
    private ?string $awsRegion;
    private ?string $awsAccessKeyId;
    private ?string $awsSecretAccessKey;

    /**
     * Constructor
     */
    public function __construct(array $arguments, string $baseDirectory)
    {
        // Load environment variables (also see .env file)
        $dotenv = Dotenv::createImmutable($baseDirectory);
        $dotenv->safeLoad();
        $this->populateEnvVariables();

        // Import command line arguments
        $this->arguments = $arguments;

        // Set the base directory
        $this->baseDirectory = $baseDirectory;
    }

    /**
     * Populate environment variables
     */
    private function populateEnvVariables()
    {
        $this->apiKey = ($_ENV["API_KEY"] ? $_ENV["API_KEY"] : null);
        $this->apiEndpointUri = ($_ENV["API_ENDPOINT_URI"] ? $_ENV["API_ENDPOINT_URI"] : null);
        $this->apiEndpointPath = ($_ENV["API_ENDPOINT_PATH"] ? $_ENV["API_ENDPOINT_PATH"] : null);
        $this->awsRegion = ($_ENV["API_ENDPOINT_REGION"] ? $_ENV["API_ENDPOINT_REGION"] : null);
        $this->awsAccessKeyId = ($_ENV["AWS_ACCESS_KEY_ID"] ? $_ENV["AWS_ACCESS_KEY_ID"] : null);
        $this->awsSecretAccessKey = ($_ENV["AWS_SECRET_ACCESS_KEY"] ? $_ENV["AWS_SECRET_ACCESS_KEY"] : null);
    }

    /**
     * Run the application
     */
    public function run()
    {
        // Get image file name (first argument)
        $imageFilename = ($this->arguments[1] ? $this->arguments[1] : null);

        // Get mime type of the image
        $mimeType = $this->getMimeType($imageFilename);
        echo "Mime type: " . $mimeType . PHP_EOL;

        // Read the image file
        $file = $this->readFile($imageFilename);
        echo "Uploading " . strlen($file) . " bytes" . PHP_EOL;

        // Send the image to AWS and get the response
        $response = $this->process($file, $mimeType);
        echo "Response status code: " . $response->getStatusCode() . PHP_EOL;
        echo $response->getBody() . PHP_EOL;
    }

    /**
     * Get the mime type of a file, stored in the local file system
     */
    private function getMimeType(string $filename): ?string
    {
        // Get the file system operator and the file system of the base directory
        $localFilesystem = new LocalFileSystem();
        $filesystem = $localFilesystem->getFilesystem($this->baseDirectory);

        try {
            return $filesystem->mimeType($filename);
        } catch (FilesystemException | UnableToReadFile $exception) {
            echo $exception->getMessage() . PHP_EOL;
        }
    }

    /**
     * Read the image file from the local file system
     */
    private function readFile(string $filename): ?string
    {
        // Get the file system operator and the file system of the base directory
        $localFilesystem = new LocalFileSystem();
        $filesystem = $localFilesystem->getFilesystem($this->baseDirectory);

        try {
            return $filesystem->read($filename);
        } catch (FilesystemException | UnableToReadFile $exception) {
            echo $exception->getMessage() . PHP_EOL;
        }
    }

    /**
     * Send the image to AWS and return the PSR-7 response
     */
    private function process(string $file, string $mimeType): Response
    {
        // Instantiate a request handler
        $requestHandler = new RequestHandler($this->apiEndpointUri . $this->apiEndpointPath);

        try {

            // Send an insigned PSR-7 request
            // Note: This request will fail if the API Gateway *requires* AWS_IAM authorization
            //return $requestHandler->unsignedPost($file, $mimeType, $this->apiKey);

            // Send a signed PSR-7 request (AWS Signature Version 4)
            return $requestHandler->signedPost($file, $mimeType, $this->apiKey, $this->awsRegion, $this->awsAccessKeyId, $this->awsSecretAccessKey);

        } catch (ClientException $exception) {
            echo $exception->getMessage() . PHP_EOL;
        }
    }
}
