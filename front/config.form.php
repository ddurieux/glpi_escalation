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

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkRight("entity", UPDATE);

Html::header("escalation", $_SERVER["PHP_SELF"], "plugins", "escalation", "config");

$peConfig = new PluginEscalationConfig();
//if (isset($_POST['unique_assigned'])
//        AND $_POST['unique_assigned'] == 'NULL') {
//   Html::back();
//}

if (isset($_POST['unique_assigned_tech'])
        AND $_POST['unique_assigned_tech'] == '+1') {
   $_POST['unique_assigned_tech'] = 1;
}
if (isset($_POST['unique_assigned_group'])
        AND $_POST['unique_assigned_group'] == '+1') {
   $_POST['unique_assigned_group'] = 1;
}
if (isset($_POST['workflow'])
        AND $_POST['workflow'] == '+1') {
   $_POST['workflow'] = 1;
}
if (isset($_POST['limitgroup'])
        AND $_POST['limitgroup'] == '+1') {
   $_POST['limitgroup'] = 1;
}
if (isset($_POST['unique_assigned_tech'])
        AND $_POST['unique_assigned_tech'] == '+0') {
   $_POST['unique_assigned_tech'] = 0;
}
if (isset($_POST['unique_assigned_group'])
        AND $_POST['unique_assigned_group'] == '+0') {
   $_POST['unique_assigned_group'] = 0;
}
if (isset($_POST['workflow'])
        AND $_POST['workflow'] == '+0') {
   $_POST['workflow'] = 0;
}
if (isset($_POST['limitgroup'])
        AND $_POST['limitgroup'] == '+0') {
   $_POST['limitgroup'] = 0;
}

if (isset ($_POST["add"])) {
   if ((isset($_POST['unique_assigned_tech'])
        AND $_POST['unique_assigned_tech'] !== 'NULL')
        OR (isset($_POST['workflow'])
        AND $_POST['workflow'] !== 'NULL')) {
      $peConfig->add($_POST);
   }
   if ((isset($_POST['unique_assigned_group'])
        AND $_POST['unique_assigned_group'] !== 'NULL')
        OR (isset($_POST['workflow'])
        AND $_POST['workflow'] !== 'NULL')) {
      $peConfig->add($_POST);
   }
   Html::back();
} else if (isset ($_POST["update"])) {
   if (isset($_POST['unique_assigned_tech'])
        AND $_POST['unique_assigned_tech'] === 'NULL'
        AND isset($_POST['unique_assigned_group'])
        AND $_POST['unique_assigned_group'] === 'NULL'
        AND isset($_POST['workflow'])
        AND $_POST['workflow'] === 'NULL') {
      $peConfig->delete($_POST);
   } else {
      $peConfig->update($_POST);
   }
   Html::back();
} else if (isset ($_POST["delete"])) {
   $peConfig->delete($_POST);
   Html::back();
}

Html::footer();
