# YIC Campus Marketplace

## How to Run the Project

### Requirements

Before running the project, make sure you have:

- Laragon
- Apache
- MySQL
- HeidiSQL
- PHP 8 or later

---

## 1. Move the Project Folder

Copy the project folder to the Laragon `www` folder:

```text
C:\laragon\www\YIC_Marketplace
```

Make sure `index.php` is directly inside the project folder.

Example:

```text
C:\laragon\www\YIC_Marketplace\index.php
```

---

## 2. Start Laragon

Open Laragon and start:

- Apache
- MySQL

---

## 3. Create the Database

Open HeidiSQL from Laragon.

Create a new database named:

```sql
marketplace_db
```

---

## 4. Import the Database File

In HeidiSQL:

1. Select the `marketplace_db` database.
2. Click `File`.
3. Click `Load SQL file`.
4. Choose this file:

```text
database/marketplace_db.sql
```

5. Run the SQL file.

This will create the required tables and demo data.

---

## 5. Check Database Connection

Open:

```text
classes/Database.php
```

Make sure the settings are:

```php
$host = 'localhost';
$db = 'marketplace_db';
$user = 'root';
$pass = '';
```

---

## 6. Run the Project

Open the browser and go to:

```text
http://localhost/YIC_Marketplace/
```

---

## Demo Accounts

| Role | Email | Password |
|---|---|---|
| Admin | admin@yic.edu.sa | password |
| Student | student@yic.edu.sa | password |

---

## Notes

- The database must be imported before using the system.
- The `uploads` folder must exist for item images.
- If the folder name is changed, update the browser URL.
