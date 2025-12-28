// Prerequisite check for GLPI version and error logging (GLPI 11+ compliance)
function plugin_geststock_check_prerequisites() {
   $min_version = '11.0';
   $max_version = '12.0';
   $glpi_version = null;
   $glpi_root = '/var/www/glpi';
   $version_dir = $glpi_root . '/version';
   if (is_dir($version_dir)) {
      $files = scandir($version_dir, SCANDIR_SORT_DESCENDING);
      foreach ($files as $file) {
         if ($file[0] !== '.' && preg_match('/^\d+\.\d+\.\d+$/', $file)) {
            $glpi_version = $file;
            break;
         }
      }
   }
   if ($glpi_version === null && defined('GLPI_VERSION')) {
      $glpi_version = GLPI_VERSION;
   }
   // Load Toolbox if not loaded
   if (!class_exists('Toolbox') && file_exists($glpi_root . '/src/Toolbox.php')) {
      require_once $glpi_root . '/src/Toolbox.php';
   }
   // Fallback error logger if Toolbox::logInFile is unavailable
   if (!function_exists('geststock_fallback_log')) {
      function geststock_fallback_log($msg) {
         $logfile = __DIR__ . '/geststock_error.log';
         $date = date('Y-m-d H:i:s');
         file_put_contents($logfile, "[$date] $msg\n", FILE_APPEND);
      }
   }
   if ($glpi_version === null) {
      $logmsg = '[setup.php:plugin_geststock_check_prerequisites] ERROR: GLPI version not detected.';
      if (class_exists('Toolbox') && method_exists('Toolbox', 'logInFile')) {
         Toolbox::logInFile('geststock', $logmsg);
      } else {
         geststock_fallback_log($logmsg);
      }
      echo "This plugin requires GLPI >= $min_version";
      return false;
   }
   if (version_compare($glpi_version, $min_version, '<')) {
      $logmsg = sprintf(
         'ERROR [setup.php:plugin_geststock_check_prerequisites] GLPI version %s is less than required minimum %s, user=%s',
         $glpi_version, $min_version, $_SESSION['glpiname'] ?? 'unknown'
      );
      if (class_exists('Toolbox') && method_exists('Toolbox', 'logInFile')) {
         Toolbox::logInFile('geststock', $logmsg);
      } else {
         geststock_fallback_log($logmsg);
      }
      echo "This plugin requires GLPI >= $min_version";
      return false;
   }
   if (version_compare($glpi_version, $max_version, '>')) {
      $logmsg = sprintf(
         'ERROR [setup.php:plugin_geststock_check_prerequisites] GLPI version %s is greater than supported maximum %s, user=%s',
         $glpi_version, $max_version, $_SESSION['glpiname'] ?? 'unknown'
      );
      if (class_exists('Toolbox') && method_exists('Toolbox', 'logInFile')) {
         Toolbox::logInFile('geststock', $logmsg);
      } else {
         geststock_fallback_log($logmsg);
      }
      echo "This plugin requires GLPI <= $max_version";
      return false;
   }
   return true;
}
<?php
if (!defined('GLPI_ROOT')) { define('GLPI_ROOT', realpath(__DIR__ . '/../..')); }
/*
 -------------------------------------------------------------------------
 LICENSE

 This file is part of GestStock plugin for GLPI.

 GestStock is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 GestStock is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with GestStock. If not, see <http://www.gnu.org/licenses/>.

 @package   geststock
 @author    Nelly Mahu-Lasson
 @copyright Copyright (c) 2017-2022 GestStock plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link
 @since     version 1.0.0
 --------------------------------------------------------------------------
 */

if (!defined("PLUGIN_GESTSTOCK_UPLOAD_DIR")) {
   define("PLUGIN_GESTSTOCK_UPLOAD_DIR", GLPI_PLUGIN_DOC_DIR."/geststock/upload/");
}

function plugin_init_geststock() {
   global $PLUGIN_HOOKS,$CFG_GLPI;

   $PLUGIN_HOOKS["helpdesk_menu_entry"]['geststock'] = true;

   $PLUGIN_HOOKS['csrf_compliant']['geststock'] = true;

   Plugin::registerClass('PluginGeststockProfile', ['addtabon' => 'Profile']);

   $PLUGIN_HOOKS['pre_item_update']['geststock'] = ['Ticket' => ['PluginGeststockTicket', 'beforeUpdate']];
   $PLUGIN_HOOKS['item_update']['geststock']     = ['Ticket' => ['PluginGeststockTicket', 'afterUpdate']];

   $plugin = new Plugin();
   if ($plugin->isActivated("geststock")) {
      $PLUGIN_HOOKS['config_page']['geststock'] = 'front/config.form.php';
   }
   include_once(Plugin::getPhpDir('geststock')."/inc/reservation.class.php");

   if ($plugin->isActivated("simcard")) {
      PluginGeststockReservation::registerType('PluginSimcardSimcard');
   }

   $type = new PluginGeststockReservation();
   foreach ($type::$types as $key) {
      $mod = $key."Model";
      Plugin::registerClass('PluginGeststockSpecification', ['addtabon' => $mod]);
   }

   $PLUGIN_HOOKS['change_profile']['geststock']   = ['PluginGeststockProfile','initProfile'];

   if (Session::getLoginUserID()) {
      if (Session::haveRight("plugin_geststock", READ)) {
         $PLUGIN_HOOKS['menu_toadd']['geststock'] = ['tools' => 'PluginGeststockMenu'];
         Plugin::registerClass('PluginGeststockReservation', ['addtabon' => 'Ticket']);
      }
      $PLUGIN_HOOKS['use_massive_action']['geststock'] = 1;
   }

   $PLUGIN_HOOKS['plugin_pdf']['PluginGeststockReservation'] = 'PluginGeststockReservationPDF';

   $PLUGIN_HOOKS['post_init']['geststock'] = 'plugin_geststock_postinit';
}


// Get the name and the version of the plugin - Needed
function plugin_version_geststock() {

   return ['name'           => __('Stock gestion', 'geststock'),
           'version'        => '2.1.1',
           'author'         => 'Nelly Mahu-Lasson',
           'license'        => 'GPLv3+',
           'homepage'       => 'https://github.com/yllen/geststock',
           'page'           => "/front/reservation.php",
           'requirements'   => ['glpi' => ['min' => '11.0',
                                           'max' => '12.0']]];
}


function plugin_geststock_check_config() {
   return true;
}

