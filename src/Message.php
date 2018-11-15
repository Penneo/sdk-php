<?php
namespace Penneo\SDK;

use Penneo\SDK\ApiConnector;

class Message
{
    public static function retrieve($limit = 10)
    {
        $response = ApiConnector::callServer('messages/'.$limit);
        if (!$response) {
            return array();
        }
        
        return json_decode((string) $response->getBody(), true);
    }

    public static function delete($id)
    {
        return (bool) ApiConnector::callServer('message/'.$id, null, 'delete');
    }
}
