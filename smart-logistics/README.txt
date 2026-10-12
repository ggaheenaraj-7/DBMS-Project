SMART LOGISTICS COMPLETE PACKAGE

Copy the smart-logistics folder to:
C:\xampp\htdocs\

Start Apache and MySQL in XAMPP.

Open:
http://localhost/smart-logistics/

Existing database required:
smart_logistics

Demo login:
Admin: admin@demo.com / Admin@123
Customer: customer@demo.com / Customer@123

IMPORTANT:
Do not delete or recreate your existing database/tables.
The shipment code expects the existing columns:
shipments: customer_id, tracking_id, sender_name, receiver_name, origin,
destination, package_type, weight, priority, status, eta_hours
shipment_events: shipment_id, status, location, message, event_time

If your schema differs, run:
DESCRIBE shipments;
DESCRIBE shipment_events;
