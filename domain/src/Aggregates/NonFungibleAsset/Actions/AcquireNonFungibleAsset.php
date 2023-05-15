<?php

namespace Domain\Aggregates\NonFungibleAsset\Actions;

final readonly class AcquireNonFungibleAsset
{
    public function __construct(
        public string $date,
        public int $costBasis,
    ) {
    }
}
