<?php

namespace Transitive\Routing;

class PathRouter implements Router
{
    private string $presenterSuffix = '.php';
    private string $viewSuffix = '.php';

    private ?string $defaultViewClassName = null;

    public function __construct(
        private string $presentersPath,
        private ?string $viewsPath = null,
        private string $separator = '/',
        public string $method = 'all',
        private ?string $prefix = null,
        private array $exposedVariables = [],
    ) {
        $this->presentersPath .= ('/' != substr($presentersPath, -1)) ? '/' : '';
        $this->viewsPath = $viewsPath ?? $presentersPath;
        $this->viewsPath .= ('/' != substr($this->viewsPath, -1)) ? '/' : '';
    }

    public function execute(string $pattern, string $method = 'all'): ?Route
    {
        if($this->method != $method || empty($pattern))
            return null;

        $presenterPattern = $pattern.$this->presenterSuffix;
        $viewPattern = $pattern.$this->viewSuffix;

        if(false === ($realPresenter = self::_real($presenterPattern, $this->separator)) || false === ($realView = self::_real($viewPattern, $this->separator)))
            return null;

        if($realPresenter && $realView && is_file($this->presentersPath.$realPresenter) || is_file(($this->viewsPath ?? '').$realView))
            return new Route($this->presentersPath.$realPresenter, ($this->viewsPath ?? '').$realView, $this->prefix, $this->exposedVariables, $this->defaultViewClassName);
        else
            return null;
    }

    private static function _real(string $filename, string $separator = '/'): string|false
    {
        $path = [];
        foreach(explode($separator, $filename) as $part) {
            if (empty($part) || '.' === $part)
                continue;

            if ('..' !== $part)
                array_push($path, $part);
            elseif (count($path) > 0)
                array_pop($path);
            else
                return false;
        }

        return implode('/', $path);
    }

    public function getRoutes(): array
    {
        $array = array();

        foreach(array_diff(scandir($this->presentersPath), array('..', '.', '.DS_Store')) as $pattern) {
            if($pattern = substr($pattern, 0, strpos($pattern, $this->presenterSuffix)?:null))
                $array[$pattern] = $this->execute($pattern);
        }

        return $array;
    }

    public function setExposedVariables(array $exposedVariables = []): void
    {
        $this->exposedVariables = $exposedVariables;
    }

    public function setPrefix(?string $prefix = null): void
    {
        $this->prefix = $prefix;
    }

    public function setDefaultViewClassName(?string $defaultViewClassName = null): void
    {
        $this->defaultViewClassName = $defaultViewClassName;
    }

    public function hasDefaultViewClassName(): bool
    {
        return !empty($this->defaultViewClassName);
    }
}
