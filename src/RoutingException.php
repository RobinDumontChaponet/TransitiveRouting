<?php

namespace Transitive\Routing;

class RoutingException extends \UnexpectedValueException
{
    private $queryURL;

    public function __construct(string $message = '', int $code = 0, string $queryURL = null) {
        $this->queryURL = $queryURL;

        parent::__construct($message, $code);
    }

    public function getQueryURL(): ?string
    {
        return $this->queryURL;
    }
}
