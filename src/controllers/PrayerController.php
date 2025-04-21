<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PrayerController
{
    public static function getPosts(Request $request, Response $response, $args)
    {
        include __DIR__ . '/../settings.php';

        $query = $db->query("SELECT * FROM prayers WHERE approved = 1 ORDER BY created_at DESC");
        $posts = $query->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($posts));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
