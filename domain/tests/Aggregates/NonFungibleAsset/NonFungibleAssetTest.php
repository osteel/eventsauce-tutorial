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
        asset: 'MonkeyJPEG',
        date: '2015-10-21',
        costBasis: 100,
    );

    $nonFungibleAssetAcquired = new NonFungibleAssetAcquired(
        asset: 'MonkeyJPEG',
        date: '2015-10-21',
        costBasis: 100,
    );

    $this->when($acquireNonFungibleAsset)
        ->then($nonFungibleAssetAcquired);
});

it('cannot acquire the same non-fungible asset more than once', function () {
    $nonFungibleAssetAcquired = new NonFungibleAssetAcquired(
        asset: 'MonkeyJPEG',
        date: '2015-10-21',
        costBasis: 100,
    );

    $acquireSameNonFungibleAsset = new AcquireNonFungibleAsset(
        asset: 'MonkeyJPEG',
        date: '2015-10-22',
        costBasis: 100,
    );

    $this->given($nonFungibleAssetAcquired)
        ->when($acquireSameNonFungibleAsset)
        ->expectToFail(NonFungibleAssetException::alreadyAcquired('MonkeyJPEG'));
});

it('can increase the cost basis of a non-fungible asset', function () {
    $nonFungibleAssetAcquired = new NonFungibleAssetAcquired(
        asset: 'MonkeyJPEG',
        date: '2015-10-21',
        costBasis: 100,
    );

    $increaseNonFungibleAssetCostBasis = new IncreaseNonFungibleAssetCostBasis(
        asset: 'MonkeyJPEG',
        date: '2015-10-22',
        costBasisIncrease: 50,
    );

    $nonFungibleAssetCostBasisIncreased = new NonFungibleAssetCostBasisIncreased(
        asset: 'MonkeyJPEG',
        date: '2015-10-22',
        costBasisIncrease: 50,
    );

    $this->given($nonFungibleAssetAcquired)
        ->when($increaseNonFungibleAssetCostBasis)
        ->then($nonFungibleAssetCostBasisIncreased);
});

it('cannot increase the cost basis of a non-fungible asset that has not been acquired', function () {
    $increaseNonFungibleAssetCostBasis = new IncreaseNonFungibleAssetCostBasis(
        asset: 'MonkeyJPEG',
        date: '2015-10-21',
        costBasisIncrease: 100,
    );

    $this->when($increaseNonFungibleAssetCostBasis)
        ->expectToFail(NonFungibleAssetException::notAcquired('MonkeyJPEG'));
});

it('cannot increase the cost basis of a non-fungible asset because the assets don\'t match', function () {
    $nonFungibleAssetAcquired = new NonFungibleAssetAcquired(
        asset: 'MonkeyJPEG',
        date: '2015-10-21',
        costBasis: 100,
    );

    $increaseNonFungibleAssetCostBasis = new IncreaseNonFungibleAssetCostBasis(
        asset: 'ZombieJPEG',
        date: '2015-10-22',
        costBasisIncrease: 50,
    );

    $this->given($nonFungibleAssetAcquired)
        ->when($increaseNonFungibleAssetCostBasis)
        ->expectToFail(NonFungibleAssetException::assetMismatch(incoming: 'ZombieJPEG', current: 'MonkeyJPEG'));
});

it('can dispose of a non-fungible asset', function () {
    $nonFungibleAssetAcquired = new NonFungibleAssetAcquired(
        asset: 'MonkeyJPEG',
        date: '2015-10-21',
        costBasis: 100,
    );

    $disposeOfNonFungibleAsset = new DisposeOfNonFungibleAsset(
        asset: 'MonkeyJPEG',
        date: '2015-10-22',
        proceeds: 150,
    );

    $nonFungibleAssetDisposedOf = new NonFungibleAssetDisposedOf(
        asset: 'MonkeyJPEG',
        date: '2015-10-22',
        costBasis: 100,
        proceeds: 150,
    );

    $this->given($nonFungibleAssetAcquired)
        ->when($disposeOfNonFungibleAsset)
        ->then($nonFungibleAssetDisposedOf);
});

it('can dispose of a non-fungible asset that had a cost basis increase', function () {
    $nonFungibleAssetAcquired = new NonFungibleAssetAcquired(
        asset: 'MonkeyJPEG',
        date: '2015-10-21',
        costBasis: 100,
    );

    $nonFungibleAssetCostBasisIncreased = new NonFungibleAssetCostBasisIncreased(
        asset: 'MonkeyJPEG',
        date: '2015-10-22',
        costBasisIncrease: 50,
    );

    $disposeOfNonFungibleAsset = new DisposeOfNonFungibleAsset(
        asset: 'MonkeyJPEG',
        date: '2015-10-23',
        proceeds: 200,
    );

    $nonFungibleAssetDisposedOf = new NonFungibleAssetDisposedOf(
        asset: 'MonkeyJPEG',
        date: '2015-10-23',
        costBasis: 150,
        proceeds: 200,
    );

    $this->given($nonFungibleAssetAcquired, $nonFungibleAssetCostBasisIncreased)
        ->when($disposeOfNonFungibleAsset)
        ->then($nonFungibleAssetDisposedOf);
});

it('cannot dispose of a non-fungible asset that has not been acquired', function () {
    $disposeOfNonFungibleAsset = new DisposeOfNonFungibleAsset(
        asset: 'MonkeyJPEG',
        date: '2015-10-22',
        proceeds: 150,
    );

    $this->when($disposeOfNonFungibleAsset)
        ->expectToFail(NonFungibleAssetException::notAcquired('MonkeyJPEG'));
});

it('cannot dispose of a non-fungible asset because the assets don\'t match', function () {
    $nonFungibleAssetAcquired = new NonFungibleAssetAcquired(
        asset: 'MonkeyJPEG',
        date: '2015-10-21',
        costBasis: 100,
    );

    $disposeOfNonFungibleAsset = new DisposeOfNonFungibleAsset(
        asset: 'ZombieJPEG',
        date: '2015-10-22',
        proceeds: 150,
    );

    $this->given($nonFungibleAssetAcquired)
        ->when($disposeOfNonFungibleAsset)
        ->expectToFail(NonFungibleAssetException::assetMismatch(incoming: 'ZombieJPEG', current: 'MonkeyJPEG'));
});
