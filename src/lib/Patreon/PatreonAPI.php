<?php

namespace Koohii\Patreon;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use RuntimeException;

/**
 * Patreon API v2 client.
 *
 * Handles authenticated requests to the Patreon v2 API using a creator access token.
 * Responses follow the JSON:API spec structure (data/meta/links).
 */
class PatreonAPI
{
  private const BASE_URL = 'https://www.patreon.com/api/oauth2/v2';

  private Client $client;

  /**
   * @param array{
   *   CLIENT_ID: string,
   *   CLIENT_SECRET: string,
   *   CREATOR_ACCESS_TOKEN: string,
   *   CREATOR_REFRESH_TOKEN: string
   * } $credentials
   */
  public function __construct(array $credentials)
  {
    $this->client = new Client([
      'headers' => [
        'Authorization' => 'Bearer '.$credentials['CREATOR_ACCESS_TOKEN'],
      ],
    ]);
  }

  /**
   * Returns all campaigns for the authenticated creator.
   *
   * @return array<int, array<string, mixed>> Array of JSON:API resource objects
   */
  public function getCampaigns(): array
  {
    $response = $this->get('/campaigns', [
      'fields[campaign]' => 'creation_name,name,patron_count,published_at,currency,url',
    ]);

    return $response['data'] ?? [];
  }

  /**
   * Returns all members for a campaign, automatically following cursor pagination.
   *
   * @return array<int, array<string, mixed>> Array of JSON:API resource objects
   */
  public function getAllCampaignMembers(string $campaignId): array
  {
    $members = [];
    $cursor  = null;

    do {
      $params = [
        'fields[member]' => 'full_name,email,patron_status,campaign_lifetime_support_cents',
        'page[count]'    => 500,
      ];

      if ($cursor !== null) {
        $params['page[cursor]'] = $cursor;
      }

      $response = $this->get('/campaigns/'.$campaignId.'/members', $params);

      $members = array_merge($members, $response['data'] ?? []);

      $cursor = $response['meta']['pagination']['cursors']['next'] ?? null;
    } while ($cursor !== null);

    return $members;
  }

  /**
   * Executes a GET request and returns the decoded JSON response.
   *
   * @param array<string, mixed> $params Query parameters
   *
   * @return array<string, mixed>
   *
   * @throws RuntimeException On HTTP errors with a descriptive message
   */
  private function get(string $path, array $params = []): array
  {
    try {
      $response = $this->client->get(self::BASE_URL.$path, ['query' => $params]);

      return json_decode((string) $response->getBody(), true);
    } catch (ClientException $e) {
      $code    = $e->getResponse()->getStatusCode();
      $message = match ($code) {
        401     => 'Unauthorized: check your CREATOR_ACCESS_TOKEN',
        403     => 'Forbidden: insufficient API scope or permissions',
        429     => 'Rate limited: too many requests, try again later',
        default => sprintf('Unexpected HTTP error (HTTP %d)', $code),
      };

      throw new RuntimeException($message, $code);
    } catch (ServerException $e) {
      $code = $e->getResponse()->getStatusCode();

      throw new RuntimeException(sprintf('Patreon server error (HTTP %d)', $code), $code);
    }
  }
}
