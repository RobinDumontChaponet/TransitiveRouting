<?php

namespace Transitive\Routing;

interface FrontController
{
    /**
     * Get presenter & view buffer (if obClean is enabled).
     */
    public function getObContent(): string;

    /**
     * Execute routers for query and return route if any.
     */
    public function execute(string $queryURL = null): ?Route;

    /**
     * Get all routers.
     *
     * @return Router[]
     */
    public function getRouters(): array;

    /**
     * Set routers list, replacing any previously set router.
     *
     * @param Router[] $routers
     */
    public function setRouters(array $routers): void;

    /**
     * Add specified router.
     *
     * @param Router $router
     */
    public function addRouter(Router $router): void;

    /**
     * Remove specified router
     * return true at success and false otherwise.
     */
    public function removeRouter(Router $router): bool;

    /**
     * Return current route.
     */
    public function getRoute(): ?Route;

    /**
     * Return processed content from current route.
     */
    public function getContent(string $contentType = null): string;
}
