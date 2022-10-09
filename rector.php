<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector;
use Rector\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector;
use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector;
use Rector\CodingStyle\Rector\Closure\StaticClosureRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector;
use Rector\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector;
use Rector\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector;
use Rector\Laravel\Set\LaravelLevelSetList;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php80\Rector\FunctionLike\UnionTypesRector;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;

return static function (RectorConfig $config): void {
    $config->phpVersion(PhpVersion::PHP_80);

    $config->paths([
        __DIR__.'/src',
        __DIR__.'/config',
    ]);

    $config->sets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::PRIVATIZATION,
        SetList::TYPE_DECLARATION_STRICT,
        LevelSetList::UP_TO_PHP_81,
        LaravelLevelSetList::UP_TO_LARAVEL_90,
    ]);

    $config->skip([
        FinalizeClassesWithoutChildrenRector::class,
        RemoveNullPropertyInitializationRector::class,
        UnSpreadOperatorRector::class,
        RemoveDelegatingParentCallRector::class,
        CallableThisArrayToAnonymousFunctionRector::class,
        BinarySwitchToIfElseRector::class,
        UnionTypesRector::class,
        ReturnBinaryOrToEarlyReturnRector::class,
        ReturnArrayClassMethodToYieldRector::class,
        PostIncDecToPreIncDecRector::class,
        StaticClosureRector::class,
        StaticArrowFunctionRector::class,
        ReturnTypeFromReturnNewRector::class,
        JsonThrowOnErrorRector::class,
    ]);
};
