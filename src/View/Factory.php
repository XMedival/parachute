<?php

namespace Parachute\View;

use Parachute\Contracts\View\Factory as ViewFactory;
use Parachute\Base\App;

class Factory implements ViewFactory
{
    protected App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function make(string $view, array $data = []): View
    {
        $config = $this->app->get('config');
        $viewsPath = $config->get('views.path', app('base_path') . '/views');
        $viewPath = rtrim($viewsPath, '/') . '/' . str_replace('.', '/', $view) . '.php';

        return new View($viewPath, $data);
    }

    public function exists(string $view): bool
    {
        $config = $this->app->get('config');
        $viewsPath = $config->get('views.path', app('base_path') . '/views');
        $viewPath = rtrim($viewsPath, '/') . '/' . str_replace('.', '/', $view) . '.php';

        return file_exists($viewPath);
    }
}
