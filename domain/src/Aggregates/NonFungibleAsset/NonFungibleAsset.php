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

    private ?string $asset = null;

    private int $costBasis = 0;

    public function acquire(AcquireNonFungibleAsset $action): void
    {
        if ($this->asset !== null) {
            throw NonFungibleAssetException::alreadyAcquired($action->asset);
        }

        $this->recordThat(new NonFungibleAssetAcquired(
            asset: $action->asset,
            date: $action->date,
            costBasis: $action->costBasis,
        ));
    }

    public function applyNonFungibleAssetAcquired(NonFungibleAssetAcquired $event): void
    {
        $this->asset = $event->asset;
        $this->costBasis = $event->costBasis;
    }

    public function increaseCostBasis(IncreaseNonFungibleAssetCostBasis $action): void
    {
        if (is_null($this->asset)) {
            throw NonFungibleAssetException::notAcquired($action->asset);
        }

        if ($action->asset !== $this->asset) {
            throw NonFungibleAssetException::assetMismatch(incoming: $action->asset, current: $this->asset);
        }

        $this->recordThat(new NonFungibleAssetCostBasisIncreased(
            asset: $action->asset,
            date: $action->date,
            costBasisIncrease: $action->costBasisIncrease,
        ));
    }

    public function applyNonFungibleAssetCostBasisIncreased(NonFungibleAssetCostBasisIncreased $event): void
    {
        $this->costBasis += $event->costBasisIncrease;
    }

    public function disposeOf(DisposeOfNonFungibleAsset $action): void
    {
        if (is_null($this->asset)) {
            throw NonFungibleAssetException::notAcquired($action->asset);
        }

        if ($action->asset !== $this->asset) {
            throw NonFungibleAssetException::assetMismatch(incoming: $action->asset, current: $this->asset);
        }

        $this->recordThat(new NonFungibleAssetDisposedOf(
            asset: $action->asset,
            date: $action->date,
            costBasis: $this->costBasis,
            proceeds: $action->proceeds,
        ));
    }

    public function applyNonFungibleAssetDisposedOf(NonFungibleAssetDisposedOf $event): void
    {
        $this->asset = null;
        $this->costBasis = 0;
    }
}
