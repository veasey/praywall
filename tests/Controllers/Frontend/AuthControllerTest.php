<?php

use PHPUnit\Framework\TestCase;
use App\Controllers\Frontend\AuthController;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthControllerTest extends TestCase
{
    private $view;
    private $db;
    private $controller;

    protected function setUp(): void
    {
        $this->view = $this->createMock(Twig::class);
        $this->db = $this->createMock(PDO::class);

        $this->controller = new AuthController($this->view, $this->db);
    }

    public function testShowLoginForm()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $renderedResponse = $this->createMock(ResponseInterface::class);

        $this->view->expects($this->once())
            ->method('render')
            ->with($response, 'frontend/auth/login.twig')
            ->willReturn($renderedResponse);

        $result = $this->controller->showLoginForm($request, $response, []);
        $this->assertSame($renderedResponse, $result);
    }

    public function testLoginWithValidCredentials()
    {
        $_SESSION = []; // Needed for session manipulation

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stmt = $this->createMock(PDOStatement::class);
        $newResponse = $this->createMock(ResponseInterface::class);

        $email = 'user@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $user = ['email' => $email, 'password_hash' => $hashedPassword];

        $request->method('getParsedBody')->willReturn(['email' => $email, 'password' => $password]);

        $this->db->method('prepare')->willReturn($stmt);
        $stmt->expects($this->once())->method('execute')->with([':email' => $email]);
        $stmt->method('fetch')->willReturn($user);

        $response->expects($this->once())
            ->method('withHeader')
            ->with('Location', '/')
            ->willReturn($newResponse);
        $newResponse->expects($this->once())->method('withStatus')->with(302)->willReturnSelf();

        $result = $this->controller->login($request, $response, []);
        $this->assertSame($newResponse, $result);
        $this->assertEquals($user, $_SESSION['user']);
    }

    public function testLoginWithInvalidCredentials()
    {
        $_SESSION = []; // Clear session

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stmt = $this->createMock(PDOStatement::class);
        $newResponse = $this->createMock(ResponseInterface::class);

        $request->method('getParsedBody')->willReturn(['email' => 'baduser@example.com', 'password' => 'wrongpass']);

        $this->db->method('prepare')->willReturn($stmt);
        $stmt->expects($this->once())->method('execute')->with([':email' => 'baduser@example.com']);
        $stmt->method('fetch')->willReturn(false); // No user found

        $response->expects($this->once())
            ->method('withHeader')
            ->with('Location', '/login')
            ->willReturn($newResponse);
        $newResponse->expects($this->once())->method('withStatus')->with(302)->willReturnSelf();

        $result = $this->controller->login($request, $response, []);
        $this->assertSame($newResponse, $result);
        $this->assertArrayNotHasKey('user', $_SESSION);
    }

    public function testLogout()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $newResponse = $this->createMock(ResponseInterface::class);

        $response->expects($this->once())
            ->method('withHeader')
            ->with('Location', '/')
            ->willReturn($newResponse);
        $newResponse->expects($this->once())
            ->method('withStatus')
            ->with(302)
            ->willReturnSelf();

        $result = $this->controller->logout($request, $response, []);
        $this->assertSame($newResponse, $result);
    }
}
