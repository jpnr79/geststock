<?php

if (!defined('GLPI_ROOT')) {
    die('Sorry. You can\'t access this file directly');
}

class PluginGeststockMigration extends \Glpi\Toolbox\PluginMigration
{
    /**
     * @param bool $do_db_checks
     */
    public function __construct($do_db_checks = true)
    {
        // Overload the constructor to allow instantiation without DB connection for uninstall.
        if ($do_db_checks) {
            parent::__construct();
        }
    }

    public static function getMigrationSteps(): array
    {
        // Map your plugin versions to migration methods.
        return [
            '1.0.0' => 'migrationTo100',
        ];
    }

    /**
     * Migration to version 1.0.0.
     * Creates the initial database tables.
     */
    public function migrationTo100(): void
    {
        // TODO: Move your table creation logic here from 'plugins/geststock/inc/config.class.php'.
        // Example:
        // $table_name = 'glpi_plugin_geststock_example';
        // if (!$this->db->tableExists($table_name)) {
        //    $this->db->createTable(
        //        $table_name,
        //        "`id` INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)"
        //    );
        // }
    }

    /**
     * Uninstall logic.
     */
    public function uninstall(): void
    {
        global $DB; // Get DB connection for uninstall.

        $tables = [
            // 'glpi_plugin_geststock_example',
            // Add all your plugin's table names here.
        ];

        foreach ($tables as $table) {
            if ($DB->tableExists($table)) {
                $DB->dropTable($table);
            }
        }
    }
}