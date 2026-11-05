<?php

function view(string $viewPath, array $data = []): void
{
    extract($data);
    require __DIR__ . "/views/{$viewPath}.view.php";
}

function partial(string $partialPath, array $data = []): void
{
    extract($data);
    require __DIR__ . "/views/partials/{$partialPath}.php";
}

function jsonData(string $path)
{
    require __DIR__ . "/data/{$path}.json";
}

/**
 * Check if Laravel Herd (or other platform) has defined this function already.
 * If not, then let it be defined here.
 */
if(!function_exists('dd')) {
    function dd(iterable $data)
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        exit();
    }
}

function app(string | array $path): void
{
    if (is_array($path)) {
        foreach ($path as $p) {
            require __DIR__ . "/src/app/{$p}.php";
        }
        return;
    }
    require __DIR__ . "/src/app/{$path}.php";
}

function redirect(string $url): void
{
    header("Location: {$url}");
    exit();
}

function not_found(): void
{
    http_response_code(404);
    view('errors/404');
    exit();
}