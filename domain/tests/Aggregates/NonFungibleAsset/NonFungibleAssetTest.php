<?php

use Domain\Aggregates\NonFungibleAsset\Actions\AcquireNonFungibleAsset;
use Domain\Aggregates\NonFungibleAsset\Actions\DisposeOfNonFungibleAsset;
use Domain\Aggregates\NonFungibleAsset\Actions\IncreaseNonFungibleAssetCostBasis;
use Domain\Aggregates\NonFungibleAsset\Events\NonFungibleAssetAcquired;
use Domain\Aggregates\NonFungibleAsset\Events\NonFungibleAssetCostBasisIncreased;
use Domain\Aggregates\NonFungibleAsset\Events\NonFungibleAssetDisposedOf;
use Domain\Aggregates\NonFungibleAsset\Exceptions\NonFungibleAssetException;
use Domain\Tests\Aggregates\NonFungibleAsset\NonFungibleAssetTestCase;
use function EventSauce\EventSourcing\PestTooling\expectToFail;
use function EventSauce\EventSourcing\PestTooling\given;
use function EventSauce\EventSourcing\PestTooling\then;
use function EventSauce\EventSourcing\PestTooling\when;

uses(NonFungibleAssetTestCase::class);

it('can acquire a non-fungible asset', function () {
    when(new AcquireNonFungibleAsset(asset: 'MonkeyJPEG', date: '2015-10-21', costBasis: 100));

    then(new NonFungibleAssetAcquired(date: '2015-10-21', costBasis: 100));
});

it('cannot acquire the same non-fungible asset more than once', function () {
    given(new NonFungibleAssetAcquired(date: '2015-10-21', costBasis: 100));

    when(new AcquireNonFungibleAsset(asset: 'MonkeyJPEG', date: '2015-10-22', costBasis: 100));

    expectToFail(NonFungibleAssetException::alreadyAcquired('MonkeyJPEG'));
});

it('can increase the cost basis of a non-fungible asset', function () {
    given(new NonFungibleAssetAcquired(date: '2015-10-21', costBasis: 100));

    when(new IncreaseNonFungibleAssetCostBasis(asset: 'MonkeyJPEG', date: '2015-10-22', costBasisIncrease: 50));

    then(new NonFungibleAssetCostBasisIncreased(date: '2015-10-22', costBasisIncrease: 50, costBasis: 150));
});

it('cannot increase the cost basis of a non-fungible asset that has not been acquired', function () {
    when(new IncreaseNonFungibleAssetCostBasis(asset: 'MonkeyJPEG', date: '2015-10-21', costBasisIncrease: 100));

    expectToFail(NonFungibleAssetException::notAcquired('MonkeyJPEG'));
});

it('can dispose of a non-fungible asset', function (int $costBasis, int $proceeds, int $capitalGain) {
    given(new NonFungibleAssetAcquired(date: '2015-10-21', costBasis: $costBasis));

    when(new DisposeOfNonFungibleAsset(asset: 'MonkeyJPEG', date: '2015-10-22', proceeds: $proceeds));

    then(new NonFungibleAssetDisposedOf(date: '2015-10-22', costBasis: $costBasis, proceeds: $proceeds, capitalGain: $capitalGain));
})->with([
    'positive capital gain' => [100, 150, 50],
    'negative capital gain' => [150, 100, -50],
]);

it('can dispose of a non-fungible asset that had a cost basis increase', function () {
    given(new NonFungibleAssetAcquired(date: '2015-10-21', costBasis: 100));

    given(new NonFungibleAssetCostBasisIncreased(date: '2015-10-22', costBasisIncrease: 50, costBasis: 150));

    when(new DisposeOfNonFungibleAsset(asset: 'MonkeyJPEG', date: '2015-10-23', proceeds: 200));

    then(new NonFungibleAssetDisposedOf(date: '2015-10-23', costBasis: 150, proceeds: 200, capitalGain: 50));
});

it('cannot dispose of a non-fungible asset that has not been acquired', function () {
    when(new DisposeOfNonFungibleAsset(asset: 'MonkeyJPEG', date: '2015-10-22', proceeds: 150));

    expectToFail(NonFungibleAssetException::notAcquired('MonkeyJPEG'));
});
