<?php

use App\Helpers\DataHelper;

spl_autoload_register(function ($class) {
    $file = __DIR__
        . DIRECTORY_SEPARATOR
        . '..'
        . DIRECTORY_SEPARATOR
        . str_replace('\\', DIRECTORY_SEPARATOR, $class)
        . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

header('Content-type: application/json; charset=utf-8');

$url = parse_url($_SERVER['REQUEST_URI']);
$path = trim($url['path'], '/');
$query = empty($_GET) ? [] : array_map('trim', $_GET);
$body = json_decode(file_get_contents('php://input') ?: '', true);
$body = empty($body) ? [] : array_map('trim', $body);
$data = new DataHelper([DataHelper::QUERY => $query, DataHelper::BODY => $body]);

try {
    switch ($path) {
        case 'signUp':
        case 'token':
            function_exists($path) ? $path($data) : abort();
            break;
        case 'ping':
            response(['message' => 'Pong!']);
            break;
        default:
            abort();
            break;
    }
} catch (\Throwable $exception) {
    response($exception->getMessage(), 500);//TODO: DEV
    abort(500);
}

function abort(int $code = 404)
{
    $message = match ($code) {
        404 => 'Not found',
        422 => 'Wrong data',
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

function signUp(DataHelper $data)
{
    $username = $data->body('username');
    $password = $data->body('password');

    $errors = \App\Models\User::validateSignUp($username, $password);

    if (!empty($errors)) {
        response(compact('errors'), 422);
    }

    try {
        $token = \App\Models\User::signUp($username, $password);

        response(compact('token'));
    } catch (\App\Exceptions\UserException $exception) {
        response(['errors' => ['request' => $exception->getMessage()]], $exception->getCode());
    }
}

function token(DataHelper $data)
{
    $username = $data->body('username');
    $password = $data->body('password');

    try {
        $token = \App\Models\User::token($username, $password);

        response(compact('token'));
    } catch (\App\Exceptions\UserException $exception) {
        response(['errors' => ['request' => $exception->getMessage()]], $exception->getCode());
    }
}

function showTable()
{
    $table = $_GET['table'] ?? null;

    if ($table) {
        $db = new \App\Services\DatabaseService();

        response($db->select($table));
    } else {
        abort(422);
    }
}

function showTables()
{
    $db = new \App\Services\DatabaseService();

    response($db->showTables());
}

function createTables()
{
    $db = new \App\Services\DatabaseService();

    response($db->createTables());
}

function dropTables()
{
    $db = new \App\Services\DatabaseService();

    response($db->dropTables());
}

function createPet()
{
    $name = $_GET['name'] ?? null;

    if ($name) {
        $pet = new \App\Models\Pet(['name' => $name]);

        response($pet->create());
    } else {
        abort(422);
    }
}

function getPet()
{
    $id = $_GET['id'] ?? null;

    if ($id) {
        response(\App\Models\Pet::find($id));
    } else {
        abort(404);
    }
}

function updatePet()
{
    $id = $_GET['id'] ?? null;
    $attributes = $_GET ?? null;

    if ($id && $attributes) {
        unset($attributes['id']);

        $pet = \App\Models\Pet::find($id);
        $pet->fill($attributes);

        response($pet->update());
    } else {
        abort(422);
    }
}