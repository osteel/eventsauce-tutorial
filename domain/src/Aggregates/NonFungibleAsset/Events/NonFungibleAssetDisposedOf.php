<?php

namespace Domain\Aggregates\NonFungibleAsset\Events;

final readonly class NonFungibleAssetDisposedOf
{
    public function __construct(
        public string $asset,
        public string $date,
        public int $costBasis,
        public int $proceeds,
    ) {
    }
}
