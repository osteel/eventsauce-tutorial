<?php

namespace Domain\Aggregates\NonFungibleAsset\Events;

final readonly class NonFungibleAssetAcquired
{
    public function __construct(
        public string $asset,
        public string $date,
        public int $costBasis,
    ) {
    }
}
