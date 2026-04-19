<?php
/**
 * Patreon admin CLI tool.
 *
 * USAGE
 *
 *   Show campaign info (use this first to obtain the campaign ID):
 *   $ php batch/admin/patreon.php --campaign
 *
 *   List all patrons (active and former):
 *   $ php batch/admin/patreon.php --members
 */

require_once realpath(dirname(__FILE__).'/../..').'/lib/Batch/Command_CLI.php';

use Koohii\Patreon\PatreonAPI;

class Patreon_CLI extends Command_CLI
{
  public function __construct()
  {
    parent::__construct([
      'campaign' => 'Fetch and display campaign info',
      'members'  => 'Fetch and display all patrons (active and former)',
    ]);

    /** @var array{CLIENT_ID: string, CLIENT_SECRET: string, CREATOR_ACCESS_TOKEN: string, CREATOR_REFRESH_TOKEN: string, CAMPAIGN_ID: string} */
    $credentials = require SF_ROOT_DIR.'/.secrets/koohii_patreon.php';
    $api         = new PatreonAPI($credentials);

    try {
      if ($this->getFlag('campaign')) {
        $this->showCampaigns($api);
      } elseif ($this->getFlag('members')) {
        $this->showMembers($api, $credentials['CAMPAIGN_ID']);
      }
    } catch (RuntimeException $e) {
      $this->throwError($e->getMessage());
    }
  }

  private function showCampaigns(PatreonAPI $api): void
  {
    $campaigns = $api->getCampaigns();

    if (empty($campaigns)) {
      echo "No campaigns found.\n";

      return;
    }

    foreach ($campaigns as $campaign) {
      $attrs = $campaign['attributes'];
      echo "\n";
      echo sprintf("  Campaign ID:  %s\n", $campaign['id']);
      echo sprintf("  Name:         %s\n", $attrs['name'] ?? $attrs['creation_name'] ?? '(unknown)');
      echo sprintf("  Patron count: %d\n", $attrs['patron_count'] ?? 0);
      echo sprintf("  Currency:     %s\n", $attrs['currency'] ?? '(unknown)');
      echo sprintf("  URL:          %s\n", $attrs['url'] ?? '(unknown)');
      echo sprintf("  Published:    %s\n", $attrs['published_at'] ?? '(unknown)');
      echo "\n";
    }
  }

  private function showMembers(PatreonAPI $api, string $campaignId): void
  {
    if ($campaignId === '') {
      $this->throwError(
        "CAMPAIGN_ID is not set.\nRun --campaign first to get your campaign ID."
      );
    }

    $members = $api->getAllCampaignMembers($campaignId);

    $activeCount = 0;
    $formerCount = 0;
    foreach ($members as $member) {
      $status = $member['attributes']['patron_status'] ?? '';
      if ($status === 'active_patron') {
        $activeCount++;
      } else {
        $formerCount++;
      }
    }

    $fmt = $this->formatter;

    echo "\n";
    echo sprintf(
      "  %s  |  %s  |  Total: %d\n",
      $fmt->setForeground('green')->setOption('bold')->apply(sprintf('Active: %d', $activeCount)),
      $fmt->setForeground('yellow')->apply(sprintf('Former/Declined: %d', $formerCount)),
      count($members)
    );
    echo "\n";

    $colName     = 30;
    $colEmail    = 36;
    $colStatus   = 8;
    $colLifetime = 10;
    $colHidden   = 6;

    $header = sprintf(
      '  %-'.$colName.'s  %-'.$colEmail.'s  %-'.$colStatus.'s  %-'.$colLifetime.'s  %s',
      'NAME',
      'EMAIL',
      'STATUS',
      'LIFETIME',
      'HIDDEN'
    );
    echo $fmt->setOption('bold')->apply($header)."\n";
    echo sprintf(
      "  %s  %s  %s  %s  %s\n",
      str_repeat('-', $colName),
      str_repeat('-', $colEmail),
      str_repeat('-', $colStatus),
      str_repeat('-', $colLifetime),
      str_repeat('-', $colHidden)
    );

    foreach ($members as $member) {
      // print_r($member);
      $attrs    = $member['attributes'];
      $status   = $attrs['patron_status'] ?? 'unknown';
      $name     = mb_substr($attrs['full_name'] ?? '(unknown)', 0, $colName);
      $email    = mb_substr($attrs['email'] ?? '(no email)', 0, $colEmail);
      $lifetime = ($attrs['campaign_lifetime_support_cents'] ?? 0) / 100;

      // Pad the status text before applying color so ANSI codes don't break alignment.
      $statusText = match ($status) {
        'active_patron'   => 'Active',
        'former_patron'   => 'Former',
        'declined_patron' => 'Declined',
        default           => $status,
      };
      $statusColor = match ($status) {
        'active_patron'   => 'green',
        'former_patron'   => 'yellow',
        'declined_patron' => 'red',
        default           => null,
      };
      $statusPadded = str_pad($statusText, $colStatus);
      $statusLabel  = $statusColor !== null
        ? $fmt->setForeground($statusColor)->apply($statusPadded)
        : $statusPadded;

      $hiddenText  = ($attrs['hide_pledges'] ?? false) ? 'yes' : '';
      $hiddenLabel = $hiddenText !== '' ? $fmt->setForeground('red')->apply($hiddenText) : '';

      echo sprintf(
        '  %-'.$colName.'s  %-'.$colEmail.'s  %s  $%'.($colLifetime - 1).'.2f  %s'."\n",
        $name,
        $email,
        $statusLabel,
        $lifetime,
        $hiddenLabel
      );
    }

    echo "\n";
  }
}

$cmd = new Patreon_CLI();
