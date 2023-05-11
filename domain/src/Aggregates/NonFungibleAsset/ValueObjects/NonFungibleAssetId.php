<?php

namespace Domain\Aggregates\NonFungibleAsset\ValueObjects;

use EventSauce\EventSourcing\AggregateRootId;
use Ramsey\Uuid\Uuid;

final readonly class NonFungibleAssetId implements AggregateRootId
{
    private const NAMESPACE = '64d4faf2-d67a-4716-9b3c-bed1ca053068';

    final private function __construct(public string $id)
    {
    }

    public function toString(): string
    {
        return $this->id;
    }

    public static function fromString(string $aggregateRootId): static
    {
        return new self(Uuid::uuid5(self::NAMESPACE, $aggregateRootId)->toString());
    }
}
