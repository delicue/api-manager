<?php

use App\Database as DB;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../functions.php';

// Start the session
session_start();

// Configure the database connection
DB::config('sqlite:' . __DIR__ . '/../data/database.sqlite');

// create users table if it doesn't exist
DB::getInstance()->createTable('users', [
    'id INTEGER PRIMARY KEY AUTOINCREMENT',
    'username TEXT NOT NULL',
    'password TEXT NOT NULL'
]);
// create posts table if it doesn't exist
DB::getInstance()->createTable('posts', [
    'id INTEGER PRIMARY KEY AUTOINCREMENT',
    'title TEXT NOT NULL',
    'content TEXT NOT NULL'                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     
]);
// create api_keys table if it doesn't exist
DB::getInstance()->createTable('api_keys', [
    'id INTEGER PRIMARY KEY AUTOINCREMENT',
    'api_key TEXT NOT NULL UNIQUE'
]);

app([
    'rate-limiter',
    'routes',
    'middleware',
]);
