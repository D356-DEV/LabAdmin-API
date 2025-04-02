<?php
require __DIR__ . "/../models/Bot.php";
require __DIR__ . "/../../config/db.php";

class BotController
{
    private $botModel;

    public function __construct($pdo)
    {
        $this->botModel = new Bot($pdo);
    }

    // Get bot dataset
    public function getBotDataset(): void
    {
        $bot = $this->botModel->getBotDataset();
        if ($bot) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "data" => $bot,
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => "error",
                "message" => "No bot dataset found.",
            ]);
        }
    }

}
