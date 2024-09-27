<?php
require 'vendor/autoload.php';

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Message;
use GuzzleHttp\Client;

$bot = new BotApi('');

$lastUpdateId = 0;

while (true) {
    try {
        $updates = $bot->getUpdates($lastUpdateId + 1, 10, 30);

        foreach ($updates as $update) {
            $message = $update->getMessage();
            if ($message instanceof Message) {
                $chatId = $message->getChat()->getId();
                $text = $message->getText();

                if (strcasecmp($text, '/start') === 0) {
                    $bot->sendMessage($chatId, "Добрый день. Как вас зовут?");
                } elseif ($text) {
                    $name = $text;
                    
                    $client = new Client();
                    $response = $client->get('https://api.exchangerate-api.com/v4/latest/USD');
                    $data = json_decode($response->getBody(), true);
                    $usdToRub = $data['rates']['RUB'];

                    $bot->sendMessage($chatId, "Рад знакомству, $name! Курс доллара сегодня " . number_format($usdToRub, 2) . "р.");
                }
            }

            $lastUpdateId = $update->getUpdateId();
        }
    } catch (\TelegramBot\Api\HttpException $e) {
        error_log("Telegram API Error: " . $e->getMessage());
        sleep(5);
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        sleep(5);
    }

    sleep(1);
}