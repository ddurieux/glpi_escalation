<?php

/*
   ------------------------------------------------------------------------
   Plugin Escalation for GLPI
   Copyright (C) 2012-2015 by the Plugin Escalation for GLPI Development Team.

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
   @copyright Copyright (c) 2011-2015 Plugin Escalation for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://github.com/ddurieux/glpi_escalation
   @since     2012

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginEscalationConfig extends CommonDBTM {

   static $rightname = 'config';

   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return __('Configuration');
   }



   /**
    * Display tab
    *
    * @param CommonGLPI $item
    * @param integer $withtemplate
    *
    * @return varchar name of the tab(s) to display
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == 'Entity'
              && $item->getID() > -1
              && Session::haveRight("entity", READ)) {
         return __('Escalation', 'escalation');
      }
      return '';
   }



   /**
    * Display content of tab
    *
    * @param CommonGLPI $item
    * @param integer $tabnum
    * @param interger $withtemplate
    *
    * @return boolean TRUE
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='Entity') {
         $peConfig = new PluginEscalationConfig();
         $peConfig->showForm($item->getID());
      }
      return TRUE;
   }



   /**
   * Display form for service configuration
   *
   * @param $items_id integer ID
   * @param $options array
   *
   *@return bool true if form is ok
   *
   **/
   function showForm($entities_id, $options=array(), $copy=array()) {
      global $DB,$CFG_GLPI;

      $a_configs = $this->find("`entities_id`='".$entities_id."'", "", 1);
      if (count($a_configs) == '1') {
         $a_config = current($a_configs);
         $this->getFromDB($a_config['id']);
      } else {
         $this->getEmpty();
      }

      $this->showFormHeader($options);

      echo "<tr>";
      echo "<td>";
      echo "<input type='hidden' name='entities_id' value='".$entities_id."' />";
      echo __('Unique assigned to technician', 'escalation')."&nbsp;:";
      echo "</td>";
      echo "<td>";
      if ($entities_id == '0') {
         $elements = array("+0" => __('No'),
                           "+1" => __('Yes')
                           );
      } else {
         $elements = array("NULL" => __('Inheritance of the parent entity'),
                           "+0" => __('No'),
                           "+1" => __('Yes')
                           );
      }
      $value = (is_null($this->fields['unique_assigned_tech']) ? "NULL" : "+".$this->fields['unique_assigned_tech']);
      $value = str_replace("++", "+", $value);
      Dropdown::showFromArray("unique_assigned_tech", $elements, array('value' => $value));
      echo "</td>";

      echo "<td>".__('Workflow', 'escalation')."&nbsp;:</td>";
      echo "<td>";
      if ($entities_id == '0') {
         $elements = array("+0" => __('No'),
                           "+1" => __('Yes')
                           );
      } else {
         $elements = array("NULL" => __('Inheritance of the parent entity'),
                           "+0" => __('No'),
                           "+1" => __('Yes')
                           );
      }
      $value = (is_null($this->fields['workflow']) ? "NULL" : "+".$this->fields['workflow']);
      $value = str_replace("++", "+", $value);

      $value = (is_null($this->fields['workflow']) ? "NULL" : "+".$this->fields['workflow']);

      Dropdown::showFromArray("workflow", $elements, array('value' => $value));
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>";
      echo "<input type='hidden' name='entities_id' value='".$entities_id."' />";
      echo __('Unique assigned to technician group', 'escalation')."&nbsp;:";
      echo "</td>";
      echo "<td>";
      if ($entities_id == '0') {
         $elements = array("+0" => __('No'),
                           "+1" => __('Yes')
                           );
      } else {
         $elements = array("NULL" => __('Inheritance of the parent entity'),
                           "+0" => __('No'),
                           "+1" => __('Yes')
                           );
      }
      $value = (is_null($this->fields['unique_assigned_group']) ? "NULL" : "+".$this->fields['unique_assigned_group']);
      $value = str_replace("++", "+", $value);
      Dropdown::showFromArray("unique_assigned_group", $elements, array('value' => $value));
      echo "</td>";

      echo "<td>".__('Limit requester groups from writer groups', 'escalation')."&nbsp;:</td>";
      echo "<td>";
      if ($entities_id == '0') {
         $elements = array("+0" => __('No'),
                           "+1" => __('Yes')
                           );
      } else {
         $elements = array("NULL" => __('Inheritance of the parent entity'),
                           "+0" => __('No'),
                           "+1" => __('Yes')
                           );
      }
      $value = (is_null($this->fields['limitgroup']) ? "NULL" : "+".$this->fields['limitgroup']);
      $value = str_replace("++", "+", $value);

      Dropdown::showFromArray("limitgroup", $elements, array('value' => $value));
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }



/**
    * Get value of config
    *
    * @global object $DB
    * @param value $name field name
    * @param integer $entities_id
    *
    * @return value of field
    */
   function getValueAncestor($name, $entities_id) {
      global $DB;

      $entities_ancestors = getAncestorsOf("glpi_entities", $entities_id);

      $nbentities = count($entities_ancestors);
      for ($i=0; $i<$nbentities; $i++) {
         $entity = array_pop($entities_ancestors);
         $query = "SELECT * FROM `".$this->getTable()."`
            WHERE `entities_id`='".$entity."'
               AND `".$name."` IS NOT NULL
            LIMIT 1";
         $result = $DB->query($query);
         if ($DB->numrows($result) != '0') {
            $data = $DB->fetch_assoc($result);
            return $data[$name];
         }
      }
      $this->getFromDB(1);
      return $this->getField($name);
   }



   /**
    * Get the value (of this entity or parent entity or in general config
    *
    * @global object $DB
    * @param value $name field name
    * @param integet $entities_id
    *
    * @return value value of this field
    */
   function getValue($name, $entities_id) {
      global $DB;

      $query = "SELECT `".$name."` FROM `".$this->getTable()."`
         WHERE `entities_id`='".$entities_id."'
            AND `".$name."` IS NOT NULL
         LIMIT 1";
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0) {
         $data = $DB->fetch_assoc($result);
         return $data[$name];
      }
      return $this->getValueAncestor($name, $entities_id);
   }

}

?>