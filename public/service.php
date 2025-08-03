<?php

if (isset($_SERVER["HTTP_AUTHORIZATION"]) && strlen($_SERVER["HTTP_AUTHORIZATION"]) > 0) {
    $header = $_SERVER["HTTP_AUTHORIZATION"];
    $header = str_replace($header, "Bearer ", "");
    if (strlen($header) > 0) {
        $decoded = base64_decode($header);

        //validate that string is what you wanted.
    } else {
        // bad things happened here, abort, unauthorized, whatever.
    }
}