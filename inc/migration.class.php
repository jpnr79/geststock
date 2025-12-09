<?php

if (!defined('GLPI_ROOT')) {
    die('Sorry. You can\'t access this file directly');
}

class PluginGeststockMigration
{
    private $db;

    /**
     * @param bool $do_db_checks
     */
    public function __construct($do_db_checks = true)
    {
        global $DB;
        $this->db = $DB;
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
        //    $query = "CREATE TABLE `" . $table_name . "` (
        //        `id` INT(11) NOT NULL AUTO_INCREMENT,
        //        PRIMARY KEY (`id`)
        //    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        //    $this->db->doQuery($query);
        // }
    }

    /**
     * Uninstall logic.
     */
    public function uninstall(): void
    {
        $tables = [
            // 'glpi_plugin_geststock_example',
            // Add all your plugin's table names here.
        ];

        foreach ($tables as $table) {
            if ($this->db->tableExists($table)) {
                $this->db->doQuery("DROP TABLE IF EXISTS `" . $table . "`");
            }
        }
    }
}