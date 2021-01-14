<?php
/**
 * Original credits below.
 *
 * Modified methods to return "$this" in order to be chainable, eg:
 *
 *   $fmt = new ConsoleFormatter();
 *   echo $fmt->setForeground('red')->setOption('bold')->apply($text);
 *
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Formatter style class for defining styles.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @api
 */
class ConsoleFormatter
{
  private static $availableForegroundColors = [
    'black'   => ['set' => 30, 'unset' => 39],
    'red'     => ['set' => 31, 'unset' => 39],
    'green'   => ['set' => 32, 'unset' => 39],
    'yellow'  => ['set' => 33, 'unset' => 39],
    'blue'    => ['set' => 34, 'unset' => 39],
    'magenta' => ['set' => 35, 'unset' => 39],
    'cyan'    => ['set' => 36, 'unset' => 39],
    'white'   => ['set' => 37, 'unset' => 39],
  ];
  private static $availableBackgroundColors = [
    'black'   => ['set' => 40, 'unset' => 49],
    'red'     => ['set' => 41, 'unset' => 49],
    'green'   => ['set' => 42, 'unset' => 49],
    'yellow'  => ['set' => 43, 'unset' => 49],
    'blue'    => ['set' => 44, 'unset' => 49],
    'magenta' => ['set' => 45, 'unset' => 49],
    'cyan'    => ['set' => 46, 'unset' => 49],
    'white'   => ['set' => 47, 'unset' => 49],
  ];
  private static $availableOptions = [
    'bold'       => ['set' => 1, 'unset' => 22],
    'underscore' => ['set' => 4, 'unset' => 24],
    'blink'      => ['set' => 5, 'unset' => 25],
    'reverse'    => ['set' => 7, 'unset' => 27],
    'conceal'    => ['set' => 8, 'unset' => 28],
  ];

  private $foreground;
  private $background;
  private $options = [];

  /**
   * Initializes output formatter style.
   *
   * @param string|null $foreground The style foreground color name
   * @param string|null $background The style background color name
   * @param array       $options    The style options
   *
   * @api
   */
  public function __construct($foreground = null, $background = null, array $options = [])
  {
    if (null !== $foreground) {
      $this->setForeground($foreground);
    }
    if (null !== $background) {
      $this->setBackground($background);
    }
    if (count($options)) {
      $this->setOptions($options);
    }
  }

  /**
   * Sets style foreground color.
   *
   * @param string|null $color The color name
   *
   * @throws Exception When the color name isn't defined
   *
   * @api
   */
  public function setForeground($color = null)
  {
    if (null === $color) {
      $this->foreground = null;

      return;
    }

    if (!isset(self::$availableForegroundColors[$color])) {
      throw new Exception(sprintf(
          'Invalid foreground color specified: "%s". Expected one of (%s)',
          $color,
          implode(', ', array_keys(self::$availableForegroundColors))
      ));
    }

    $this->foreground = self::$availableForegroundColors[$color];

    return $this;
  }

  /**
   * Sets style background color.
   *
   * @param string|null $color The color name
   *
   * @throws Exception When the color name isn't defined
   *
   * @api
   */
  public function setBackground($color = null)
  {
    if (null === $color) {
      $this->background = null;

      return $this;
    }

    if (!isset(self::$availableBackgroundColors[$color])) {
      throw new Exception(sprintf(
          'Invalid background color specified: "%s". Expected one of (%s)',
          $color,
          implode(', ', array_keys(self::$availableBackgroundColors))
      ));
    }

    $this->background = self::$availableBackgroundColors[$color];

    return $this;
  }

  /**
   * Sets some specific style option.
   *
   * @param string $option The option name
   *
   * @throws Exception When the option name isn't defined
   *
   * @api
   */
  public function setOption($option)
  {
    if (!isset(self::$availableOptions[$option])) {
      throw new Exception(sprintf(
          'Invalid option specified: "%s". Expected one of (%s)',
          $option,
          implode(', ', array_keys(self::$availableOptions))
      ));
    }

    if (false === array_search(self::$availableOptions[$option], $this->options)) {
      $this->options[] = self::$availableOptions[$option];
    }

    return $this;
  }

  /**
   * Unsets some specific style option.
   *
   * @param string $option The option name
   *
   * @throws Exception When the option name isn't defined
   */
  public function unsetOption($option)
  {
    if (!isset(self::$availableOptions[$option])) {
      throw new Exception(sprintf(
        'Invalid option specified: "%s". Expected one of (%s)',
        $option,
        implode(', ', array_keys(self::$availableOptions))
      ));
    }

    $pos = array_search(self::$availableOptions[$option], $this->options);
    if (false !== $pos) {
      unset($this->options[$pos]);
    }

    return $this;
  }

  /**
   * Sets multiple style options at once.
   *
   * @param array $options
   */
  public function setOptions(array $options)
  {
    $this->options = [];

    foreach ($options as $option) {
      $this->setOption($option);
    }

    return $this;
  }

  /**
   * Applies the style to a given text.
   *
   * @param string $text The text to style
   *
   * @return string
   */
  public function apply($text)
  {
    $setCodes = [];
    $unsetCodes = [];

    if (null !== $this->foreground) {
      $setCodes[] = $this->foreground['set'];
      $unsetCodes[] = $this->foreground['unset'];
    }
    if (null !== $this->background) {
      $setCodes[] = $this->background['set'];
      $unsetCodes[] = $this->background['unset'];
    }
    if (count($this->options)) {
      foreach ($this->options as $option) {
        $setCodes[] = $option['set'];
        $unsetCodes[] = $option['unset'];
      }
    }

    if (0 === count($setCodes)) {
      return $text;
    }

    return sprintf("\033[%sm%s\033[%sm", implode(';', $setCodes), $text, implode(';', $unsetCodes));
  }
}
