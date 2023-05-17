<?php

namespace Domain\Aggregates\NonFungibleAsset\Exceptions;

use RuntimeException;

final class NonFungibleAssetException extends RuntimeException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function alreadyAcquired(string $asset): self
    {
        return new self(sprintf('Non-fungible asset %s has already been acquired', $asset));
    }

    public static function notAcquired(string $asset): self
    {
        return new self(sprintf('Non-fungible asset %s has not been acquired', $asset));
    }
}
