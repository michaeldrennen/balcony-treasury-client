<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\TreasuryClient;

use DateTimeImmutable;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use MichaelDrennen\Balcony\TreasuryClient\DTO\TreasuryRateDTO;
use MichaelDrennen\Balcony\TreasuryClient\Exceptions\BalconyClientException;

final class Client
{
    private readonly ClientInterface $http;

    public function __construct(
        private readonly Config $config,
        ?ClientInterface $http = null,
    ) {
        $this->http = $http ?? new GuzzleClient([
            'timeout' => $config->timeoutSeconds,
            'http_errors' => false,
        ]);
    }

    /**
     * @return TreasuryRateDTO[]
     */
    public function fetchRates(): array
    {
        $query = [
            'filter' => 'record_date:gte:' . $this->config->startDate,
            'page[size]' => $this->config->pageSize,
            'sort' => '-record_date',
        ];

        try {
            $response = $this->http->request('GET', $this->config->baseUrl, ['query' => $query]);
        } catch (GuzzleException $e) {
            throw BalconyClientException::transport($e->getMessage(), $e);
        }

        $status = $response->getStatusCode();

        if ($status === 429) {
            $retryHeader = $response->getHeaderLine('Retry-After');
            throw BalconyClientException::rateLimited(is_numeric($retryHeader) ? (int) $retryHeader : null);
        }

        if ($status >= 400) {
            throw BalconyClientException::httpError($status, (string) $response->getBody());
        }

        return $this->parse((string) $response->getBody());
    }

    /**
     * Parse a raw Fiscal Data JSON payload. Rows without a numeric rate are skipped.
     *
     * @return TreasuryRateDTO[]
     */
    public function parse(string $json): array
    {
        $decoded = json_decode($json, true);

        if (!is_array($decoded) || !isset($decoded['data']) || !is_array($decoded['data'])) {
            throw BalconyClientException::malformed('Treasury payload is missing the data array.');
        }

        $rows = [];

        foreach ($decoded['data'] as $row) {
            if (!is_array($row)) {
                continue;
            }

            $recordDate = self::parseDate($row['record_date'] ?? null);
            $rate = self::parseRate($row['avg_interest_rate_amt'] ?? null);

            if ($recordDate === null || $rate === null) {
                continue;
            }

            $rows[] = new TreasuryRateDTO(
                recordDate: $recordDate,
                securityType: (string) ($row['security_type_desc'] ?? ''),
                securityDesc: (string) ($row['security_desc'] ?? ''),
                rate: $rate,
            );
        }

        return $rows;
    }

    public static function parseRate(mixed $raw): ?float
    {
        if (is_int($raw) || is_float($raw)) {
            return (float) $raw;
        }

        if (!is_string($raw)) {
            return null;
        }

        $trimmed = trim($raw);

        return ($trimmed !== '' && is_numeric($trimmed)) ? (float) $trimmed : null;
    }

    public static function parseDate(mixed $raw): ?DateTimeImmutable
    {
        if (!is_string($raw)) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $raw);

        return ($date !== false && $date->format('Y-m-d') === $raw) ? $date : null;
    }
}
