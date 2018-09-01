<?php
namespace App\Controllers;

use Elasticsearch\Client;
use Psr\Container\ContainerInterface;

class NoteController {
    public function stored($body, ContainerInterface $container) {
        $container->get(Client::class)->index([
            'index' => $container->get('elasticsearch')['index'],
            'type' => 'note',
            'id' => $body['id'],
            'body' => $body
        ]);
        echo "Success! Stored {$body['id']}";
    }

    public function destroyed($body, ContainerInterface $container) {
        $container->get(Client::class)->delete([
            'index' => $container->get('elasticsearch')['index'],
            'type' => 'note',
            'id' => $body
        ]);
        echo "Success! Destroyed {$body}";
    }

    public function updated($body, ContainerInterface $container) {
        $container->get(Client::class)->update([
            'index' => $container->get('elasticsearch')['index'],
            'type' => 'note',
            'id' => $body['id'],
            'body' => [
                'doc' => $body
            ]
        ]);
        echo "Success! Updated {$body['id']}";
    }
}