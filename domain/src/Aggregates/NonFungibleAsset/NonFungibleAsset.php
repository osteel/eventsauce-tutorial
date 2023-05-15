<?php

namespace Domain\Aggregates\NonFungibleAsset;

use Domain\Aggregates\NonFungibleAsset\Actions\AcquireNonFungibleAsset;
use Domain\Aggregates\NonFungibleAsset\Actions\DisposeOfNonFungibleAsset;
use Domain\Aggregates\NonFungibleAsset\Actions\IncreaseNonFungibleAssetCostBasis;
use Domain\Aggregates\NonFungibleAsset\Events\NonFungibleAssetAcquired;
use Domain\Aggregates\NonFungibleAsset\Events\NonFungibleAssetCostBasisIncreased;
use Domain\Aggregates\NonFungibleAsset\Events\NonFungibleAssetDisposedOf;
use Domain\Aggregates\NonFungibleAsset\Exceptions\NonFungibleAssetException;
use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;

class NonFungibleAsset implements AggregateRoot
{
    use AggregateRootBehaviour;

    private bool $acquired = false;

    private int $costBasis = 0;

    public function acquire(AcquireNonFungibleAsset $action): void
    {
        throw_if($this->acquired, NonFungibleAssetException::alreadyAcquired($this->aggregateRootId));

        $this->recordThat(new NonFungibleAssetAcquired(
            date: $action->date,
            costBasis: $action->costBasis,
        ));
    }

    public function applyNonFungibleAssetAcquired(NonFungibleAssetAcquired $event): void
    {
        $this->acquired = true;
        $this->costBasis = $event->costBasis;
    }

    public function increaseCostBasis(IncreaseNonFungibleAssetCostBasis $action): void
    {
        throw_unless($this->acquired, NonFungibleAssetException::notAcquired($this->aggregateRootId));

        $this->recordThat(new NonFungibleAssetCostBasisIncreased(
            date: $action->date,
            costBasisIncrease: $action->costBasisIncrease,
            costBasis: $this->costBasis + $action->costBasisIncrease,
        ));
    }

    public function applyNonFungibleAssetCostBasisIncreased(NonFungibleAssetCostBasisIncreased $event): void
    {
        $this->costBasis = $event->costBasis;
    }

    public function disposeOf(DisposeOfNonFungibleAsset $action): void
    {
        throw_unless($this->acquired, NonFungibleAssetException::notAcquired($this->aggregateRootId));

        $this->recordThat(new NonFungibleAssetDisposedOf(
            date: $action->date,
            costBasis: $this->costBasis,
            proceeds: $action->proceeds,
            capitalGain: $action->proceeds - $this->costBasis
        ));
    }

    public function applyNonFungibleAssetDisposedOf(NonFungibleAssetDisposedOf $event): void
    {
        $this->acquired = false;
        $this->costBasis = 0;
    }
}
