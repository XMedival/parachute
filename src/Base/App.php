<?php

namespace Parachute\Base;

use Parachute\Base\Router;
use Parachute\Http\Request;
use Parachute\Http\Response;
use Parachute\Support\Config;
use Parachute\Support\Container;
use Parachute\Support\Env;
use Parachute\View\Factory as ViewFactory;
use Parachute\Database\DatabaseManager;
use Parachute\Queue\Queue;

class App extends Container
{
    protected static ?App $instance = null;

    public function __construct(string $basePath = '', string $routesPath = '', string $configPath = '')
    {
        static::$instance = $this;

        Env::load($basePath);

        $router = new Router();
        $this->instance('router', $router);

        $routesPath = $routesPath ?: $basePath . '/routes';
        if (is_dir($routesPath)) {
            foreach (glob($routesPath . '/*.php') as $routeFile) {
                require_once $routeFile;
            }
        } elseif (is_file($routesPath)) {
            require_once $routesPath;
        }

        $configPath = $configPath ?: $basePath . '/config';
        $config = new Config($configPath);
        $this->instance('config', $config);

        $dbConfig = $this->get('config')->get('database');
        $dbManager = new DatabaseManager($dbConfig);
        $this->instance('db', $dbManager);

        $queueConfig = $this->get('config')->get('queue', []);
        $this->instance('queue', new Queue($queueConfig));

        $viewFactory = new ViewFactory($this);
        $this->instance('view', $viewFactory);

        $this->instance('base_path', $basePath);
    }

    public static function getInstance(): ?App
    {
        return static::$instance;
    }

    public function run()
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            $request = Request::capture();
            $response = $this->get('router')->dispatch($request);
        } catch (\Throwable $e) {
            $response = $this->handleException($e);
        }
        $response->send();
    }

    // NOTE: THIS SHOULD BE protected
    public function handleException(\Throwable $e)
    {
        $debug = config('app.debug', false);
        $code = $e->getCode() ?: 500;
        $viewFactory = $this->get('view');

        if ($viewFactory->exists("status.{$code}")) {
            $html = $viewFactory->make("status.{$code}", [
                'e' => $debug ? $e : null,
                'code' => $code,
            ])->render();
            return new Response($html, $code);
        }

        // Fall back to debug or generic
        if ($debug) {
            return $this->renderDebugError($e);
        }

        return new Response('<style>
          body { font-family: system-ui, sans-serif; padding: 2rem; background: #1a1a2e; color: #eee; }
          h1 { color: #ff6b6b; }
          .trace { background: #16213e; padding: 1rem; overflow-x: auto; font-family: monospace; font-size: 0.9rem; }
          .file { color: #4ecdc4; }
          .line { color: #ffe66d; }
      </style><h1>Something went wrong</h1><h1>Error code: ' . $code . '</h1>', $code);
    }

    protected function renderDebugError(\Throwable $e): Response
    {
        $html = '<style>
          body { font-family: system-ui, sans-serif; padding: 2rem; background: #1a1a2e; color: #eee; }
          h1 { color: #ff6b6b; }
          .trace { background: #16213e; padding: 1rem; overflow-x: auto; font-family: monospace; font-size: 0.9rem; }
          .file { color: #4ecdc4; }
          .line { color: #ffe66d; }
      </style>';

        $html .= '<h1>' . htmlspecialchars(get_class($e)) . '</h1>';
        $html .= '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        $html .= '<p class="file">' . htmlspecialchars($e->getFile()) . ':<span class="line">' . $e->getLine() . '</span></p>';
        $html .= '<pre class="trace">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        return new Response($html, 500);
    }
}
