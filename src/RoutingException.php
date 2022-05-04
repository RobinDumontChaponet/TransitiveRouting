<?php

namespace Transitive\Routing;

class RoutingException extends \UnexpectedValueException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        private ?string $queryURL = null,
    ) {
        parent::__construct($message, $code);
    }

    public function getQueryURL(): ?string
    {
        return $this->queryURL;
    }
}
