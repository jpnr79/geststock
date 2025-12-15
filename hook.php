<?php
if (!defined('GLPI_ROOT')) { define('GLPI_ROOT', realpath(__DIR__ . '/../..')); }

use Glpi\Toolbox\PluginMigration;

// It's good practice to have an init function, even if it's empty.
if (!function_exists('plugin_init_geststock')) {
    function plugin_init_geststock() {
       return true;
    }
}

/**
 * Install hook
 *
 * @return boolean
 */
function plugin_geststock_install()
{
   try {
      include_once __DIR__ . '/inc/migration.class.php';
      $migration = new PluginGeststockMigration(true);
      
      // Execute migration steps
      $steps = PluginGeststockMigration::getMigrationSteps();
      foreach ($steps as $version => $method) {
          if (method_exists($migration, $method)) {
              $migration->$method();
          }
      }
      
      return true;
   } catch (Exception $e) {
      error_log("Geststock install error: " . $e->getMessage());
      return false;
   }
}

/**
 * Uninstall hook
 *
 * @return boolean
 */
function plugin_geststock_uninstall()
{
   include_once __DIR__ . '/inc/migration.class.php';

   $migration = new PluginGeststockMigration(false); // Pass false to skip parent constructor
   $migration->uninstall();

   return true;
}

