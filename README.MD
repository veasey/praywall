# 🙏 PrayWall MicroSaaS
A lightweight microSaaS application for churches to host an online Prayer Request & Praise Report wall. Built with Slim 4 and Core PHP, this can be embedded into church websites or run standalone.

## 📦 Features
Post prayer requests or praise reports (with optional anonymity)

- JSON API for fetching prayers (coming soon)
- Embeddable HTML prayer wall via iframe (coming soon)
- Lightweight Slim routing with clean, maintainable code
- Facebook login integration-ready (coming soon)
- Simple moderation system

---

## 🛠️ Local Development Setup

Follow these steps to run PrayWall locally using Docker:

### 📥 1️⃣ Clone the repository:

```bash
git clone https://github.com/yourusername/praywall.git
cd praywall
```

### 🚀 2️⃣ Start the application and database:

```bash
docker-compose up --build
```

- PHP app will be available at: http://localhost:8000
- MySQL database will be available on port 3307

### 📂 3️⃣ Import the database schema:

Once the containers are running, open a new terminal tab and run:

```bash
docker exec -i praywall_db mysql -u root -proot praywall < db/schema.sql
```

Locally this is made easier by a route you can visit: `localhost:8000/migrate`

---

## 📄 Project Configuration

Database connection settings can be found in settings.php:

```php
$db = new PDO("mysql:host=db;dbname=praywall;charset=utf8", 'root', 'root');
```

Adjust as needed for your local or production setup.

---

## ✅ Summary

| Action | Command |
| --- | --- |
| Start local environment | docker-compose up --build |
| Import database schema | docker exec -i praywall_db mysql -u root -proot praywall < db/schema.sql |
| Export database schema | docker exec praywall_db mysqldump -u root -proot --no-data praywall > db/schema.sql |
| Deploy via Docker VPS | docker-compose -f docker-compose.yml up -d --build |
