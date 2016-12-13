<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Groupperformance_QueryObject extends CRM_Contact_BAO_Query_Interface {

  public function &getFields() {
    return array(
      'group' => 'group'
    );
  }

  public function getPanesMapper(&$panes) {
    return null;
  }

  /**
   * @param string $fieldName
   * @param $mode
   * @param $side
   *
   * @return mixed
   */
  public function from($fieldName, $mode, $side) {
    return NULL;
  }

  /**
   * @param $query
   *
   * @return null
   */
  public function select(&$query) {
    return NULL;
  }

  /**
   * @param $query
   *
   * @return null
   */
  public function where(&$query) {
    foreach($query->_params as $param) {
      if ($param[0] == 'group' && is_array($param[2]) && count($param[2]) > 0) {
        list($name, $op, $value, $grouping, $wildcard) = $param;

        if ($value) {
          if (strpos($op, 'IN') === FALSE) {
            $value = key($value);
          }
          else {
            $value = array_keys($value);
          }
        }

        if ($op != 'IN' && $op != 'NOT IN') {
          return;
        }

        unset($query->_paramLookup['group']); // This prevents the building of the select query on the civicrm_group_contact

        // Find all the groups that are part of a saved search.
        $sql = "
SELECT id, cache_date, saved_search_id, children
FROM   civicrm_group
WHERE  civicrm_group.id IN (".implode(",", $value).")
  AND  ( saved_search_id != 0
   OR    saved_search_id IS NOT NULL
   OR    children IS NOT NULL )
";
        $group = CRM_Core_DAO::executeQuery($sql);
        while ($group->fetch()) {
          if (!$query->_smartGroupCache || $group->cache_date == NULL) {
            CRM_Contact_BAO_GroupContactCache::load($group);
          }
        }

        $clause = '(
contact_a.id IN (SELECT contact_id FROM civicrm_group_contact WHERE status = \'Added\' AND group_id '.$op.' ('.implode(",", $value).'))
OR
contact_a.id IN (SELECT contact_id FROM civicrm_group_contact_cache WHERE group_id  '.$op.' ('.implode(",", $value).'))
)';
        $query->_where[$grouping][] = $clause;

        list($qillop, $qillVal) = CRM_Contact_BAO_Query::buildQillForFieldValue('CRM_Contact_DAO_Group', 'id', $value, $op);
        $query->_qill[$grouping][] = ts("Group(s) %1 %2", array(1 => $qillop, 2 => $qillVal));
        $query->_qill[$grouping][] = ts("Group Status %1", array(1 => implode(' ' . ts('or') . ' ', array('Added'))));
      }
    }
    return NULL;
  }

}