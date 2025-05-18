<?php

namespace Keepsuit\LaravelTemporal\Integrations\Eloquent;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;

class TemporalEloquentSerializer
{
    /**
     * @var array<string, string[]>
     */
    protected static array $modelsRelationsMap = [];

    public static function toPayload(Model $model): array
    {
        $relations = Collection::make($model->getRelations())
            ->mapWithKeys(function (EloquentCollection|Model $value, string $key): array {
                return [
                    $key => match (true) {
                        $value instanceof TemporalSerializable => $value->toTemporalPayload(),
                        $value instanceof Model => static::toPayload($value),
                        $value instanceof EloquentCollection => $value->map(fn (Model $related) => match (true) {
                            $related instanceof TemporalSerializable => $related->toTemporalPayload(),
                            default => static::toPayload($related),
                        }),
                    },
                ];
            });

        return Collection::make($model->attributesToArray())
            ->merge($relations)
            ->mapWithKeys(fn (mixed $value, string $key) => [static::mapAttributeKeyToTemporal($key) => $value])
            ->when(static::shouldIncludeMetadataFields(), fn (Collection $collection) => $collection
                ->put('__exists', $model->exists)
                ->put('__dirty', $model->isDirty())
            )
            ->all();
    }

    /**
     * @param  class-string<Model>  $className
     */
    public static function fromPayload(string $className, array $payload): Model
    {
        $model = new $className;

        $relations = static::getModelRelationMethods($model);

        /** @var Collection<string,mixed> $attributes */
        $attributes = Collection::make($payload)
            ->mapWithKeys(fn (mixed $value, string $key) => [static::mapAttributeKeyFromTemporal($key) => $value]);

        /** @var bool $exists */
        $exists = $attributes->get('__exists', $attributes->get($model->getKeyName()) !== null);

        /** @var bool $dirty */
        $dirty = $attributes->get('__dirty', true);

        $instance = $model->newInstance([], $exists);

        $instance->forceFill($attributes->except($relations)->except(['__exists', '__dirty'])->all());

        if (! $dirty) {
            $instance->syncOriginal();
        }

        foreach ($relations as $relationName) {
            if (! $attributes->has($relationName)) {
                continue;
            }

            $relation = $model->{$relationName}();

            if (! ($relation instanceof Relation)) {
                continue;
            }

            $relatedModel = $relation->getRelated();

            if ($relation instanceof BelongsTo || $relation instanceof HasOne || $relation instanceof MorphOne) {
                $instance->setRelation(
                    $relationName,
                    static::fromPayload($relatedModel::class, $attributes->get($relationName))
                );

                continue;
            }

            $instance->setRelation($relationName, $relatedModel->newCollection(
                Collection::make($attributes->get($relationName) ?? [])
                    ->map(fn (array $data) => static::fromPayload($relatedModel::class, $data))
                    ->filter()
                    ->all()
            ));
        }

        return $instance;
    }

    protected static function mapAttributeKeyToTemporal(string $attribute): string
    {
        return match (config('temporal.integrations.eloquent.serialize_attribute_case')) {
            'snake' => Str::snake($attribute),
            'camel' => Str::camel($attribute),
            default => $attribute,
        };
    }

    protected static function mapAttributeKeyFromTemporal(string $attribute): string
    {
        return match (config('temporal.integrations.eloquent.deserialize_attribute_case')) {
            'snake' => Str::snake($attribute),
            'camel' => Str::camel($attribute),
            default => $attribute,
        };
    }

    protected static function shouldIncludeMetadataFields(): bool
    {
        return config('temporal.integrations.eloquent.include_metadata_field', false);
    }

    protected static function getModelRelationMethods(Model $model): array
    {
        if (isset(static::$modelsRelationsMap[$model::class])) {
            return static::$modelsRelationsMap[$model::class];
        }

        $reflectionClass = new \ReflectionClass($model);

        $relations = collect($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC))
            ->filter(function (\ReflectionMethod $reflectionMethod): bool {
                $returnType = $reflectionMethod->getReturnType();

                return $returnType instanceof \ReflectionNamedType
                    && is_subclass_of($returnType->getName(), Relation::class);
            })
            ->map(fn (\ReflectionMethod $reflectionMethod) => $reflectionMethod->getName())
            ->values()
            ->all();

        return static::$modelsRelationsMap[$model::class] = $relations;
    }
}
