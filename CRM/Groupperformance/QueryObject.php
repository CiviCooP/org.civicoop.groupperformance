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
  public function where(&$query) {
    foreach($query->_params as $param) {
      if ($param[0] == 'group' && $param[1] == 'IN' && is_array($param[2]) && count($param[2]) > 0) {
        list($name, $op, $value, $grouping, $wildcard) = $param;
        $groupIds = array_keys($value);
        $clause = '(
contact_a.id IN (SELECT contact_id FROM civicrm_group_contact WHERE status = \'Added\' AND group_id IN ('.implode(",", $groupIds).'))
OR
contact_a.id IN (SELECT contact_id FROM civicrm_group_contact_cache WHERE group_id IN ('.implode(",", $groupIds).'))
)';
        $query->_where[$grouping][] = $clause;

        list($qillop, $qillVal) = CRM_Contact_BAO_Query::buildQillForFieldValue('CRM_Contact_DAO_Group', 'id', $value, $op);
        $query->_qill[$grouping][] = ts("Group(s) %1 %2", array(1 => $qillop, 2 => $qillVal));
      }
    }
    return NULL;
  }

}