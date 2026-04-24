<?php

/**
 * Peer class for the patreon_members table.
 *
 * This table caches Patreon campaign member data synced via the Patreon API v2.
 *
 * Methods:
 *  countActivePatrons()       Total count of active patrons
 *  getActivePublicPatrons()   Active patrons who have not set their pledges to private
 *  getFormerPublicPatrons()   Former patrons who have not set their pledges to private
 *  countFormerAnonymous()     Count of former patrons with pledges set to private
 */
class PatreonMembersPeer extends coreDatabaseTable
{
  protected $tableName = 'patreon_members';

  public const STATUS_ACTIVE = 'active_patron';

  /**
   * This function must be copied in each peer class.
   */
  public static function getInstance(): self
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }

  /**
   * Total count of active patrons (public + anonymous).
   */
  public static function countActivePatrons(): int
  {
    return (int) self::getInstance()->count('patron_status = ?', self::STATUS_ACTIVE);
  }

  /**
   * Returns active patrons who have not set their pledges to private,
   * ordered by pledge start date then name.
   *
   * @return array<array{full_name: string, pledge_start: string}>
   */
  public static function getActivePublicPatrons(): array
  {
    $select = self::getInstance()->select(['full_name', 'pledge_start'])
      ->where('patron_status = ? AND hide_pledges = 0', self::STATUS_ACTIVE)
      ->order(['pledge_start ASC', 'full_name ASC'])
    ;

    return self::getInstance()->getDb()->fetchAll($select);
  }

  /**
   * Returns former patrons who have not set their pledges to private,
   * ordered alphabetically by name.
   *
   * @return array<array{full_name: string}>
   */
  public static function getFormerPublicPatrons(): array
  {
    $select = self::getInstance()->select('full_name')
      ->where('patron_status != ? AND hide_pledges = 0', self::STATUS_ACTIVE)
      ->order('full_name ASC')
    ;

    return self::getInstance()->getDb()->fetchAll($select);
  }

  /**
   * Count of former patrons who have set their pledges to private.
   */
  public static function countFormerAnonymous(): int
  {
    return (int) self::getInstance()->count('patron_status != ? AND hide_pledges = 1', self::STATUS_ACTIVE);
  }
}
