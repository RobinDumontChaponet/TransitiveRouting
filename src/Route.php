<?php

namespace Transitive\Routing;

use Transitive\Core;

/**
 * Route class.
 */
class Route
{
    private static function _include($exposedVariables, $_prefix) {
        extract($exposedVariables, (!empty($_prefix)) ? EXTR_PREFIX_ALL : null, $_prefix);
        unset($exposedVariables);

        include ${$_prefix.((!empty($_prefix)) ? '_' : '').'path'};
    }

    private static function includePresenter(string $path, array $exposedVariables = [], string $_prefix = null, bool $obClean = true)
    {
        if($obClean) {
            ob_start();
            ob_clean();
        }

        try {
            self::_include(['path' => $path, 'obClean' => $obClean] + $exposedVariables, $_prefix);
        } catch(Core\BreakFlowException $e) {
            ob_clean();
            throw $e;
        }

        if($obClean)
            return ob_get_clean();
    }

    private static function includeView(string $path, array $exposedVariables = [], string $_prefix = null, bool $obClean = true)
    {
        if($obClean) {
            ob_start();
            ob_clean();
        }

        self::_include(['path' => $path, 'obClean' => $obClean] + $exposedVariables, $_prefix);

        if($obClean)
            return ob_get_clean();
    }

    public function execute(bool $obClean = true)
    {
        $obContent = '';

        // Presenter
        $presenter = $this->getPresenter();

        if(is_string($presenter)) {
            if(is_file($presenter)) {
                $presenter = new Core\Presenter();

                try {
                    $obContent .= self::includePresenter($this->getPresenter(), $this->exposedVariables + ['presenter' => $presenter], $this->prefix, $obClean);
                } catch(Core\BreakFlowException $e) {
                    $this->setView();

                    throw $e;
                }

                $this->setPresenter($presenter);
            } else {
                $this->setView();
                throw new RoutingException('Presenter not found', 404);
            }
        }

        // View
        $view = $this->getView();

        if(is_string($view)) {
            if(empty($this->defaultViewClassName))
                $view = new Core\BasicView();
            else
                $view = new $this->defaultViewClassName();
            $view->content = '';

            if(is_file($this->getView())) {
                $obContent .= self::includeView($this->getView(), ['view' => &$view], $this->prefix, $obClean);
            } else {
                $this->setView($view);
                throw new RoutingException('View not found', 404);
            }

            $this->setView($view);
        }

        if($this->hasPresenter() && $this->hasView() && $this->presenter->hasData())
            $this->view->setData($this->presenter->getData());

        return $obContent;
    }

    public function __construct($presenter, $view = null, string $prefix = null, array $exposedVariables = [], string $defaultViewClassName = null)
    {
        $this->presenter = $presenter;
        $this->view = $view;
        $this->prefix = $prefix;
        $this->exposedVariables = $exposedVariables;
        $this->setDefaultViewClassName($defaultViewClassName);
    }

    /**
     * @var string | null : prefix for exposed variables
     */
    private $prefix;

    /**
     * @var string : View's ClassName for when we have specified a path instead of a View instance
     */
    private $defaultViewClassName;

    /**
     * @var Core\Presenter | string
     */
    public $presenter;

    /**
     * @var Core\View | string | null
     */
    public $view;

    private $exposedVariables;

    public function setDefaultViewClassName(?string $defaultViewClassName = '\Transitive\Core\BasicView'): void
    {
        $this->defaultViewClassName = $defaultViewClassName;
    }

    public function hasDefaultViewClassName(): bool
    {
        return !empty($this->defaultViewClassName);
    }

    public function setExposedVariables(array $exposedVariables = []): void
    {
        $this->exposedVariables = $exposedVariables;
    }

    public function hasExposedVariables(): bool
    {
        return !empty($this->exposedVariables);
    }

    public function setPrefix(string $prefix = null): void
    {
        $this->prefix = $prefix;
    }

    public function hasPrefix(): bool
    {
        return !empty($this->prefix);
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function hasPresenter(): bool
    {
        return isset($this->presenter) && $this->presenter instanceof Core\Presenter;
    }

    /**
     * @return Core\Presenter | string
     */
    public function getPresenter()
    {
        return $this->presenter;
    }

    public function setPresenter(Core\Presenter $presenter)
    {
        return $this->presenter = $presenter;
    }

    public function hasView(): bool
    {
        return isset($this->view) && $this->view instanceof Core\View;
    }

    /**
     * @return Core\View | string | null
     */
    public function getView()
    {
        return $this->view;
    }

    public function setView(Core\View $view = null)
    {
        return $this->view = $view;
    }

    /**
     * @param string $key
     */
    public function hasContent(string $key = null): bool
    {
        if(isset($this->view))
            return $this->view->hasContent($key);
    }

    /**
     * @param string $key
     */
    public function getContent(string $key = null)
    {
        if(isset($this->view))
            return $this->view->getContent($key);
    }

    public function getHead(): Core\ViewResource
    {
        if(isset($this->view))
            return $this->view->getHeadValue();
    }

    public function getBody()
    {
        if(isset($this->view))
            return $this->view->getBody();
    }

    public function getDocument()
    {
        if(isset($this->view))
            return $this->view->getDocument();
    }
}
