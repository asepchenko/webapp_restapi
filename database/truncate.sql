-- delete semua transaksi
truncate table orders;
truncate table order_agents;
truncate table order_agent_destinations;
truncate table order_units;
truncate table order_costs;
truncate table order_cost_agents;
truncate table order_references;
truncate table order_trackings;
truncate table manifests;
truncate table manifest_cogs;
truncate table manifest_details;
truncate table trips;
truncate table trip_cities;
truncate table trip_details;
truncate table invoices;
truncate table invoice_details;
truncate table bills;
truncate table bill_details;
truncate table documents;
truncate table document_details;
-- delete customer
truncate table customers;
truncate table customer_branchs;
truncate table customer_brands;
truncate table customer_mous;
truncate table customer_master_prices;
truncate table user_customers;
-- delete agent
truncate table agents;
truncate table agent_master_prices;
-- delete master data
truncate table locations;
truncate table areas;
truncate table area_cities;