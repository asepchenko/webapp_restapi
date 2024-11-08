insert into permission_role(role_id, permission_id)
select 6 as role_id, id as permission_id
-- ,title
from permissions 
where id in(9,
102,119)
-- where id in(9,70,74,71,73,75,72,102,119,116,117,103,105,104,111,62,68,63,65,69,66,67,64,76,80,77,79,81,78)
-- where title like '%menu%'

SELECT * FROM permission_role WHERE role_id=6 order by permission_id

DELETE FROM permission_role WHERE role_id=6 and permission_id in(9,102,119)