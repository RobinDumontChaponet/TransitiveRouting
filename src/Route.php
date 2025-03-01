<?php

namespace Transitive\Routing;

use DomainException;
use Transitive\Core;

/**
 * Route class.
 */
class Route
{
    const defaultViewClassName = '\Transitive\Simple\View';

    public function __construct(
        public Core\Presenter|string $presenter,
        public Core\View|string|null $view = null,
        /**
         * prefix for exposed variables
         */
        private ?string $prefix = null,
        private array $exposedVariables = [],
        /**
         * View's ClassName for when we have specified a path instead of a View instance
         */
        private ?string $defaultViewClassName = null,
    ){
        $this->setDefaultViewClassName($defaultViewClassName);
    }

    private static function _include(array $exposedVariables, ?string $_prefix = ''): void
    {
        extract($exposedVariables, (!empty($_prefix)) ? EXTR_PREFIX_ALL : EXTR_OVERWRITE, $_prefix ?? '');
        unset($exposedVariables);

        /** @psalm-suppress UnresolvableInclude */
        include ${$_prefix.((!empty($_prefix)) ? '_' : '').'path'};
    }

    private static function includePresenter(string $path, array $exposedVariables = [], ?string $_prefix = null, bool $obClean = true): string
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
            return ob_get_clean()?:'';

        return '';
    }

    private static function includeView(string $path, array $exposedVariables = [], ?string $_prefix = null, bool $obClean = true): string
    {
        if($obClean) {
            ob_start();
            ob_clean();
        }

        self::_include(['path' => $path, 'obClean' => $obClean] + $exposedVariables, $_prefix);

        if($obClean)
            return ob_get_clean()?:'';

        return '';
    }

    public function execute(bool $obClean = true): string
    {
        $obContent = '';

        // Presenter
        $presenter = $this->getPresenter();

        if(is_string($presenter)) {
            if(is_file($presenter)) {
                $presenterObject = new Core\Presenter();

                try {
                    $obContent .= self::includePresenter($presenter, $this->exposedVariables + ['presenter' => $presenterObject], $this->prefix, $obClean);
                } catch(Core\BreakFlowException $e) {
                    $this->setView();

                    throw $e;
                }

                $this->setPresenter($presenterObject);
            } else {
                $this->setView();
                throw new RoutingException('Presenter not found', 404);
            }
        }

        // View
        $view = $this->getView();

        if(is_string($view)) {
            if(empty($this->defaultViewClassName)) {
                /** @psalm-var class-string $className */
                $className = self::defaultViewClassName;
                $viewObject = new $className();
            } else
                $viewObject = new $this->defaultViewClassName();
//             $view->content = '';

            if(is_file($view)) {
                $obContent .= self::includeView($view, ['view' => &$viewObject], $this->prefix, $obClean);
            } else {
                if($viewObject instanceof Core\View)
                    $this->setView($viewObject);
                else
                    throw new RoutingException('Provided view is not a Core\View', 404);

                throw new RoutingException('View not found', 404);
            }

            $this->setView($viewObject);
        }

        // $this->hasPresenter() &&
        if($this->hasView() && $this->presenter instanceof Core\Presenter && $this->presenter->hasData() && $this->view instanceof Core\View)
            $this->view->setData($this->presenter->getData());

        return $obContent;
    }

    public function setDefaultViewClassName(?string $defaultViewClassName = self::defaultViewClassName): void
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

    public function addExposedVariable(string $key, mixed $value = null): void
    {
        $this->exposedVariables[$key] = $value;
    }

    public function removeExposedVariable(string $key): void
    {
        if(isset($this->exposedVariables[$key]))
            unset($this->exposedVariables[$key]);
    }

    public function hasExposedVariables(): bool
    {
        return !empty($this->exposedVariables);
    }

    public function setPrefix(?string $prefix = null): void
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

    // public function hasPresenter(): bool
    // {
    //     return isset($this->presenter) && $this->presenter instanceof Core\Presenter;
    // }

    public function getPresenter(): Core\Presenter|string
    {
        return $this->presenter;
    }

    public function setPresenter(Core\Presenter $presenter): void
    {
        $this->presenter = $presenter;
    }

    public function hasView(): bool
    {
        return isset($this->view) && $this->view instanceof Core\View;
    }

    public function getView(): Core\View|string|null
    {
        return $this->view;
    }

    public function setView(?Core\View $view = null): void
    {
        $this->view = $view;
    }

    public function hasContent(string $contentType = '', string $contentKey = ''): bool
    {
        if(isset($this->view) && $this->view instanceof Core\View)
            return $this->view->hasContent($contentType, $contentKey);

        return false;
    }

    public function getContent(string $contentType = '', string $contentKey = ''): ?Core\ViewResource
    {
        if(isset($this->view) && $this->view instanceof Core\View)
            return $this->view->getContent($contentType, $contentKey);

        return null;
    }

    public function getContentByType(string $contentType = ''): ?Core\ViewResource
    {
        if(isset($this->view) && $this->view instanceof Core\View)
            return $this->view->getContentByType($contentType);

        return null;
    }

    public function getHead(): Core\ViewResource
    {
        if(isset($this->view) && $this->view instanceof Core\View)
            return $this->view->getHead();

        throw new DomainException('No view');
    }

    public function getDocument(): ?Core\ViewResource
    {
        if(isset($this->view) && $this->view instanceof Core\View)
            return $this->view->getDocument();

        return null;
    }

    public function getAllDocument(): ?Core\ViewResource
    {
        if(isset($this->view) && $this->view instanceof Core\View)
            return $this->view->getAllDocument();

        return null;
    }
}
