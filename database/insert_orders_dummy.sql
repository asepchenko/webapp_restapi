-- select * from orders;

-- select replace(rand(),'.','') as awb_no;

insert into orders(branch_id, pickup_date, awb_no,
customer_id, customer_branch_id,customer_master_price_id, 
service_id, service_group_id, origin, destination,
total_colly, total_kg, total_kg_agent, last_status,
payment_type, user_id, created_at, updated_at)
select 1 as branch_id, '2022-05-11' as pickup_date, replace(rand(),'.','a.') as awb_no,
1 as customer_id, 3 as customer_branch_id, 1 as customer_master_price_id,
1 as service_id, 1 as service_group_id, 3 as origin, 1 as destination,
10 as total_colly, 1000 as total_kg, 1000 as total_kg_agent, 'Open' as last_status,
'Cash' as payment_type, 1 as user_id, now() as created_at, now() as updated_at from orders
limit 5000