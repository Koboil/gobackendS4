<?php

namespace App\Tests\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\MediaObject;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaObjectTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public function testCreateAMediaObject(): void
    {
        // The file "image.jpg" is the folder fixtures which is in the project dir
        $file = new UploadedFile(__DIR__ . '/../fixtures/image.jpg', 'image.jpg');
        $client = self::createClient();

        $client->request('POST', 'http://localhost:8888/api/media_objects', [
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                // If you have additional fields in your MediaObject entity, use the parameters.
                'parameters' => [
                    // 'title' => 'title'
                ],
                'files' => [
                    'file' => $file,
                ],
            ]
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceItemJsonSchema(MediaObject::class);
        $this->assertJsonContains([
            // 'title' => 'My file uploaded',
        ]);
    }
}