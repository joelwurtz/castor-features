<?php

namespace JCastor\Features;

use Castor\Attribute\AsListener;use Castor\Attribute\AsTask;

use Castor\Event\AfterApplicationInitializationEvent;use Castor\TaskDescriptorCollection;use JCastor\Features\Attribute\FeatureTask;use function Castor\get_cache;use function Castor\io;use function Castor\variable;

function has_feature(string|\Stringable|FeatureInterface $feature): bool
{
    $defaultFeatures = variable('default_features', []);
    $cache = get_cache();
    $featureCache = $cache->getItem('features');
    $features = $featureCache->isHit() ? $featureCache->get() : $defaultFeatures;

    return \in_array((string)$feature, $features);
}

#[AsTask(description: 'List features', namespace: 'features', name: 'list')]
function list_features(bool $enabled = null): void
{
    foreach (FeatureRegistry::getFeatures() as $feature) {
        io()->writeln(sprintf('%s: %s, enabled : %s', $feature, $feature->getDescription(), has_feature($feature) ? 'yes' : 'no'));
    }
}

#[AsTask(description: 'Configure features', namespace: 'features', name: 'configure')]
function configure(): void
{
    $newFeatures = [];

    foreach (FeatureRegistry::getFeatures() as $feature) {
        $enabled = io()->confirm(sprintf('Enable "%s" feature : %s', $feature, $feature->getDescription()), has_feature($feature));
        if ($enabled) {
            $newFeatures[] = (string)$feature;
        }
    }

    $cache = get_cache();
    $featureCache = $cache->getItem('features');
    $featureCache->set($newFeatures);
    $cache->save($featureCache);
}

#[AsListener(AfterApplicationInitializationEvent::class)]
function unregister_featured_tasks(AfterApplicationInitializationEvent $event): void
{
    $taskDescriptors = [];

    foreach ($event->taskDescriptorCollection->taskDescriptors as $taskDescriptor) {
        $attributes = $taskDescriptor->function->getAttributes(FeatureTask::class);

        if (!$attributes) {
            $taskDescriptors[] = $taskDescriptor;
            continue;
        }

        foreach ($attributes as $attribute) {
            /** @var FeatureTask $featureTask */
            $featureTask = $attribute->newInstance();

            if (has_feature($featureTask->feature)) {
                $taskDescriptors[] = $taskDescriptor;
                continue 2;
            }
        }
    }

    $event->taskDescriptorCollection = new TaskDescriptorCollection($taskDescriptors, $event->taskDescriptorCollection->symfonyTaskDescriptors);
}