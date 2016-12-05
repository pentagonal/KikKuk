<?php
namespace KikKuk\SlimOverride;

use Slim\Http\Cookies;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request as SlimHttpRequest;
use Slim\Http\RequestBody;
use Slim\Http\UploadedFile;

/**
 * Class Request
 * @package KikKuk\SlimOverride
 */
class Request extends SlimHttpRequest
{
    /**
     * Create new HTTP request with data extracted from the application
     * Environment object
     *
     * @param  Environment $environment The Slim application Environment
     *
     * @return self
     */
    public static function createFromEnvironment(Environment $environment)
    {
        $method = $environment['REQUEST_METHOD'];
        $uri = Uri::createFromEnvironment($environment);
        $headers = Headers::createFromEnvironment($environment);
        $cookies = Cookies::parseHeader($headers->get('Cookie', []));
        $serverParams = $environment->all();
        $body = new RequestBody();
        $uploadedFiles = UploadedFile::createFromEnvironment($environment);

        $request = new static($method, $uri, $headers, $cookies, $serverParams, $body, $uploadedFiles);

        if ($method === 'POST' &&
            in_array($request->getMediaType(), ['application/x-www-form-urlencoded', 'multipart/form-data'])
        ) {
            // parsed body must be $_POST
            $request = $request->withParsedBody($_POST);
        }
        return $request;
    }
}
