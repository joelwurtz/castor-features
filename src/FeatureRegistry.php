<?php

namespace JCastor\Features;

final class FeatureRegistry
{
    private static array $features = [];

    /**
     * @param FeatureInterface[] $features
     */
    public static function setFeatures(array $features): void
    {
        foreach ($features as $feature) {
            self::$features[(string)$feature] = $feature;
        }
    }

    /**
     * @return FeatureInterface[]
     */
    public static function getFeatures(): array
    {
        return self::$features;
    }
}