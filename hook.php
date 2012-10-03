<?php

/*
   ------------------------------------------------------------------------
   Plugin Escalation for GLPI
   Copyright (C) 2012-2012 by the Plugin Escalation for GLPI Development Team.

   https://forge.indepnet.net/projects/escalation/
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
   along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Escalation for GLPI
   @author    David Durieux
   @co-author 
   @comment   
   @copyright Copyright (c) 2011-2012 Plugin Escalation for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/escalation/
   @since     2012
 
   ------------------------------------------------------------------------
 */

function plugin_escalation_install() {
   global $DB, $LANG;

   if (!TableExists("glpi_plugin_escalation_groups_groups")) {
      $empty_sql = "plugin_escalation-".PLUGIN_ESCALATION_VERSION."-empty.sql";
      $DB_file = GLPI_ROOT ."/plugins/escalation/install/mysql/$empty_sql";
      $DBf_handle = fopen($DB_file, "rt");
      $sql_query = fread($DBf_handle, filesize($DB_file));
      fclose($DBf_handle);
      foreach ( explode(";\n", "$sql_query") as $sql_line) {
         if (Toolbox::get_magic_quotes_runtime()) $sql_line=Toolbox::stripslashes_deep($sql_line);
         if (!empty($sql_line)) {
            $DB->query($sql_line)/* or die($DB->error())*/;
         }
      }
   } else {
      if (!TableExists("glpi_plugin_escalation_configs")) {
         $DB->query("CREATE TABLE `glpi_plugin_escalation_configs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `entities_id` int(11) NOT NULL DEFAULT '0',
            `unique_assigned` varchar(255) DEFAULT NULL,
            `workflow`  varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
         ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
         $DB->query("INSERT INTO `glpi_plugin_escalation_configs`
            (`id` ,`entities_id` ,`unique_assigned` ,`workflow`)
         VALUES (NULL , '0', '0', '0');");         
      }
      if (!TableExists("glpi_plugin_escalation_profiles")) {
         $DB->query("CREATE TABLE `glpi_plugin_escalation_profiles` (
           `profiles_id` int(11) NOT NULL DEFAULT '0',
           `bypassworkflow` char(1) COLLATE utf8_unicode_ci DEFAULT NULL
         ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
      }      
   }
   return true;
}



// Uninstall process for plugin : need to return true if succeeded
function plugin_escalation_uninstall() {
   global $DB;
   
   $query = "SHOW TABLES;";
   $result=$DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      if (strstr($data[0],"glpi_plugin_escalation_")){
         $query_delete = "DROP TABLE `".$data[0]."`;";
         $DB->query($query_delete) or die($DB->error());
      }
   }
   
   return true;
}



function plugin_get_headings_escalation($item,$withtemplate) {
   global $LANG;
   
   switch (get_class($item)) {
      case 'Ticket' :
         $array = array ();
         if ($item->getID() > 0) {
            if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
               $peConfig = new PluginEscalationConfig();
               if ($peConfig->getValue('workflow', $item->fields['entities_id']) == '1') {
                  $peGroup_group = new PluginEscalationGroup_Group();
                  if (PluginEscalationProfile::haveRight("bypassworkflow", 1)
                          OR $peGroup_group->is_user_tech($item->getID())) {               
                     $array[1] = "Escalade";
                  }
               }
            }
         }
         return $array;
         break;
      
      case 'Group' :
         $array = array ();
         if ($item->getID() > 0) {
            $peConfig = new PluginEscalationConfig();
            if ($peConfig->getValue('workflow', $item->fields['entities_id']) == '1') {
               $array[1] = "Escalade";
            }
         }
         return $array;
         break; 
         
      case 'Entity':
         $array = array();
         if (Session::haveRight("entity", 'r')) {
            $array[0] = "Escalation";
          }
          return $array;
          break;

   }
   
}


function plugin_headings_actions_escalation($item) {
//   switch ($type) {

   switch (get_class($item)) {
      case 'Ticket' :
         $array = array();
         if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
            $array[1] = "plugin_headings_escalation_escalade";
         }
         return $array;
         break;
      
      case 'Group' :
         $array = array();
         $array[1] = "plugin_headings_escalation_escaladegroup";
         return $array;
         break;
      
      case 'Entity':
         $array = array();
         $array[0] = "plugin_headings_escalation_config";
         return $array;
         break;
      
   }
}



function plugin_headings_escalation_escalade($item) {
   $peGroup_Group = new PluginEscalationGroup_Group();
   $peGroup_Group->showGroups($item->getID());
   
}

function plugin_headings_escalation_escaladegroup($item) {
   $peGroup_Group = new PluginEscalationGroup_Group();
   $peGroup_Group->manageGroup($item->getID());
   
}

function plugin_headings_escalation_config($item) {
   $peConfig = new PluginEscalationConfig();
   $peConfig->showForm($item->getID());
}


?>