<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\TreasuryClient\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use MichaelDrennen\Balcony\TreasuryClient\Client;
use MichaelDrennen\Balcony\TreasuryClient\Config;
use MichaelDrennen\Balcony\TreasuryClient\Exceptions\BalconyClientException;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    private function fixture(): string
    {
        return (string) file_get_contents(__DIR__ . '/Fixtures/sample_response.json');
    }

    private function clientWith(MockHandler $mock): Client
    {
        $http = new GuzzleClient(['handler' => HandlerStack::create($mock), 'http_errors' => false]);

        return new Client(new Config(), $http);
    }

    public function testParsesFixtureAndSkipsNullRates(): void
    {
        $dtos = (new Client(new Config()))->parse($this->fixture());

        $this->assertCount(3, $dtos);
        $this->assertSame('2026-09-30', $dtos[0]->recordDate->format('Y-m-d'));
        $this->assertSame('Treasury Bills', $dtos[0]->securityDesc);
        $this->assertSame(4.821, $dtos[0]->rate);
    }

    public function testFetchUsesMockedHttp(): void
    {
        $client = $this->clientWith(new MockHandler([new Response(200, [], $this->fixture())]));

        $this->assertCount(3, $client->fetchRates());
    }

    public function testRateLimitThrows(): void
    {
        $client = $this->clientWith(new MockHandler([new Response(429, ['Retry-After' => '12'], '{}')]));

        try {
            $client->fetchRates();
            $this->fail('Expected BalconyClientException.');
        } catch (BalconyClientException $e) {
            $this->assertTrue($e->isRateLimited());
            $this->assertSame(12, $e->retryAfterSeconds);
        }
    }

    public function testMalformedPayloadThrows(): void
    {
        $this->expectException(BalconyClientException::class);
        (new Client(new Config()))->parse('{"meta":{}}');
    }

    public function testInvalidStartDateRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Config(startDate: '01/01/2026');
    }

    public function testParseRateCoercions(): void
    {
        $this->assertNull(Client::parseRate(null));
        $this->assertNull(Client::parseRate('abc'));
        $this->assertSame(4.5, Client::parseRate('4.5'));
    }
}
