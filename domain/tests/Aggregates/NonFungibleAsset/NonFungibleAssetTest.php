<?php

use Domain\Aggregates\NonFungibleAsset\Actions\AcquireNonFungibleAsset;
use Domain\Aggregates\NonFungibleAsset\Actions\DisposeOfNonFungibleAsset;
use Domain\Aggregates\NonFungibleAsset\Actions\IncreaseNonFungibleAssetCostBasis;
use Domain\Aggregates\NonFungibleAsset\Events\NonFungibleAssetAcquired;
use Domain\Aggregates\NonFungibleAsset\Events\NonFungibleAssetCostBasisIncreased;
use Domain\Aggregates\NonFungibleAsset\Events\NonFungibleAssetDisposedOf;
use Domain\Aggregates\NonFungibleAsset\Exceptions\NonFungibleAssetException;
use Domain\Tests\Aggregates\NonFungibleAsset\NonFungibleAssetTestCase;

uses(NonFungibleAssetTestCase::class);

it('can acquire a non-fungible asset', function () {
    $acquireNonFungibleAsset = new AcquireNonFungibleAsset(
        date: '2015-10-21',
        costBasis: 100,
    );

    $nonFungibleAssetAcquired = new NonFungibleAssetAcquired(
        date: '2015-10-21',
        costBasis: 100,
    );

    $this->when($acquireNonFungibleAsset)
        ->then($nonFungibleAssetAcquired);
});

it('can acquire a non-fungible asset 2')
    ->when(new AcquireNonFungibleAsset(date: '2015-10-21', costBasis: 100))
    ->then(new NonFungibleAssetAcquired(date: '2015-10-21', costBasis: 100));

it('cannot acquire the same non-fungible asset more than once', function () {
    $nonFungibleAssetAcquired = new NonFungibleAssetAcquired(
        date: '2015-10-21',
        costBasis: 100,
    );

    $acquireSameNonFungibleAsset = new AcquireNonFungibleAsset(
        date: '2015-10-22',
        costBasis: 100,
    );

    $this->given($nonFungibleAssetAcquired)
        ->when($acquireSameNonFungibleAsset)
        ->expectToFail(NonFungibleAssetException::alreadyAcquired($this->aggregateRootId));
});

it('can increase the cost basis of a non-fungible asset', function () {
    $nonFungibleAssetAcquired = new NonFungibleAssetAcquired(
        date: '2015-10-21',
        costBasis: 100,
    );

    $increaseNonFungibleAssetCostBasis = new IncreaseNonFungibleAssetCostBasis(
        date: '2015-10-22',
        costBasisIncrease: 50,
    );

    $nonFungibleAssetCostBasisIncreased = new NonFungibleAssetCostBasisIncreased(
        date: '2015-10-22',
        costBasisIncrease: 50,
        costBasis: 150,
    );

    $this->given($nonFungibleAssetAcquired)
        ->when($increaseNonFungibleAssetCostBasis)
        ->then($nonFungibleAssetCostBasisIncreased);
});

it('cannot increase the cost basis of a non-fungible asset that has not been acquired', function () {
    $increaseNonFungibleAssetCostBasis = new IncreaseNonFungibleAssetCostBasis(
        date: '2015-10-21',
        costBasisIncrease: 100,
    );

    $this->when($increaseNonFungibleAssetCostBasis)
        ->expectToFail(NonFungibleAssetException::notAcquired($this->aggregateRootId));
});

it('can dispose of a non-fungible asset', function (int $costBasis, int $proceeds, int $capitalGain) {
    $nonFungibleAssetAcquired = new NonFungibleAssetAcquired(
        date: '2015-10-21',
        costBasis: $costBasis,
    );

    $disposeOfNonFungibleAsset = new DisposeOfNonFungibleAsset(
        date: '2015-10-22',
        proceeds: $proceeds,
    );

    $nonFungibleAssetDisposedOf = new NonFungibleAssetDisposedOf(
        date: '2015-10-22',
        costBasis: $costBasis,
        proceeds: $proceeds,
        capitalGain: $capitalGain,
    );

    $this->given($nonFungibleAssetAcquired)
        ->when($disposeOfNonFungibleAsset)
        ->then($nonFungibleAssetDisposedOf);
})->with([
    'positive capital gain' => [100, 150, 50],
    'negative capital gain' => [150, 100, -50],
]);

it('can dispose of a non-fungible asset that had a cost basis increase', function () {
    $nonFungibleAssetAcquired = new NonFungibleAssetAcquired(
        date: '2015-10-21',
        costBasis: 100,
    );

    $nonFungibleAssetCostBasisIncreased = new NonFungibleAssetCostBasisIncreased(
        date: '2015-10-22',
        costBasisIncrease: 50,
        costBasis: 150,
    );

    $disposeOfNonFungibleAsset = new DisposeOfNonFungibleAsset(
        date: '2015-10-23',
        proceeds: 200,
    );

    $nonFungibleAssetDisposedOf = new NonFungibleAssetDisposedOf(
        date: '2015-10-23',
        costBasis: 150,
        proceeds: 200,
        capitalGain: 50,
    );

    $this->given($nonFungibleAssetAcquired, $nonFungibleAssetCostBasisIncreased)
        ->when($disposeOfNonFungibleAsset)
        ->then($nonFungibleAssetDisposedOf);
});

it('cannot dispose of a non-fungible asset that has not been acquired', function () {
    $disposeOfNonFungibleAsset = new DisposeOfNonFungibleAsset(
        date: '2015-10-22',
        proceeds: 150,
    );

    $this->when($disposeOfNonFungibleAsset)
        ->expectToFail(NonFungibleAssetException::notAcquired($this->aggregateRootId));
});
