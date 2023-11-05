<?php

header('Content-type: application/json; charset=utf-8');

function response(array $data, int $code = 200) {
    http_response_code($code);
    print json_encode($data);
    exit(1);
}