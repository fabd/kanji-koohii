<?php
/**
 * This table links Patreon information with Koohii users, and the pledge level for perks/rewards.
 * 
 * Methods:
 *  updatePatron($data)       Create or update a Patreon patron info
 *  getPatronInfo($userId)    Return Patreon info given Koohii user id, or false
 *                            (note user could be a patron but is not linked yet so we don't know)
 *
 * 
 */

class PatreonPeer extends coreDatabaseTable
{
  protected $tableName = 'patreon';

  // this may be incorrect, no idea what a inactive patron looks like in the API output
  // we will assume for now that non zero amount is an active patron
  const PLEDGE_AMOUNT_ACTIVE = 100;

  // min. pledge amount (cents) for the reward that includes perks
  const PLEDGE_AMOUNT_PERKS = 500;

  /**
   * This function must be copied in each peer class.
   * @return self
   */
  public static function getInstance()
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }

  /**
   * Create / update Patreon info and:
   *
   *   - sets `is_active` if pledge amount is the min/. amount for perks
   * 
   * @param  [type] $data [description]
   * @return [type]       [description]
   */
  static public function updatePatron($data)
  {
    assert(isset($data["pa_id"]));

    // update flags
    $pa_amount_cents = isset($data['pa_amount_cents']) ? $data['pa_amount_cents'] :_0;
    $data['is_active'] = (int) ($pa_amount_cents >= self::PLEDGE_AMOUNT_ACTIVE);
    $data['has_perks'] = (int) ($pa_amount_cents >= self::PLEDGE_AMOUNT_PERKS);

    return self::getInstance()->replace($data, ['pa_id' => $data['pa_id']]);
  }

  /**
   * 
   * @param int $userId    Koohii user id
   *
   * @return array|false
   */
  static public function getPatronInfo($userId)
  {
    self::getInstance()->select('*')->where('userid = ?', $userId)->query();
    $db = self::getInstance()->getDb();
    return $db->fetch();
  }

  /**
   * Return list of active patrons (regardless if amount meets the perks/reward level).
   * 
   */
  static public function getPatronsList()
  {
    $db = self::getInstance()->getDb();
    //$select = self::getInstance()->select('pa_full_name')->where('is_active = 1');
    $select = $db->select(['u.username', 'p.userid', 'p.pa_full_name'])
      ->from(['p' => self::getInstance()->getName()])
      ->joinLeftUsing(['u' => 'users'], 'userid')
      ->where('is_active = 1');

    return $db->fetchAll($select);
  }
}
