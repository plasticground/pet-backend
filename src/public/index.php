<?php

header('Content-type: application/json; charset=utf-8');

$url = parse_url($_SERVER['REQUEST_URI'])['path'];

switch ($url) {
    case '/ping':
        response(['message' => 'Pong!']);
        break;
    default:
        abort();
        break;
}

function abort(int $code = 404)
{
    $message = match ($code) {
        404 => 'Not found',
        500 => 'Server error',
        default => null
    };

    response($message, $code);
}

function response($data = null, int $code = 200)
{
    http_response_code($code);

    if ($data) {
        print json_encode($data);
    }

    exit(0);
}