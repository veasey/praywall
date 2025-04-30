<?php

use PHPUnit\Framework\TestCase;
use App\Controllers\Frontend\PrayerController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use PDO;
use PDOStatement;

class PrayerControllerTest extends TestCase
{
    private $twig;
    private $db;
    private $controller;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Twig::class);
        $this->db = $this->createMock(PDO::class);
        $this->controller = new PrayerController($this->twig, $this->db);
    }

    public function testListPrayersRendersViewWithPrayers()
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetchAll')->willReturn([
            ['id' => 1, 'title' => 'Peace', 'body' => 'Pray for peace', 'approved' => true]
        ]);

        $this->db->method('query')->willReturn($stmt);

        $response = $this->createMock(ResponseInterface::class);
        $this->twig->expects($this->once())
            ->method('render')
            ->with($response, 'frontend/prayers/view.twig', $this->arrayHasKey('prayers'))
            ->willReturn($response);

        $request = $this->createMock(ServerRequestInterface::class);

        $result = $this->controller->listPrayers($request, $response, []);
        $this->assertSame($response, $result);
    }

    public function testPrayerRequestRendersFormOnGet()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $response = $this->createMock(ResponseInterface::class);
        $this->twig->expects($this->once())
            ->method('render')
            ->with($response, 'frontend/prayers/request.twig')
            ->willReturn($response);

        $result = $this->controller->prayerRequest($request, $response, []);
        $this->assertSame($response, $result);
    }

    public function testPrayerRequestHandlesPost()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getParsedBody')->willReturn([
            'title' => 'Hope',
            'body' => 'Pray for hope'
        ]);

        $stmt = $this->createMock(PDOStatement::class);
        $this->db->method('prepare')->willReturn($stmt);
        $stmt->expects($this->once())->method('execute');

        $response = $this->createMock(ResponseInterface::class);
        $this->twig->expects($this->once())
            ->method('render')
            ->with($response, 'frontend/prayers/request_success.twig', $this->arrayHasKey('message'))
            ->willReturn($response);

        $result = $this->controller->prayerRequest($request, $response, []);
        $this->assertSame($response, $result);
    }

    public function testApprovePrayerRedirects()
    {
        $stmt = $this->createMock(PDOStatement::class);
        $this->db->method('prepare')->willReturn($stmt);
        $stmt->expects($this->once())->method('execute')->with([':id' => 123]);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('withHeader')->with('Location', '/prayers')->willReturnSelf();
        $response->expects($this->once())
            ->method('withStatus')->with(302)->willReturnSelf();

        $request = $this->createMock(ServerRequestInterface::class);

        $result = $this->controller->approvePrayer($request, $response, ['id' => 123]);
        $this->assertSame($response, $result);
    }

    public function testDeletePrayerRedirects()
    {
        $stmt = $this->createMock(PDOStatement::class);
        $this->db->method('prepare')->willReturn($stmt);
        $stmt->expects($this->once())->method('execute')->with([':id' => 456]);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('withHeader')->with('Location', '/prayers')->willReturnSelf();
        $response->expects($this->once())
            ->method('withStatus')->with(302)->willReturnSelf();

        $request = $this->createMock(ServerRequestInterface::class);

        $result = $this->controller->deletePrayer($request, $response, ['id' => 456]);
        $this->assertSame($response, $result);
    }
}
