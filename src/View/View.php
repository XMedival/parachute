<?php

namespace Parachute\View;

use Parachute\Contracts\View\View as ViewContract;

class View implements ViewContract
{
    protected string $name;
    protected string $viewPath;
    protected array $data = [];

    public function __construct(string $viewPath, array $data = [])
    {
        $this->viewPath = $viewPath;
        $this->name = pathinfo($viewPath, PATHINFO_FILENAME);
        $this->data = $data;
    }

    public function render(): string
    {
        if (!file_exists($this->viewPath)) {
            throw new \Exception("View file not found: {$this->viewPath}");
        }

        extract($this->data);

        ob_start();
        include $this->viewPath;
        return ob_get_clean();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function with(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
