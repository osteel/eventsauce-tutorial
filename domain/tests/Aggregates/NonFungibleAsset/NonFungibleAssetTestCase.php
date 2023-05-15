<?php

namespace Domain\Tests\Aggregates\NonFungibleAsset;

use Domain\Aggregates\NonFungibleAsset\Actions\AcquireNonFungibleAsset;
use Domain\Aggregates\NonFungibleAsset\Actions\DisposeOfNonFungibleAsset;
use Domain\Aggregates\NonFungibleAsset\Actions\IncreaseNonFungibleAssetCostBasis;
use Domain\Aggregates\NonFungibleAsset\NonFungibleAsset;
use Domain\Aggregates\NonFungibleAsset\ValueObjects\NonFungibleAssetId;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use EventSauce\EventSourcing\TestUtilities\AggregateRootTestCase;

abstract class NonFungibleAssetTestCase extends AggregateRootTestCase
{
    protected function newAggregateRootId(): AggregateRootId
    {
        return NonFungibleAssetId::fromString('MonkeyJPEG');
    }

    protected function aggregateRootClassName(): string
    {
        return NonFungibleAsset::class;
    }

    protected function messageDispatcher(): MessageDispatcher
    {
        return new SynchronousMessageDispatcher();
    }

    public function handle(object $action)
    {
        $nonFungibleAsset = $this->repository->retrieve($this->aggregateRootId);

        match ($action::class) {
            AcquireNonFungibleAsset::class => $nonFungibleAsset->acquire($action),
            IncreaseNonFungibleAssetCostBasis::class => $nonFungibleAsset->increaseCostBasis($action),
            DisposeOfNonFungibleAsset::class => $nonFungibleAsset->disposeOf($action),
        };

        $this->repository->persist($nonFungibleAsset);
    }
}
