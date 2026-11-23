<?php

declare(strict_types=1);

namespace Controllers;

use Lib\DB;
use function Lib\responseJson;

class ChatController
{
    public static function uploadImage(): void
    {
        // receive the image file from the request
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            responseJson(data: [], message: 'No image file provided or upload error', statusCode: 400);
            return;
        }

        $image = $_FILES['image'];

        // save to storage folder
        $storagePath = dirname(__DIR__) . '/storage/';
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0777, true);
        }

        $filename = basename($image['name']);
        move_uploaded_file($image['tmp_name'], $storagePath . $filename);

        // save to database
        $db = DB::getInstance();
        $statement = $db->prepare(
            'INSERT INTO chats (image, reference) VALUES (:image, :reference)'
        );
        $statement->execute([
            'image' => $filename,
            'reference' => (string) (time() + rand(1, 1000))
        ]);
        $id = $db->lastInsertId();

        $imageUrl = '/storage/' . $filename;

        responseJson(
            data: [
                'id' => (int) $id,
                'image' => $imageUrl
            ],
            message: 'Image received successfully',
            statusCode: 200
        );
    }
}
