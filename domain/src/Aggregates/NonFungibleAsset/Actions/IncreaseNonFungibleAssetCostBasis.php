<?php

namespace Domain\Aggregates\NonFungibleAsset\Actions;

final readonly class IncreaseNonFungibleAssetCostBasis
{
    public function __construct(
        public string $date,
        public int $costBasisIncrease,
    ) {
    }
}
