# org.civicoop.groupperformance

This extension was developed after we discovered that looking up contacts in
an installation with more than 300.000 contacts and 3000 groups was performing very
slow.

The real cause was in an SQL statement on the civicrm_group_contact and civicrm_group_contact_cache with an OR clause in the where.
This extension changes that SQL statement so it will use a _semi-join_.