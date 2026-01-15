# AWS IAM Signed Requests Prototype

## Summary

This prototype demonstrates how to sign a request ([AWS Signature Version 4](https://docs.aws.amazon.com/IAM/latest/UserGuide/reference_sigv.html)) for the Amazon API Gateway endpoint using the AWS SDK for PHP.

## Installation

Clone the Git repository and change into the project directory. Install dependent packages using [Composer](https://getcomposer.org/):

```bash
composer install
```

Copy an image file (for example `image.png`) into the project directory.

## Configuration

Create a copy of the `.env.example` file.

```bash
cp .env.example .env
```

Open the `.env` file and provide details for each variable:

- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `API_KEY`
- `API_ENDPOINT_URI`
- `API_ENDPOINT_REGION`

All values are required and depedent on the configuration made at AWS.

## Run the Application

Execute the PHP script and pass the image file name (for example `image.png`) as the first and only argument.

```bash
php run.php image.png
```

The output could look as follows.

```text
SchamsNet\AwsIamSignedRequests\Core\Bootstrap::run
Mime type: image/png
Uploading 151799 bytes
Response status code: 200
{ ... }
```

The last line shows the extracted data.

## Technical Background

### PHP Libraries

The application leverages the following four main PHP libraries as Composer packages:

- [vlucas/phpdotenv](https://packagist.org/packages/vlucas/phpdotenv)
- [league/flysystem](https://packagist.org/packages/league/flysystem)
- [guzzlehttp/guzzle](https://packagist.org/packages/guzzlehttp/guzzle)
- [aws/aws-sdk-php](https://packagist.org/packages/aws/aws-sdk-php)

**PHP dotenv** loads environment variables, for example from `.env`, and exposes them as `$_ENV` variables in the application.

**Flysystem** is a well-known file storage abstraction for PHP. It provides one interface to interact with many types of file systems. Although the current version of the application only uses the local file system through Flysystem, the abstraction makes it easy to load files from other storages such as FTP, Amazon S3, etc. in the future. See <https://flysystem.thephpleague.com/docs/> for further details.

The **Guzzle** library is a PHP HTTP client that makes it easy to send HTTP requests and trivial to integrate with web services. It uses the official PSR-7 interfaces for requests, responses, and streams. See <https://docs.guzzlephp.org/en/stable/> for further details.

The **AWS SDK for PHP** allows us to access Amazon Web Services in PHP code, and build robust applications and software using services like Amazon S3, Amazon DynamoDB, Amazon Glacier, etc. See <https://docs.aws.amazon.com/aws-sdk-php/v3/api/> for further details.

### Flow

1. The PHP script `run.php` initiates the bootstrap process and launches the application.
2. The constructor of the `Bootstrap` class loads the environment variables and other configuration data.
3. The function `run()` loads the image file, determined the file's mime type, and executes the `process()` function.
4. The function `process()` instantiates a request handler and calls the `signedPost()` function.
5. The constructor of the `RequestHandler` class creates the `GuzzleHttp\Client` with some basic configuration.
6. The functions `unsignedPost()` and `signedPost()` prepare the request, set the content type, and add the API key.
7. The function `signedPost()` signs the request (AWS Signature Version 4), and both functions execute the request.
8. Both functions return the PSR-7 compatible response.
9. The function `run()` of the `Bootstrap` class outputs the response status code and body.

### Request Signing

The followings line in the method `signedPost()` of the `RequestHandler` class use functions of the AWS SDK for PHP to sign the request for the API Gateway endpoint in a specific region:

```php
// Sign the request for the API Gateway (AWS Signature Version 4)
$signature = new SignatureV4('execute-api', $awsRegion);
$credentials = new Credentials($awsAccessKeyId, $awsSecretAccessKey);
$signedRequest = $signature->signRequest($request, $credentials);
```
