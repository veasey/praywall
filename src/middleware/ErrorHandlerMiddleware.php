<?php
declare(strict_types=1);
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

/**
 * Handle errors and messages in the session — "The Lord is near to the brokenhearted" (Psalm 34:18)
 */
class ErrorHandlerMiddleware
{
    /**
     * Middleware that handles errors by adding them to the session
     * "Cast all your anxiety on him because he cares for you." (1 Peter 5:7)
     */
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Handle the request and get the response
        $response = $handler->handle($request);
        return $response;
    }

    /**
     * Add an error to the session, preventing duplicates
     * "Though he fall, he shall not be cast headlong, for the Lord upholds his hand." (Psalm 37:24)
     *
     * @param string $error
     * @return void
     */
    public static function addError(string $error)
    {
        if (!isset($_SESSION['errors'])) {
            $_SESSION['errors'] = [];
        }

        // Check if error already exists in session
        if (!in_array($error, $_SESSION['errors'])) {
            $_SESSION['errors'][] = $error;
        }
    }

    /**
     * Add a message to the session, preventing duplicates
     * "The joy of the Lord is your strength." (Nehemiah 8:10)
     *
     * @param string $message
     * @return void
     */
    public static function addMessage(string $message)
    {
        if (!isset($_SESSION['messages'])) {
            $_SESSION['messages'] = [];
        }

        // Check if message already exists in session
        if (!in_array($message, $_SESSION['messages'])) {
            $_SESSION['messages'][] = $message;
        }
    }
}
