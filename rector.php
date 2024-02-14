<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector;
use Rector\CodingStyle\Rector\Closure\StaticClosureRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector;
use Rector\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector;
use Rector\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector;
use Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnDirectArrayRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector;

return RectorConfig::configure()
    ->withPhpSets()
    ->withPreparedSets()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/config',
    ])
    ->withSkip([
        AddArrowFunctionReturnTypeRector::class,
        CallableThisArrayToAnonymousFunctionRector::class,
        ChangeAndIfToEarlyReturnRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        PostIncDecToPreIncDecRector::class,
        RemoveNullPropertyInitializationRector::class,
        ReturnBinaryOrToEarlyReturnRector::class,
        ReturnTypeFromReturnDirectArrayRector::class,
        ReturnTypeFromReturnNewRector::class,
        ReturnTypeFromStrictTypedCallRector::class,
        StaticArrowFunctionRector::class,
        StaticClosureRector::class,
    ]);
