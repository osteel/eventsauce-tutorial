<?php

namespace Domain\Aggregates\NonFungibleAsset\Exceptions;

use Domain\Aggregates\NonFungibleAsset\ValueObjects\NonFungibleAssetId;
use RuntimeException;

final class NonFungibleAssetException extends RuntimeException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function alreadyAcquired(NonFungibleAssetId $asset): self
    {
        return new self(sprintf('Non-fungible asset %s has already been acquired', $asset->toString()));
    }

    public static function notAcquired(NonFungibleAssetId $asset): self
    {
        return new self(sprintf('Non-fungible asset %s has not been acquired', $asset->toString()));
    }
}
