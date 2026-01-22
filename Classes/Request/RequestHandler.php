<?php
declare(strict_types=1);
namespace SchamsNet\AwsIamSignedRequests\Request;

use Aws\Credentials\Credentials;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class RequestHandler
{
    private string $baseUri;
    private Client $client;

    /**
     * Constructor
     */
    public function __construct($baseUri)
    {
        // Base URI
        $this->baseUri = $baseUri;

        // GuzzleHttp\Client
        $this->client = new Client([

            // Total timeout of the request in seconds. Use 0 to wait indefinitely (the default behavior).
            'timeout' => 10,

            // Disable redirects.
            'allow_redirects' => false,

            // Disable throwing exceptions on an HTTP protocol errors (i.e., 4xx and 5xx responses).
            'http_errors' => false,

        ]);
    }

    /**
     * Send an unsigned POST request to AWS
     */
    public function unsignedPost(string $object, string $mimeType, ?string $apiKey): Response
    {
        $headers = ["Content-type" => $mimeType];

        if ($apiKey) {
            $headers["X-API-Key"] = $apiKey;
        }

        // Utilize PSR-7 as the HTTP message interface
        $request = new Request('POST', $this->baseUri, $headers, $object);

        // Send the request and return a GuzzleHttp\Psr7\Response
        return $this->client->send($request);
    }

    /**
     * Sign the request before sending to AWS
     * Note: This requires a valid AWS IAM user with access key ID and secret access key, and user permissions to execute the API
     */
    public function signedPost(string $object, string $mimeType, ?string $apiKey, string $awsRegion, string $awsAccessKeyId, string $awsSecretAccessKey): Response
    {
        $headers = ["Content-type" => $mimeType];

        if ($apiKey) {
            $headers["X-API-Key"] = $apiKey;
        }

        // Utilize PSR-7 as the HTTP message interface
        $request = new Request('POST', $this->baseUri, $headers, $object);

        // Sign the request for the API Gateway (AWS Signature Version 4)
        $signature = new SignatureV4('execute-api', $awsRegion);
        $credentials = new Credentials($awsAccessKeyId, $awsSecretAccessKey);
        $signedRequest = $signature->signRequest($request, $credentials);

        // Send the signed request and return a GuzzleHttp\Psr7\Response
        return $this->client->send($signedRequest);
    }

}
