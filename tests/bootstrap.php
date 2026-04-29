<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/.env.test.local')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env.test.local');
} elseif (file_exists(dirname(__DIR__).'/.env.test')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env.test');
} elseif (file_exists(dirname(__DIR__).'/.env.local')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env.local');
} else {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

putenv('DATABASE_URL=postgresql://rqbi_user:rqbi_password@127.0.0.1:5432/rqbi_chat_test');
