<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Advert;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AdvertTest extends ApiTestCase
{
    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testCreateAdvert() : void
    {
        $response = static::createClient()->request('POST', '/api/adverts', ['json' => [
            'title'       => 'string',
            'content'     => 'string',
            'author'      => 'string',
            'email'       => 'user@example.com',
            'category'    => '/api/categories/1',
            'price'       => 125,
            'state'       => 'draft',
            'createdAt'   => '2022-12-11T09:17:00.047Z',
            'publishedAt' => '2022-12-10T09:17:59.047Z',
        ]]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context'    => '/api/contexts/Advert',
            '@type'       => 'Advert',
            'title'       => 'string',
            'content'     => 'string',
            'author'      => 'string',
            'email'       => 'user@example.com',
            'category'    => '/api/categories/1',
            'price'       => 125,
            'state'       => 'draft',
            'createdAt'   => '2022-12-11T09:17:00+00:00',
            'publishedAt' => '2022-12-10T09:17:59+00:00'
        ]);
        $this->assertMatchesRegularExpression('~^/api/adverts/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(Advert::class);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testGetAllAdvert() : void
    {
        $response = static::createClient()->request('GET', '/api/adverts');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testGetByIdAdvert() : void
    {
        $response = static::createClient()->request('GET', '/api/adverts/1');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
    }
}