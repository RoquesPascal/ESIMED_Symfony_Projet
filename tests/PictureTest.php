<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Picture;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class PictureTest extends ApiTestCase
{
    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testCreatePicture() : void
    {
        $file = new UploadedFile(
            'tests/Mazda RX-7.jpg',
            'Mazda RX-7.jpg',
            'image/jpg',
        );

        $response = static::createClient()->request(
            'POST',
            '/api/pictures',
            [
                'headers' => ['Content-Type' => 'multipart/form-data'],
                'extra' => [
                    'files' => [
                        'file' => $file,
                    ],
                ],
            ]

        );
        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/api/contexts/Picture',
            '@type' => 'https://schema.org/MediaObject',
        ]);

        $this->assertMatchesRegularExpression('~^/api/pictures/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(Picture::class);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testGetAllPicture() : void
    {
        $response = static::createClient()->request('GET', '/api/pictures');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testGetByIdPicture() : void
    {
        $response = static::createClient()->request('GET', '/api/pictures/1');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
    }
}