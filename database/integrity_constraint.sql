ALTER TABLE area_cities
ADD CONSTRAINT FK_area_cities_cities
FOREIGN KEY (city_id) REFERENCES cities(id);

ALTER TABLE locations
ADD CONSTRAINT FK_locations_origin_cities
FOREIGN KEY (origin) REFERENCES cities(id);

ALTER TABLE locations
ADD CONSTRAINT FK_locations_destination_cities
FOREIGN KEY (destination) REFERENCES cities(id);

ALTER TABLE locations
ADD CONSTRAINT FK_locations_services
FOREIGN KEY (service_id) REFERENCES services(id);

----------------------------------------------------
ALTER TABLE agents
ADD CONSTRAINT FK_agents_areas
FOREIGN KEY (area_id) REFERENCES areas(id);

ALTER TABLE agent_master_prices
ADD CONSTRAINT FK_agent_master_prices_locations
FOREIGN KEY (location_id) REFERENCES locations(id);

ALTER TABLE branchs
ADD CONSTRAINT FK_branchs_cities
FOREIGN KEY (city_id) REFERENCES cities(id);

ALTER TABLE customers
ADD CONSTRAINT FK_customers_cities
FOREIGN KEY (city_id) REFERENCES cities(id);

ALTER TABLE customer_branchs
ADD CONSTRAINT FK_customer_branchs_customer
FOREIGN KEY (customer_id) REFERENCES customers(id);

ALTER TABLE customer_branchs
ADD CONSTRAINT FK_customer_branchs_customer_brands
FOREIGN KEY (customer_brand_id) REFERENCES customer_brands(id);

ALTER TABLE customer_branchs
ADD CONSTRAINT FK_customer_branchs_cities
FOREIGN KEY (city_id) REFERENCES cities(id);

ALTER TABLE customer_brands
ADD CONSTRAINT FK_customer_brands_customers
FOREIGN KEY (customer_id) REFERENCES customers(id);

ALTER TABLE customer_master_prices
ADD CONSTRAINT FK_customer_master_prices_customers
FOREIGN KEY (customer_id) REFERENCES customers(id);

ALTER TABLE customer_master_prices
ADD CONSTRAINT FK_customer_master_prices_locations
FOREIGN KEY (location_id) REFERENCES locations(id);

ALTER TABLE customer_master_prices
ADD CONSTRAINT FK_customer_master_prices_services
FOREIGN KEY (service_id) REFERENCES services(id);

ALTER TABLE customer_mous
ADD CONSTRAINT FK_customer_mous_customers
FOREIGN KEY (customer_id) REFERENCES customers(id);

ALTER TABLE customer_pics
ADD CONSTRAINT FK_customer_pics_customers
FOREIGN KEY (customer_id) REFERENCES customers(id);

ALTER TABLE customer_pics
ADD CONSTRAINT FK_customer_pics_cities
FOREIGN KEY (city_id) REFERENCES cities(id);

ALTER TABLE customer_trucking_prices
ADD CONSTRAINT FK_customer_trucking_prices_customers
FOREIGN KEY (customer_id) REFERENCES customers(id);

ALTER TABLE customer_trucking_prices
ADD CONSTRAINT FK_customer_trucking_prices_origin_cities
FOREIGN KEY (origin) REFERENCES cities(id);

ALTER TABLE customer_trucking_prices
ADD CONSTRAINT FK_customer_trucking_prices_destination_cities
FOREIGN KEY (destination) REFERENCES cities(id);

ALTER TABLE customer_trucking_prices
ADD CONSTRAINT FK_customer_trucking_prices_truck_types
FOREIGN KEY (truck_type_id) REFERENCES truck_types(id);

ALTER TABLE drivers
ADD CONSTRAINT FK_drivers_truck
FOREIGN KEY (truck_id) REFERENCES trucks(id);

ALTER TABLE invoices
ADD CONSTRAINT FK_invoices_customers
FOREIGN KEY (customer_id) REFERENCES customers(id);

ALTER TABLE manifests
ADD CONSTRAINT FK_manifests_drivers
FOREIGN KEY (driver_id) REFERENCES drivers(id);

ALTER TABLE manifests
ADD CONSTRAINT FK_manifests_trucks
FOREIGN KEY (truck_id) REFERENCES trucks(id);

ALTER TABLE manifests
ADD CONSTRAINT FK_manifests_origin_cities
FOREIGN KEY (origin) REFERENCES cities(id);

ALTER TABLE manifests
ADD CONSTRAINT FK_manifests_destination_cities
FOREIGN KEY (destination) REFERENCES cities(id);

ALTER TABLE manifest_cogs
ADD CONSTRAINT FK_manifest_cogs_cities
FOREIGN KEY (city_id) REFERENCES cities(id);

ALTER TABLE orders
ADD CONSTRAINT FK_orders_customers
FOREIGN KEY (customer_id) REFERENCES customers(id);

ALTER TABLE orders
ADD CONSTRAINT FK_orders_customer_branchs
FOREIGN KEY (customer_branch_id) REFERENCES customer_branchs(id);

ALTER TABLE orders
ADD CONSTRAINT FK_orders_customer_master_prices
FOREIGN KEY (customer_master_price_id) REFERENCES customer_master_prices(id);

ALTER TABLE orders
ADD CONSTRAINT FK_orders_trucking_prices
FOREIGN KEY (trucking_price_id) REFERENCES trucking_prices(id);
/*
ALTER TABLE orders
ADD CONSTRAINT FK_orders_services
FOREIGN KEY (service_id) REFERENCES services(id);
*/
ALTER TABLE orders
ADD CONSTRAINT FK_orders_service_groups
FOREIGN KEY (service_group_id) REFERENCES service_groups(id);
/*
ALTER TABLE orders
ADD CONSTRAINT FK_orders_truck_types
FOREIGN KEY (truck_type_id) REFERENCES truck_types(id);
*/
ALTER TABLE orders
ADD CONSTRAINT FK_orders_origin_cities
FOREIGN KEY (origin) REFERENCES cities(id);

ALTER TABLE orders
ADD CONSTRAINT FK_orders_destination_cities
FOREIGN KEY (destination) REFERENCES cities(id);

ALTER TABLE order_agents
ADD CONSTRAINT FK_order_agents_agents
FOREIGN KEY (agent_id) REFERENCES agents(id);

ALTER TABLE order_agents
ADD CONSTRAINT FK_order_agents_branchs
FOREIGN KEY (branch_id) REFERENCES branchs(id);

ALTER TABLE services
ADD CONSTRAINT FK_services_service_groups
FOREIGN KEY (service_group_id) REFERENCES service_groups(id);

ALTER TABLE trucking_prices
ADD CONSTRAINT FK_trucking_prices_truck_types
FOREIGN KEY (truck_type_id) REFERENCES truck_types(id);

ALTER TABLE trucking_prices
ADD CONSTRAINT FK_trucking_prices_origin_cities
FOREIGN KEY (origin) REFERENCES cities(id);

ALTER TABLE trucking_prices
ADD CONSTRAINT FK_trucking_prices_destination_cities
FOREIGN KEY (destination) REFERENCES cities(id);

ALTER TABLE trucks
ADD CONSTRAINT FK_trucks_truck_types
FOREIGN KEY (truck_type_id) REFERENCES truck_types(id);

ALTER TABLE user_agents
ADD CONSTRAINT FK_user_agents_agents
FOREIGN KEY (agent_id) REFERENCES agents(id);

ALTER TABLE user_customers
ADD CONSTRAINT FK_user_customers_customers
FOREIGN KEY (customer_id) REFERENCES customers(id);