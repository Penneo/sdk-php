<?php
namespace Penneo\SDK;

use Penneo\SDK\ApiConnector;

class Message
{
    /**
     * @param int $limit
     *
     * @return Message[]
     * @throws \Exception
     */
    public static function retrieve($limit = 10)
    {
        $response = ApiConnector::callServer('messages/'.$limit);
        if (!$response) {
            return array();
        }
        
        return json_decode($response->getBody()->getContents(), true);
    }

    public static function delete($id)
    {
        return ApiConnector::callServer('message/'.$id, null, 'delete') !== null;
    }
}
