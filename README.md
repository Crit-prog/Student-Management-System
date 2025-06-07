### 🎓 Student Management System

A full-featured web-based Student Management System built using PHP and MySQL — includes login authentication, grading system (college-style), quiz handling, and student result viewing.

> 🚀 Built with passion, caffeine, and some help from ChatGPT.  
> 🔗 Live demo coming soon (optional — you can host on XAMPP, etc.)

---

### 📌 Features

- 🔐 **Login System** – Secure login for admins and users
- 📝 **Grading System** – Custom college-style grades (1.00, 1.25, etc.)
- 🧠 **Quiz Module** – Assign quizzes, input scores, and track results
- 📊 **Student Result Viewer** – Students can view their results in real-time
- 🛠️ **Admin Panel** – Manage quizzes, student data, and system settings
- 🎨 **Styled Login UI** – Custom background + styled card layout

---

### 🛠️ Installation & Setup

Follow these steps to run the project locally:

### 1. Clone the Repo

> git clone https://github.com/Crit-prog/Student-Management-System.git
cd Student-Management-System
---
### 2. Set Up Your Server
> Use XAMPP or WAMP as your local server.

>Place the project inside your htdocs folder (for XAMPP) or www folder (for WAMP).

>Example path: C:\xampp\htdocs\Student-Management-System
---
### 3. Import the Database
>Open phpMyAdmin

>Create a new database (e.g., student_db)

>Import the SQL file from the project folder (lms_db.sql)
---
### 4. Configure the DB Connection
>Open db_connect.php and update the credentials if necessary:
  >$conn = new mysqli('localhost', 'root', '', 'student_db');
---
### 5. Start the App
>Run XAMPP/WAMP and start Apache and MySQL

>Go to your browser:
>http://localhost/login.php
---
### 🧑‍💻 Default Login 
>Username: admin
>Password: admin123
---
### 📂 Project Structure (Simplified)
├── db_connect.php 

├── index.php

├── login.php

├── admin/

├── faculty/

├── assets/

├── css/

├── js/

└── student_db.sql
