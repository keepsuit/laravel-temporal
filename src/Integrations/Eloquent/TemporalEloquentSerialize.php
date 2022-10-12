<?php

namespace Keepsuit\LaravelTemporal\Integrations\Eloquent;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;

/**
 * @mixin Model
 */
trait TemporalEloquentSerialize
{
    protected function mapAttributeKeyToTemporal(string $attribute): string
    {
        return match (config('temporal.integrations.eloquent.serialize_attribute_case')) {
            'snake' => Str::snake($attribute),
            'camel' => Str::camel($attribute),
            default => $attribute,
        };
    }

    protected function mapAttributeKeyFromTemporal(string $attribute): string
    {
        return match (config('temporal.integrations.eloquent.deserialize_attribute_case')) {
            'snake' => Str::snake($attribute),
            'camel' => Str::camel($attribute),
            default => $attribute,
        };
    }

    public function toTemporalPayload(): array
    {
        $relations = Collection::make($this->getArrayableRelations())
            ->mapWithKeys(function (mixed $value, string $key) {
                $key = static::$snakeAttributes ? Str::snake($key) : $key;

                return [
                    $key => match (true) {
                        $value instanceof TemporalSerializable => $value->toTemporalPayload(),
                        $value instanceof Arrayable => $value->toArray(),
                        default => $value,
                    },
                ];
            });

        return Collection::make($this->attributesToArray())
            ->merge($relations)
            ->mapWithKeys(fn (mixed $value, string $key) => [$this->mapAttributeKeyToTemporal($key) => $value])
            ->put('__exists', $this->exists)
            ->all();
    }

    public static function fromTemporalPayload(array $payload): static
    {
        $model = (new static());

        /** @var Collection $attributes */
        $attributes = Collection::make($payload)
            ->mapWithKeys(fn (mixed $value, string $key) => [$model->mapAttributeKeyFromTemporal($key) => $value]);

        $relationships = $attributes->mapWithKeys(fn (mixed $value, string $key) => match (true) {
            $model->isRelation($key) => [$key => $key],
            $model->isRelation(Str::snake($key)) => [$key => Str::snake($key)],
            $model->isRelation(Str::camel($key)) => [$key => Str::camel($key)],
            default => [],
        });

        $instance = $model->newInstance(
            $attributes->except($relationships->keys()->merge(['__exists']))->all(),
            $attributes->get('__exists', $attributes->get($model->getKeyName()) !== null),
        );

        foreach ($relationships as $attributeKey => $relationship) {
            /** @var Relation $relation */
            $relation = $model->$relationship();

            if ($relation === null) {
                continue;
            }

            $relatedModel = $relation->getRelated();

            if ($relation instanceof BelongsTo || $relation instanceof HasOne) {
                $instance->setRelation($relationship, self::buildRelatedInstance($relatedModel, $attributes->get($attributeKey)));
                continue;
            }

            $instance->setRelation($relationship, $relatedModel->newCollection(
                Collection::make($attributes->get($attributeKey))
                    ->map(fn (array $data) => static::buildRelatedInstance($relatedModel, $data))
                    ->filter()
                    ->all()
            ));
        }

        return $instance;
    }

    private static function buildRelatedInstance(Model $relatedModel, ?array $attributes): ?Model
    {
        if ($attributes === null) {
            return null;
        }

        if ($relatedModel instanceof TemporalSerializable) {
            return $relatedModel::fromTemporalPayload($attributes);
        }

        $exists = Arr::get($attributes, '__exists', Arr::get($attributes, $relatedModel->getKeyName()) !== null);

        return $relatedModel->newInstance(Arr::except($attributes, ['__exists']), $exists);
    }
}