# Student CRUD (PHP + MySQLi) — README

## Project Overview
This project is a basic **Student Management CRUD** system built with:
- **PHP (MySQLi)**
- **MySQL Database**
- **HTML + CSS** (single shared stylesheet)

It supports:
- View all student records (Index)
- Add a new student
- Edit/update an existing student
- Delete a student

It also supports a shared-database scenario (batch environment) where:
- Everyone can **view** all records
- Only the owner (matching **createdBy**) can **edit/delete** their own records


## File Responsibilities

### `config.php`
- Holds database connection settings.
- Used by all other PHP files with `require_once "config.php";`

### `index.php`
- Lists student records in a table.
- Shows **Edit/Delete buttons** only if:
  - `createdBy == YOUR_NAME` (in shared DB scenario)
- Provides link to `add.php`

### `add.php`
- Form to insert a new student record.
- Validates required fields (Student ID, First Name, Last Name, Created By / or hard-coded createdBy if needed).
- Inserts into table `student`.

### `edit.php`
- Loads one student row by `studentID`.
- Shows a form pre-filled with existing data.
- Submits to `update.php`.

### `update.php`
- Updates record safely using prepared statements.
- If Student ID editing is enabled:
  - Uses `oldStudentID` to find the record
  - Writes new `studentID` into DB
- Optionally restricts update to only your own records:
  - `WHERE studentID=? AND createdBy=?`

### `delete.php`
- Deletes a record by `studentID`.
- Optionally restricts delete to only your own records:
  - `WHERE studentID=? AND createdBy=?`

### `css/style.css`
- Shared styling for all pages.
- Keeps consistent UI design across Add/Edit/Index/Error pages.

## Database Setup

### 1) Create Database
Example:
- Database name: `artist_bci` (or any name your lecturer gives)

### 2) Create Table (`student`)
Run this SQL in phpMyAdmin (XAMPP) or in your hosting database panel for the Tbale:

```sql
CREATE TABLE student (
  studentID VARCHAR(50) PRIMARY KEY,
  firstName VARCHAR(100) NOT NULL,
  lastName VARCHAR(100) NOT NULL,
  birthDate DATE NULL,
  email VARCHAR(150) NULL,
  city VARCHAR(100) NULL,
  courseName VARCHAR(100) NULL,
  enrolledYear INT NULL,
  createdBy VARCHAR(100) NOT NULL
);