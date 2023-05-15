<?php

namespace Domain\Aggregates\NonFungibleAsset\Actions;

final readonly class DisposeOfNonFungibleAsset
{
    public function __construct(
        public string $date,
        public int $proceeds,
    ) {
    }
}
