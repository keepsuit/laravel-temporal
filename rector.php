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
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnDirectArrayRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $config): void {
    $config->phpVersion(PhpVersion::PHP_81);

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
        SetList::TYPE_DECLARATION,
        LevelSetList::UP_TO_PHP_81,
    ]);

    $config->skip([
        AddArrowFunctionReturnTypeRector::class,
        CallableThisArrayToAnonymousFunctionRector::class,
        ChangeAndIfToEarlyReturnRector::class,
        FinalizeClassesWithoutChildrenRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        JsonThrowOnErrorRector::class,
        PostIncDecToPreIncDecRector::class,
        RemoveNullPropertyInitializationRector::class,
        ReturnBinaryOrToEarlyReturnRector::class,
        ReturnTypeFromReturnDirectArrayRector::class,
        ReturnTypeFromReturnNewRector::class,
        ReturnTypeFromStrictTypedCallRector::class,
        StaticArrowFunctionRector::class,
        StaticClosureRector::class,
    ]);
};
