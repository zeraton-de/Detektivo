<?php

namespace Detektivo\Controller;


class Admin extends \Cockpit\AuthController {

    public function index() {

        $collections = $this->module('detektivo')->config('collections', []);

        return $this->render('detektivo:views/index.php', compact('collections'));
    }


    public function reindex() {

        $storage = $this->module('detektivo')->storage();
        $collection = $this->param('collection');

        if (!$collection) {
            return false;
        }

        $options = [
            'limit' => 100,
            'skip' => $this->param('skip', 0),
            'fields' => ['_id' => 1]
        ];

        $fields = $this->module('detektivo')->fields($collection);

        foreach ($fields as $field) {
            $options['fields'][$field] = 1;
        }

        $this->module('detektivo')->storage()->empty($collection);

        $items  = $this->module('collections')->find($collection, $options);

        if (!count($items) || count($items) < $options['limit']) {
            foreach ($items as $item) {
                $storage->save($collection, $item);
            }
            return ['finished' => true, 'imported' => count($items)];
        }

        $storage->batchSave($collection, $items);

        return ['finished' => false, 'imported' => count($items), 'items' => $items];
    }
}
