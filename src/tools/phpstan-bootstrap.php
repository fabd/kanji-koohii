<?php
/**
 * PHPStan bootstrap stubs.
 *
 * Defines constants that are set at runtime inside method bodies and
 * therefore cannot be resolved by PHPStan's static analysis.
 */

// Defined in koohiiConfiguration::initialize() via define()
define('KK_ENV_DEV', false);
define('KK_ENV_PROD', false);
define('KK_ENV_FORK', false);
define('CJ_MODE', 'rtk');

// Symfony 1.x expects a concrete ProjectConfiguration class by convention;
// referenced internally when resolving sfProjectConfiguration subclasses.
class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup() {}
}
