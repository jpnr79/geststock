<?php

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
   // Ensure the core PluginMigration class is loaded.
   if (!class_exists(PluginMigration::class)) {
      return false;
   }

   include_once __DIR__ . '/inc/migration.class.php';
   PluginMigration::makeMigration('geststock', PluginGeststockMigration::class);
   return true;
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

