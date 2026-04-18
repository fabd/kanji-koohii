# coreDatabase ORM — Developer Reference

Custom Zend_Db-inspired database abstraction layer. The concrete implementation is `coreDatabaseMySQL`. Peer classes extend `coreDatabaseTable` to encapsulate table-specific logic.

## Getting the database handle

```php
$db = kk_get_database();
```

## Peer classes

Every DB table has a peer class in `src/lib/peer/`. The class name must match the pattern `<TableName>Peer` (case-insensitive table name derived automatically). Peer classes extend `coreDatabaseTable` and use the singleton pattern.

```php
class ReviewsPeer extends coreDatabaseTable
{
  protected $tableName = 'reviews';   // optional: override auto-derived name

  protected $columns = [              // needed for auto-timestamp handling
    'userid', 'ucs_id', 'created_on', 'updated_on', ...
  ];

  /** Must be copied verbatim into each peer class */
  public static function getInstance(): self
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }
}
```

Usage:

```php
$peer = ReviewsPeer::getInstance();
$db   = $peer->getDb();     // returns the coreDatabase handle
```

---

## SELECT queries

### Building a select via the peer

`$peer->select($columns)` returns a `coreDatabaseSelect` pre-populated with `FROM <tableName>`.

```php
// Select all columns (*)
$select = ReviewsPeer::getInstance()->select();

// Single column
$select = ReviewsPeer::getInstance()->select('ucs_id');

// Multiple columns
$select = ReviewsPeer::getInstance()->select(['ucs_id', 'leitnerbox', 'totalreviews']);
```

### Building a select via the database handle

Use when you need to specify the FROM table manually (e.g. cross-table queries).

```php
$db = kk_get_database();
$select = $db->select(['count' => 'SUM(totalreviews)'])->from('reviews');
```

### Column aliases

Array keys become SQL `AS` aliases when they are strings:

```php
$select = $peer->select([
  'ucs'           => 'reviews.ucs_id',     // → reviews.ucs_id AS ucs
  'ts_lastreview' => 'UNIX_TIMESTAMP(lastreview)',
  'leitnerbox',                             // → leitnerbox (no alias)
  '*',
]);
```

### WHERE clause

Bind parameters with `?` placeholders — values are automatically quoted.

```php
// Single value
$select->where('ucs_id = ?', $ucsId);

// Multiple values (pass array)
$select->where('userid = ? AND leitnerbox > ?', [$userId, 1]);

// No parameters
$select->where('totalreviews > 0');

// SQL expression as parameter (not quoted)
$sqlNow = new coreDbExpr('NOW()');
$select->where('expiredate <= ?', $sqlNow);

// IN clause (integer values only)
$select->whereIn('ucs_id', [0x5c71, 0x5ddd, 0x5973]);
```

### JOINs

```php
// JOIN ... ON condition
$select->join('kanjis', 'kanjis.ucs_id = reviews.ucs_id');

// JOIN table AS alias
$select->join(['k' => 'kanjis'], 'kanjis.ucs_id = reviews.ucs_id');

// JOIN ... USING(col)
$select->joinUsing('kanjis', 'ucs_id');

// LEFT JOIN ... ON condition
$select->joinLeft('sequences', 'sequences.ucs_id = kanjis.ucs_id');

// LEFT JOIN ... USING(col1, col2)
$select->joinLeftUsing('custkeywords', ['ucs_id', 'userid']);
```

### ORDER, GROUP, LIMIT

```php
$select->order('leitnerbox DESC');
$select->order(['leitnerbox DESC', 'ucs_id ASC']);

$select->group('leitnerbox');
$select->group(['leitnerbox', 'due ASC']);

$select->limit(10);                // LIMIT 10
$select->limit(10, 20);            // LIMIT 20, 10  (offset, count)
$select->limitPage($pageNum, 25);  // zero-based page number
```

### HAVING

```php
$select->having('COUNT(*) > ?', 5);
```

### Chaining

All `coreDatabaseSelect` methods return `$this`, so calls can be chained:

```php
$select = $peer->select(['ucs_id', 'leitnerbox'])
  ->where('userid = ?', $userId)
  ->where('totalreviews > 0')
  ->order('leitnerbox')
  ->limit(50);
```

### Adding columns after construction

```php
$select = $peer->select();
$select->columns(['count' => 'COUNT(*)']);
```

### Resetting parts

```php
$select->reset();                           // reset everything
$select->reset(coreDatabaseSelect::WHERE);  // reset only WHERE clauses
$select->reset(coreDatabaseSelect::ORDER);
```

---

## Executing queries and fetching results

### Execute then iterate

```php
$select->query();                     // or $db->query($select)
while ($row = $db->fetch()) { ... }   // FETCH_ASSOC (default)
```

### fetch modes

```php
$db->fetch();                          // associative array (default)
$db->fetch(coreDatabase::FETCH_OBJ);  // stdClass object
$db->fetch(coreDatabase::FETCH_NUM);  // numeric array

// Or set a persistent default
$db->setFetchMode(coreDatabase::FETCH_OBJ);
```

### fetchObject — single row as object

After a `query()`, fetch one row as an object:

```php
$select->query();
$obj = $db->fetchObject();   // stdClass with column properties
// $obj->leitnerbox, $obj->ucs_id, etc.

// Custom class
$obj = $db->fetchObject(MyRowClass::class);
```

### fetchOne — single scalar value

```php
// Returns first column of first row (for COUNT, SUM, etc.)
$count = $db->fetchOne($select);
$count = $db->fetchOne('SELECT COUNT(*) FROM reviews WHERE userid = ?', $userId);
```

### fetchRow — single row as assoc array

```php
$row = $db->fetchRow($select);
$row = $db->fetchRow('SELECT * FROM users WHERE userid = ?', $userId);
// returns false if no row
```

### fetchAll — all rows as array of assoc arrays

```php
$rows = $db->fetchAll($select);
foreach ($rows as $row) {
  echo $row['ucs_id'];
}
```

### fetchCol — first column of every row

```php
$ids = $db->fetchCol($select);
// returns ['12345', '20999', ...]
```

---

## INSERT / UPDATE / DELETE

### insert

```php
// Basic insert
$peer->insert([
  'userid'  => $userId,
  'ucs_id'  => $ucsId,
  'keyword' => $keyword,
]);

// SQL expressions are not quoted
$db->insert('reviews', [
  'userid'     => $userId,
  'created_on' => new coreDbExpr('NOW()'),
]);

// Get auto-increment ID
$db->insert('stories', $data);
$newId = $db->lastInsertId();
```

`coreDatabaseTable::insert()` automatically sets `created_on` and `updated_on` timestamps when those columns are declared in `$this->columns`.

### update

```php
// Via peer (auto-sets updated_on if declared)
$peer->update(
  ['leitnerbox' => 2, 'expiredate' => new coreDbExpr('NOW()')],
  'userid = ? AND ucs_id = ?',
  [$userId, $ucsId]
);

// Via database handle
$db->update('reviews',
  ['leitnerbox' => 2],
  'userid = ?',
  $userId
);
```

### delete

```php
// Via peer
$peer->delete('userid = ? AND ucs_id = ?', [$userId, $ucsId]);

// Via database handle
$db->delete('reviews', 'userid = ?', $userId);

// Delete all rows (no WHERE)
$peer->delete();
```

### replace (upsert)

`coreDatabaseTable::replace()` inserts or updates based on whether the row exists:

```php
$peer->replace(
  ['keyword' => $newKeyword],    // data to set
  ['userid' => $userId, 'ucs_id' => $ucsId]  // primary key(s)
);
```

---

## Raw queries

```php
$db->query('UPDATE reviews SET leitnerbox = 1 WHERE userid = ?', $userId);
$db->query('TRUNCATE reviews');
```

---

## count()

`coreDatabaseTable::count()` returns a row count for the table:

```php
$total = $peer->count();
$total = $peer->count('userid = ?', $userId);
$total = $peer->count('userid = ? AND leitnerbox > ?', [$userId, 1]);
```

---

## coreDbExpr — unquoted SQL expressions

Wrap any value you do **not** want auto-quoted:

```php
new coreDbExpr('NOW()')
new coreDbExpr('CURRENT_TIMESTAMP')
new coreDbExpr("DATE_ADD(NOW(), INTERVAL 7 DAY)")
```

---

## Typical peer method pattern

```php
public static function getCardsByUser(int $userId): array
{
  $select = self::getInstance()->select(['ucs_id', 'leitnerbox'])
    ->where('userid = ?', $userId)
    ->where('totalreviews > 0')
    ->order('leitnerbox');

  return self::getInstance()->getDb()->fetchAll($select);
}
```

---

## Debugging

Print the generated SQL:

```php
echo $select;           // __toString() returns the SQL string
// or
DBG::out($select);
```
