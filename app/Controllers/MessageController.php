<?php

namespace App\Controllers;

use App\Service\Publisher;
use Pecee\SimpleRouter\SimpleRouter as Router;

class MessageController
{
    /**
     * @throws \Exception
     */
    public function store(): void
    {
        $request = Router::request();
        $userId = $request->getInputHandler()->post('user_id')->getValue();
        $message = $request->getInputHandler()->post('message')->getValue();

        if ($userId && $message && is_numeric($userId) && is_string($message)) {
            $publisher = new Publisher();

            $publisher($userId, $message);

            Router::response()->httpCode(200)->json(['data' => 'Message delivered']);
        }

        Router::response()->httpCode(422)->json(['data' => 'Data is invalid']);
    }
}