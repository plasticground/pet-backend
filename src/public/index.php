<?php

require __DIR__.'/../app/interfaces/DatabaseInterface.php';
require __DIR__.'/../app/services/DatabaseService.php';
require __DIR__.'/../app/traits/HasAttributes.php';
require __DIR__.'/../app/models/Model.php';
require __DIR__.'/../app/models/Pet.php';

header('Content-type: application/json; charset=utf-8');

$url = parse_url($_SERVER['REQUEST_URI']);
$path = trim($url['path'], '/');

switch ($path) {
    case 'ping':
        response(['message' => 'Pong!']);
        break;
    case 'createTables':
        createTables();
        break;
    case 'dropTables':
        dropTables();
        break;
    case 'showTables':
        showTables();
        break;
    case 'showTable':
        showTable();
        break;
    case 'createPet':
        createPet();
        break;
    case 'getPet':
        getPet();
        break;
    case 'updatePet':
        updatePet();
        break;
    default:
        abort();
        break;
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

function showTable() {
    $table = $_GET['table'] ?? null;

    if ($table) {
        $db = new \App\Services\DatabaseService();

        response($db->select($table));
    } else {
        abort(422);
    }
}

function showTables() {
    $db = new \App\Services\DatabaseService();

    response($db->showTables());
}

function createTables() {
    $db = new \App\Services\DatabaseService();

    response($db->createTables());
}

function dropTables() {
    $db = new \App\Services\DatabaseService();

    response($db->dropTables());
}

function createPet() {
    $name = $_GET['name'] ?? null;

    if ($name) {
        $pet = new \App\Models\Pet(['name' => $name]);

        response($pet->create());
    } else {
        abort(422);
    }
}

function getPet() {
    $id = $_GET['id'] ?? null;

    if ($id) {
        response(\App\Models\Pet::find($id));
    } else {
        abort(404);
    }
}

function updatePet() {
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