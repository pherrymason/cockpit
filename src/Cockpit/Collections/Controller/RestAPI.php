<?php declare(strict_types=1);

namespace Cockpit\Collections\Controller;

use Cockpit\App\Revisions;
use Cockpit\Collections\CollectionRepository;
use Cockpit\Collections\EntriesRepository;
use Cockpit\Collections\Entry;
use Lime\App;

final class RestAPI extends \LimeExtra\Controller
{
    /** @var CollectionRepository */
    private $collections;
    /** @var EntriesRepository */
    private $entries;
    /** @var Revisions */
    private $revisions;

    public function __construct(CollectionRepository $collections, EntriesRepository $entries, Revisions $revisions, App $app)
    {
        $this->collections = $collections;
        $this->entries = $entries;
        $this->revisions = $revisions;
        $this->app = $app;
        parent::__construct($app);
    }


    public function get($collectionName)
    {
        //$collectionName = $collection;
        if (!$collectionName) {
            return $this->stop('{"error": "Missing collection name"}', 412);
        }

        $collection = $this->collections->byName($collectionName);
        if ($collection === null) {
            return $this->stop('{"error": "Collection not found"}', 412);
        }

        if ($filter   = $this->param('filter', null))   $options['filter'] = $filter;
        if ($limit    = $this->param('limit', null))    $options['limit'] = \intval($limit);
        if ($sort     = $this->param('sort', null))     $options['sort'] = $sort;
        if ($fields   = $this->param('fields', null))   $options['fields'] = $fields;
        if ($skip     = $this->param('skip', null))     $options['skip'] = \intval($skip);
        if ($populate = $this->param('populate', null)) $options['populate'] = $populate;
        // cast string values if get request
        if ($filter && isset($_GET['filter'])) $options['filter'] = $this->app->helper('utils')->fixStringBooleanValues($filter);
        if ($fields && isset($_GET['fields'])) $options['fields'] = $this->app->helper('utils')->fixStringNumericValues($fields);

        // fields filter
        if ($fieldsFilter = $this->param('fieldsFilter', [])) {
            $options['fieldsFilter'] = $fieldsFilter;
        }

        if ($lang = $this->param('lang', false)) {
            $options['lang'] = $lang;
        }

        if ($ignoreDefaultFallback = $this->param('ignoreDefaultFallback', false)) {
            $fieldsFilter['ignoreDefaultFallback'] = \in_array($ignoreDefaultFallback, ['1', '0']) ? \boolval($ignoreDefaultFallback) : $ignoreDefaultFallback;
        }
        //if ($user) $fieldsFilter['user'] = $user;

        if (\is_array($fieldsFilter) && \count($fieldsFilter)) {
            $options['fieldsFilter'] = $fieldsFilter;
        }

        if ($sort) {
            foreach ($sort as $key => &$value) {
                $options['sort'][$key]= \intval($value);
            }
        }

        $entries = $this->entries->byCollectionFiltered($collection, [], $options);
        if (!$skip && !$limit) {
            $count = \count($entries);
        } else {
            $count = $this->entries->count($collection, $filter ? $filter : []);
        }
        $isSortable = $collection->sortable();

        $fields = [];

        foreach ($collection->fields() as $field) {
            /*if (
                $user && isset($field['acl']) &&
                \is_array($field['acl']) && \count($field['acl']) &&
                !(\in_array($user['_id'] , $field['acl']) || \in_array($user['group'] , $field['acl']))
            ) {
                continue;
            }*/

            $fields[$field->name()] = [
                'name' => $field->name(),
                'type' => $field->type(),
                'localize' => $field->localize(),
                'options' => $field->options(),
            ];
        }

        return [
            'fields'   => $fields,
            'entries'  => array_map(function (Entry $entry) {
                    return $entry->toArray();
                }, $entries),
            'total'    => $count
        ];
    }
}
