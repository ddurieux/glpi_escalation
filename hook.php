<?php

/*
   ------------------------------------------------------------------------
   Plugin Escalation for GLPI
   Copyright (C) 2012-2017 by the Plugin Escalation for GLPI Development Team.

   https://github.com/ddurieux/glpi_escalation
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Escalation project.

   Plugin Escalation for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Escalation for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Escalation. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Escalation for GLPI
   @author    David Durieux
   @co-author
   @comment
   @copyright Copyright (c) 2011-2017 Plugin Escalation for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://github.com/ddurieux/glpi_escalation
   @since     2012

   ------------------------------------------------------------------------
 */

function plugin_escalation_install() {
   global $DB;

   $migration = new Migration(PLUGIN_ESCALATION_VERSION);

   if (!$DB->TableExists("glpi_plugin_escalation_groups_groups")) {
      $migration->displayMessage("Creation tables in database");
      $empty_sql = "plugin_escalation-empty.sql";
      $DB_file = GLPI_ROOT ."/plugins/escalation/install/mysql/$empty_sql";

      if (!$DB->runFile($DB_file)) {
         $migration->displayMessage("Error on creation tables in database");
      }
   }
   if (!$DB->TableExists("glpi_plugin_escalation_configs")) {
      $DB->query("CREATE TABLE `glpi_plugin_escalation_configs` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `entities_id` int(11) NOT NULL DEFAULT '0',
         `unique_assigned_tech` varchar(255) DEFAULT NULL,
         `unique_assigned_group` varchar(255) DEFAULT NULL,
         `workflow`  varchar(255) DEFAULT NULL,
         `limitgroup`  varchar(255) DEFAULT NULL,
         PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
      $DB->query("INSERT INTO `glpi_plugin_escalation_configs`
         (`id` ,`entities_id` ,`unique_assigned_tech`, `unique_assigned_group`, `workflow`, `limitgroup`)
      VALUES (NULL , '0', '0', '0', '0', '0');");
   } else {
      if ($DB->FieldExists('glpi_plugin_escalation_configs', 'unique_assigned')) {
         // Update
         $DB->query("ALTER TABLE `glpi_plugin_escalation_configs` CHANGE `unique_assigned` `unique_assigned_tech` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;");
         $DB->query("ALTER TABLE `glpi_plugin_escalation_configs` ADD `unique_assigned_group` VARCHAR(250) NULL DEFAULT NULL AFTER `unique_assigned_tech`;");
         $DB->query("UPDATE glpi_plugin_escalation_configs SET `unique_assigned_group` = `unique_assigned_tech`");
      }
   }
   if (!$DB->TableExists("glpi_plugin_escalation_profiles")) {
      $DB->query("CREATE TABLE `glpi_plugin_escalation_profiles` (
         `profiles_id` int(11) NOT NULL DEFAULT '0',
         `bypassworkflow` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
         `copyticket` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
         `copyticketonworkflow` char(1) COLLATE utf8_unicode_ci DEFAULT NULL
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
   }
   if (!$DB->FieldExists('glpi_plugin_escalation_profiles', 'copyticket')) {
      $DB->query("ALTER TABLE `glpi_plugin_escalation_profiles`
         ADD `copyticket` CHAR( 1 ) NULL ");
      $DB->query("ALTER TABLE `glpi_plugin_escalation_profiles`
         ADD `copyticketonworkflow` CHAR( 1 ) NULL ");
   }
   if (!$DB->FieldExists("glpi_plugin_escalation_configs", "limitgroup")) {
      $migration = new Migration(PLUGIN_ESCALATION_VERSION);
      $migration->addField('glpi_plugin_escalation_configs',
                           "limitgroup",
                           "varchar(255) DEFAULT NULL");
      $migration->migrationOneTable('glpi_plugin_escalation_configs');
      $DB->query("UPDATE `glpi_plugin_escalation_configs`
         SET `limitgroup` = '0' WHERE `entities_id` =1");
   }
   return true;
}



// Uninstall process for plugin : need to return true if succeeded
function plugin_escalation_uninstall() {
   global $DB;

   $query = "SHOW TABLES;";
   $result=$DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      if (strstr($data[0], "glpi_plugin_escalation_")) {
         $query_delete = "DROP TABLE `".$data[0]."`;";
         $DB->query($query_delete) or die($DB->error());
      }
   }

   return true;
}
