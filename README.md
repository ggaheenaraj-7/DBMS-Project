# Smart Logistics & Courier Tracking System

Stack: HTML/CSS/JavaScript + PHP + MySQL + XAMPP.

Included from both project reviews:
- Customer registration/login, courier booking, shipment history
- Package registration, tracking ID, classification
- Real-time-style shipment tracking/status timeline
- Warehouse records, package storage, inventory/capacity alerts
- Driver/vehicle records, assignment and delivery scheduling
- Admin dashboard, monitoring, reports
- Role-based access
- AI-style decision support demo: assignment scoring, ETA prediction, delay/anomaly detection
- Proactive alerts and shipment communication
- Route recommendation
- QR tracking payload generation
- Reverse logistics/returns
- Customer feedback/ratings
- Carbon-footprint estimate
- Analytics cards and bar chart
- API endpoints implemented in PHP
- MySQL relational schema with foreign keys

## Run
1. Install XAMPP and start Apache + MySQL.
2. Create a database named `smart_logistics`.
3. Import `sql/schema.sql` in phpMyAdmin.
4. Copy this folder to `xampp/htdocs/smart-logistics`.
5. Open `http://localhost/smart-logistics/`.
6. Demo login:
   - admin@demo.com / admin123
   - customer@demo.com / customer123

The AI features are intentionally implemented as transparent PHP heuristics so the project works locally without an external AI service. They can later be replaced by a trained ML/API model.
