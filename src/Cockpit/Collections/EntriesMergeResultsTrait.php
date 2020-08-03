<?php

namespace Cockpit\Collections;

trait EntriesMergeResultsTrait
{
    protected function mergeResults(Collection $collection, array $entries): array
    {
        /** @var Field[] $baseFields */
        $baseFields = array_filter($collection->fields(), function (Field $field) {
            return !$field->localize();
        });
        /** @var Field[] $localizedFields */
        $localizedFields = array_filter($collection->fields(), function (Field $field) {
            return $field->localize();
        });

        $harmonized = [];
        foreach ($entries as $entry) {
            $id = $entry['_id'];
            if (!isset($harmonized[$id])) {
                // Copy fields
                $harmonized[$id] = [
                    'id' => $id,
                    '_created' => $entry['_created'],
                    '_modified' => $entry['_modified'],
                    '_by' => $entry['_by'],
                    '_mby' => $entry['_mby']
                ];
                foreach ($baseFields as $field) {
                    $harmonized[$id][$field->name()] = $entry[$field->name()] ?? null;
                }
            }

            // localized Fields
            $harmonized[$id]['localized'][$entry['language']] = [];
            foreach ($localizedFields as $field) {
                $language = $entry['language'];
                if (!isset($harmonized[$id]['localized'][$language])) {
                    $harmonized[$id]['localized'][$language] = ['language' => $language];
                }
                $harmonized[$id]['localized'][$language][$field->name()] = $entry[$field->name()] ?? null;
            }
        }

        return $harmonized;
    }
}
