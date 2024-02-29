<?php

namespace JCastor\Features;

interface FeatureInterface extends \Stringable
{
    public function getDescription(): string;
}