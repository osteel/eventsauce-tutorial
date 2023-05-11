<?php

namespace Domain\Aggregates\NonFungibleAsset\Events;

final readonly class NonFungibleAssetCostBasisIncreased
{
    public function __construct(
        public string $asset,
        public string $date,
        public int $costBasisIncrease,
    ) {
    }
}
