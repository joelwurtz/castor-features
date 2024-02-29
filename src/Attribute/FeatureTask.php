<?php

namespace JCastor\Features\Attribute;

use JCastor\Features\FeatureInterface;

#[\Attribute(\Attribute::TARGET_FUNCTION)]
class FeatureTask
{
    public function __construct(
        public string|FeatureInterface $feature,
    ) {
    }
}