<?php

namespace Transitive\Routing;

class ListRouter implements Router
{
    protected ?string $prefix = null;
    protected ?string $defaultViewClassName = null;

    public function __construct(
        public array $routes = [],
        protected array $exposedVariables = [],
    ) {}

    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @param Route[] $routes
     */
    public function setRoutes(array $routes): void
    {
        $this->routes = $routes;
    }

    public function addRoute(string $pattern, Route $route, string $method = 'all'): void
    {
        if(isset($this->routes[$pattern]))
            if(!is_array($this->routes[$pattern]))
                $this->routes[$pattern] = array('all' => $this->routes[$pattern]);

        $this->routes[$pattern][$method] = $route;
    }

    public function removeRoute(string $pattern, string $method = 'all'): void
    {
        if(isset($this->routes[$pattern])) {
            if(!is_array($this->routes[$pattern]))
                unset($this->routes[$pattern]);
            elseif(isset($this->routes[$pattern][$method]))
                unset($this->routes[$pattern][$method]);
        }
    }

    public function execute(string $pattern, string $method = 'all'): ?Route
    {
        $pattern = rtrim($pattern, '/');
        $route = null;

        if(isset($this->routes[$pattern]))
            if(!is_array($this->routes[$pattern]))
                $route = $this->routes[$pattern];
            elseif(isset($this->routes[$pattern][$method]))
                $route = $this->routes[$pattern][$method];

        if($route) {
            if(!$route->hasExposedVariables())
                $route->setExposedVariables($this->exposedVariables);
            if(!$route->hasPrefix())
                $route->setPrefix($this->prefix);
            if(!$route->hasDefaultViewClassName())
                $route->setDefaultViewClassName($this->defaultViewClassName);
        }

        return $route;
    }

    public function setExposedVariables(array $exposedVariables = []): void
    {
        $this->exposedVariables = $exposedVariables;
    }

    public function setPrefix(string $prefix = null): void
    {
        $this->prefix = $prefix;
    }

    public function setDefaultViewClassName(string $defaultViewClassName = null): void
    {
        $this->defaultViewClassName = $defaultViewClassName;
    }

    public function hasDefaultViewClassName(): bool
    {
        return !empty($this->defaultViewClassName);
    }
}
