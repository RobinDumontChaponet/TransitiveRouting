<?php

declare(strict_types=1);

use Transitive\Routing\Route;
use Transitive\Core\Presenter;
use Transitive\Simple\View;

final class RouteTest extends PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $presenter = new Presenter();
        $view = new View();
        $instance = new Route($presenter, $view);

        $this->assertEquals(
            $instance->presenter,
            $presenter
        );
        $this->assertEquals(
            $instance->view,
            $view
        );
    }
}
