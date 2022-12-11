<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Category;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CategoryTest extends ApiTestCase
{
    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testCreateCategories() : void
    {
        $response = static::createClient()->request('POST', '/api/categories', [
            'json' => [
                'name' => 'Voiture'
            ]
        ]);
        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/Category',
            '@type'    => 'Category',
            'name'     => 'Voiture'
        ]);
        $this->assertMatchesRegularExpression('~^/api/categories/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(Category::class);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testGetAllCategories() : void
    {
        $response = static::createClient()->request('GET', '/api/categories');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testUpdateCategories() : void
    {
        $response = static::createClient()->request('PUT', '/api/categories/1', ['json' =>[
            'name' => 'moto',
        ]]);
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/api/contexts/Category',
            '@id'      => '/api/categories/1',
            '@type'    => 'Category',
        ]);
    }

    /*public function testDeleteCategories(){
        $response = static::createClient()->request('DELETE', 'api/categories/1');
        $this->assertResponseStatusCodeSame(204);
    }*/
}