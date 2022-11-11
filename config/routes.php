<?php

use App\Controllers\MessageController;
use Pecee\SimpleRouter\SimpleRouter as Router;

Router::post('/message', [MessageController::class, 'store']);