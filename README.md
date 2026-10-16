# NoteBridge — University Note Sharing Platform

A web-based academic resource-sharing platform where university students can upload, search, filter, rate, bookmark, and download PDF study notes organized by academic department, course, and year. Built with **HTML5, CSS3, Vanilla JavaScript, PHP, and MySQL** for XAMPP environments.

---

## Table of Contents
1. [Project Overview](#project-overview)
2. [Technology Stack](#technology-stack)
3. [Folder & File Structure](#folder--file-structure)
4. [XAMPP Installation & Setup Guide](#xampp-installation--setup-guide)
5. [Database Import (phpMyAdmin)](#database-import-phpmyadmin)
6. [Default Login Credentials](#default-login-credentials)
7. [Key Features & Flow Walkthrough](#key-features--flow-walkthrough)
8. [University Defense Preparation & FAQ](#university-defense-preparation--faq)
9. [QA Testing Checklist](#qa-testing-checklist)

---

## 1. Project Overview

NoteBridge bridges the academic material gap between university students. Rather than relying on scattered messaging groups or broken file links, NoteBridge provides:
- **Centralized Storage**: PDF study notes categorized by Department and Course.
- **Peer Moderation**: Admin workflow where notes are submitted in `pending` state and reviewed before public listing.
- **Smart Discovery**: Live search, department-to-course dynamic filtering, year filtering, and multiple sort options (Newest, Oldest, Downloads, Title).
- **Interactive Engagement**: 5-star rating system, personal bookmarking (AJAX), and download counting.
- **Personal Dashboards**: "My Notes" manager for uploaders and an administrative dashboard for approval and user management.

---

## 2. Technology Stack

This project strictly adheres to native, beginner-friendly web technologies with zero external build tools, package managers, or heavyweight frameworks:

| Layer | Technology | Purpose |
|---|---|---|
| **Frontend Structure** | HTML5 (Semantic) | Accessible document markup (`<nav>`, `<main>`, `<section>`, `<header>`, `<footer>`) |
| **Frontend Styling** | CSS3 (Vanilla) | Custom Design System with CSS variables, Flexbox, Grid, Glassmorphism, and responsive breakpoints |
| **Icons & Fonts** | Font Awesome 6 CDN & Google Fonts Inter | Professional typography and visual iconography |
| **Client Scripting** | Vanilla JavaScript (ES6) | DOM manipulation, AJAX requests (`XMLHttpRequest`), drag-and-drop file upload, star ratings |
| **Backend** | PHP 8.x with PDO (PHP Data Objects) | Routing, authentication, session management, file handling, and prepared SQL queries (Chapter 5 syllabus) |
| **Database** | MySQL / MariaDB (InnoDB Engine) | Relational database with Foreign Keys, Cascading Deletes, Unique constraints, and Indexes |
| **Web Server** | Apache (XAMPP) | Local development server |

---

## 3. Folder & File Structure

```
Note Bridge/
│
├── admin/
│   └── dashboard.php          # Admin overview: stats, pending approvals, notes & user management
│
├── css/
│   └── style.css              # Unified design system: variables, components, responsive breakpoints
│
├── database/
│   └── notebridge.sql         # Database schema, foreign keys, constraints, and seed data
│
├── includes/
│   ├── config.php             # Database credentials, BASE_PATH, session, and helper functions
│   ├── header.php             # Shared responsive navigation bar, flash messages, <head>
│   ├── footer.php             # Shared site footer and script includes
│   ├── get_courses.php        # AJAX endpoint: returns JSON course list by department
│   └── toggle_bookmark.php    # AJAX endpoint: toggles user bookmark on/off
│
├── js/
│   └── main.js                # Vanilla JS: mobile menu, drag & drop, validation, AJAX bookmarks
│
├── pages/
│   ├── about.php              # Project overview, mission, and team credits
│   ├── bookmarks.php          # Student's saved notes with live removal
│   ├── browse.php             # Search, multi-criteria filter, pagination, star display
│   ├── download.php           # Secure PDF streamer & download increment handler
│   ├── login.php              # Student/Admin authentication with bcrypt verification
│   ├── logout.php             # Secure session destruction and flash notification
│   ├── my_notes.php           # Student dashboard for managing uploaded notes
│   ├── profile.php            # User profile view, bio update, and password change
│   ├── register.php           # New student registration with server-side validation
│   ├── upload.php             # Drag-and-drop PDF uploader with MIME inspection
│   └── view_note.php          # Single note detail, star rating submission, bookmark toggle
│
├── tests/
│   └── test_suite.php         # Automated QA test suite (28 automated tests)
│
├── uploads/
│   ├── .htaccess              # Security: disables script execution inside uploads folder
│   ├── sample_data_structures.pdf
│   ├── sample_sql_guide.pdf
│   ├── sample_calculus.pdf
│   └── sample_web_security.pdf
│
├── index.php                  # Landing page: hero with live stats, features, recent notes
└── README.md                  # Comprehensive manual & defense guide
```

---

## 4. XAMPP Installation & Setup Guide

### Windows & macOS
1. **Download XAMPP**:
   - Visit [https://www.apachefriends.org](https://www.apachefriends.org) and download XAMPP for your OS (PHP 8.1 or PHP 8.2 recommended).
2. **Install XAMPP**:
   - Run the installer. Keep default components (Apache, MySQL, phpMyAdmin).
3. **Copy Project Folder**:
   - Copy the entire `Note Bridge` folder into XAMPP's web root:
     - **Windows**: `C:\xampp\htdocs\Note Bridge`
     - **macOS**: `/Applications/XAMPP/xamppfiles/htdocs/Note Bridge`
4. **Start Apache & MySQL**:
   - Open **XAMPP Control Panel**.
   - Click **Start** next to **Apache**.
   - Click **Start** next to **MySQL**.
   - Both modules will display a green background indicating they are active.

### Linux (Ubuntu / Debian / Fedora)
1. Download `xampp-linux-x64-installer.run` from Apache Friends.
2. Grant execution rights and install:
   ```bash
   chmod +x xampp-linux-x64-8.2.*-installer.run
   sudo ./xampp-linux-x64-8.2.*-installer.run
   ```
3. Copy project into `/opt/lampp/htdocs/Note Bridge`:
   ```bash
   sudo cp -r "/home/dawit/projects/Note Bridge" "/opt/lampp/htdocs/Note Bridge"
   sudo chmod -R 777 "/opt/lampp/htdocs/Note Bridge/uploads"
   ```
4. Start XAMPP:
   ```bash
   sudo /opt/lampp/lampp start
   ```

---

## 5. Database Import (phpMyAdmin)

1. Open your web browser and navigate to:
   ```
   http://localhost/phpmyadmin
   ```
2. In the top navigation menu, click **Import**.
3. Click **Browse...** (or "Choose File") and select:
   ```
   Note Bridge/database/notebridge.sql
   ```
4. Scroll to the bottom and click **Import** (or **Go**).
5. The `notebridge` database will be created automatically with all 6 tables and seed data.
6. Open the website in your browser:
   ```
   http://localhost/Note Bridge/
   ```

---

## 6. Default Login Credentials

The database comes pre-seeded with ready-to-test accounts:

| Role | Full Name | Email Address | Password | Permissions |
|---|---|---|---|---|
| **Admin** | Administrator | `admin@notebridge.com` | `admin123` | Full dashboard access, note approvals, user & note deletion, pending file inspection |
| **Student** | Sarah Chen | `sarah@notebridge.com` | `student123` | Upload notes, view notes, rate notes, bookmark notes |
| **Student** | David Miller | `david@notebridge.com` | `student123` | Upload notes, view notes, rate notes, bookmark notes |

> **Tip**: You can also register a brand-new student account using the **Sign Up** page.

---

## 7. Key Features & Flow Walkthrough

### 1. User Registration & Login
- Passwords are encrypted using PHP's `password_hash($password, PASSWORD_BCRYPT)` with cost factor 10.
- Server validates unique email, minimum 6 characters, and password confirmation.
- Session persists `user_id`, `full_name`, `email`, and `role`.
- Flash messages notify the user on login, logout, and validation errors.

### 2. PDF Upload Workflow
- Navigate to **Upload** (requires login).
- Fill in Note Title, optional Description, Department, Course, and Academic Year.
- Drag and drop a PDF file into the upload dropzone or click to browse.
- Real-time client preview shows the file name and formatted size (e.g., `820 KB`).
- **Backend Security Verification**:
  1. Verifies upload error code is `UPLOAD_ERR_OK`.
  2. Verifies file extension is `.pdf`.
  3. Verifies file MIME type using PHP's `finfo_file` is `application/pdf`.
  4. Enforces 10 MB maximum size limit.
  5. Generates a collision-proof unique filename (`uniqid('note_', true) . '.pdf'`).
  6. Saves row with status `pending`.

### 3. Admin Moderation Workflow
- Log in with `admin@notebridge.com`.
- Click the **Admin** link in the navigation bar.
- The **Pending** tab displays all uploaded notes awaiting review.
- The administrator can:
  - **View Note Details**: Preview the note page.
  - **Download PDF**: Open and verify the submitted document.
  - **Approve**: Immediately publishes the note to the public Browse page.
  - **Reject**: Marks status as rejected.
- In the **Users** tab, the administrator can manage users and delete abusive accounts (which automatically unlinks their physical files on disk before removing database rows).

### 4. Search & Multi-Criteria Filtering
- Navigate to **Browse Notes**.
- Filter by:
  - Keyword (searches both `title` and `description` via SQL `LIKE ?`).
  - Department (Computer Science, Engineering, Mathematics, etc.).
  - Course (dynamically synchronized based on selected Department).
  - Academic Year.
  - Sort Order (Newest, Oldest, Most Downloaded, Title A-Z).
- Results display average star rating, download count, and tags.
- Includes pagination with preserved search filters in query parameters.

### 5. Download & Counting
- Clicking **Download PDF** invokes `pages/download.php?id=X`.
- Verifies the note is approved (or that the downloader is an admin or the note's uploader).
- Atomically increments the `downloads` counter: `UPDATE notes SET downloads = downloads + 1 WHERE id = ?`.
- Cleans any previous output buffer (`ob_end_clean()`) to guarantee zero binary corruption.
- Streams the file with sanitized filename and proper `Content-Type: application/pdf` headers.

### 6. Interactive Star Ratings
- Logged-in students can rate any note from 1 to 5 stars.
- Star icons highlight interactively on mouse hover.
- Submitting a rating uses SQL `INSERT ... ON DUPLICATE KEY UPDATE rating = VALUES(rating)` so students can adjust their previous rating without duplicating database rows.

### 7. AJAX Bookmarking
- Logged-in students can bookmark notes with one click.
- JavaScript sends an asynchronous `POST` request to `includes/toggle_bookmark.php`.
- The button updates instantaneously without a full page refresh.
- On the **Bookmarks** page, removing a bookmark animates the card out of the grid smoothly.

---

## 8. University Defense Preparation & FAQ

When defending this project before university instructors and examiners, be prepared to answer the following architectural questions:

### Q1: "Why did you choose PHP and MySQL rather than a Node.js or Python framework?"
> **Answer**: *"We chose native PHP and MySQL because it represents the core foundation of relational server-side web architectures. By avoiding heavy abstractions like ORMs or heavy build pipelines, every SQL query, session transaction, and HTTP header is written explicitly and transparently. This demonstrates a complete understanding of HTTP request-response lifecycles, database normalization, and web security principles."*

### Q2: "How does NoteBridge protect against SQL Injection?"
> **Answer**: *"We never concatenate user input directly into SQL strings. Every query involving user data uses prepared statements with parameterized inputs via PDO (`$stmt = $db->prepare(...)` followed by `$stmt->execute([...])` as taught in Chapter 5: Database Programming with PDO). Parameter values are transmitted to MySQL separately from the query structure, rendering SQL injection impossible."*

### Q3: "How do you protect against Cross-Site Scripting (XSS)?"
> **Answer**: *"All dynamic data rendered into the HTML document is sanitized through our central `sanitize()` helper function, which applies `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`. This converts `<, >, &, \", '` into harmless HTML entities so injected scripts cannot execute in the browser."*

### Q4: "How are passwords stored securely?"
> **Answer**: *"Passwords are hashed using PHP's standard `password_hash($password, PASSWORD_BCRYPT)`. Bcrypt incorporates an automatic random cryptographic salt and a computationally intensive work factor. We verify passwords on login using `password_verify($password, $user['password'])`, which uses a constant-time comparison algorithm to prevent timing attacks."*

### Q5: "What prevents someone from uploading an executable PHP script masquerading as a PDF?"
> **Answer**: *"We implement defense-in-depth security:
> 1. We inspect the file extension (`$fileType !== 'pdf'`).
> 2. We inspect the actual file MIME type using PHP's `finfo_file` to confirm it is genuine `application/pdf`.
> 3. We rename the file on the server using `uniqid('note_', true) . '.pdf'`, stripping any malicious user-provided filename.
> 4. We placed an `.htaccess` file inside the `uploads/` directory that turns off the PHP engine (`php_flag engine off`) and sets `SetHandler default-handler`, preventing Apache from executing any scripts inside that folder."*

### Q6: "Explain your database normalization."
> **Answer**: *"The database is structured in Third Normal Form (3NF):
> - **1NF**: Every table has a primary key (`id`), and attributes contain atomic values.
> - **2NF**: All non-key attributes are fully dependent on the primary key. Courses and departments are segregated so course data does not repeat department names.
> - **3NF**: Non-key fields are not transitively dependent on other non-key fields. Ratings and bookmarks are isolated into junction tables with composite unique constraints (`UNIQUE KEY (note_id, user_id)`), ensuring referential integrity and zero redundant data."*

---

## 9. QA Testing Checklist

### A. Automated Tests Executed by Test Suite (29 / 29 Passed)
Run `php tests/test_suite.php` to execute these automated checks:

- [x] **TEST 01**: `sanitize()` converts `<` and `>` to safe HTML entities.
- [x] **TEST 02**: `sanitize()` handles quotes and ampersands using `ENT_QUOTES`.
- [x] **TEST 03**: `setFlash()` and `getFlash()` successfully store and retrieve messages.
- [x] **TEST 04**: `getFlash()` automatically clears messages after one retrieval.
- [x] **TEST 05**: `$db` is an active instance of `PDO` (Chapter 5 Database Programming).
- [x] **TEST 06**: `isLoggedIn()` accurately identifies logged-in sessions.
- [x] **TEST 07**: `isAdmin()` returns `false` for standard student accounts.
- [x] **TEST 08**: `isAdmin()` returns `true` for administrator accounts.
- [x] **TEST 09**: `isLoggedIn()` returns `false` after session termination.
- [x] **TEST 10**: Admin seed hash exists in `notebridge.sql`.
- [x] **TEST 11**: Admin seed hash successfully verifies against `admin123`.
- [x] **TEST 12**: Student seed hash exists in `notebridge.sql`.
- [x] **TEST 13**: Student seed hash successfully verifies against `student123`.
- [x] **TEST 14**: `uploads/.htaccess` security file exists.
- [x] **TEST 15**: `uploads/.htaccess` enforces `SetHandler default-handler`.
- [x] **TEST 16**: `uploads/.htaccess` explicitly disables PHP engine (`php_flag engine off`).
- [x] **TEST 17**: Schema creates database `notebridge` with UTF-8 character set.
- [x] **TEST 18**: Schema creates `users` table with role enum (`student`, `admin`).
- [x] **TEST 19**: Schema creates `departments` table with unique name constraint.
- [x] **TEST 20**: Schema creates `courses` table with foreign key `ON DELETE CASCADE`.
- [x] **TEST 21**: Schema creates `notes` table with foreign key constraints.
- [x] **TEST 22**: Schema creates `ratings` table with range check (`1 BETWEEN 5`).
- [x] **TEST 23**: Schema creates `bookmarks` table with composite unique key.
- [x] **TEST 24**: Schema uses idempotent `INSERT IGNORE` statements for safe re-runs.
- [x] **TEST 25**: Zero broken internal `href` links across all 18 PHP files.
- [x] **TEST 26**: Seed file `sample_data_structures.pdf` exists and is readable.
- [x] **TEST 27**: Seed file `sample_sql_guide.pdf` exists and is readable.
- [x] **TEST 28**: Seed file `sample_calculus.pdf` exists and is readable.
- [x] **TEST 29**: Seed file `sample_web_security.pdf` exists and is readable.

---

### B. Manual Verification Checklist (For Evaluation Day)
Follow this step-by-step verification checklist when demonstrating the live system:

1. **Server & Database Initialization**:
   - [ ] Apache & MySQL services started in XAMPP Control Panel.
   - [ ] `database/notebridge.sql` imported in `http://localhost/phpmyadmin`.
   - [ ] Homepage loads at `http://localhost/Note Bridge/` with live counters displaying 3 approved notes and 3 users.

2. **Navigation & Responsiveness**:
   - [ ] Resize the browser down to mobile width (<768px):
     - Hamburger toggle appears.
     - Clicking the hamburger button opens and closes the mobile menu smoothly.
     - Note cards and statistics grids stack into a single column.
   - [ ] Navbar turns frosted with subtle drop-shadow when scrolling down.

3. **Authentication Demonstration**:
   - [ ] Click **Sign Up** and submit an empty form: client-side red error prompts appear.
   - [ ] Register a new student account (e.g., `test@notebridge.com`, password `secret123`).
   - [ ] Redirected to login with green flash message.
   - [ ] Log in with new credentials: username displays in top-right user menu.
   - [ ] Click **Logout**: session destroys and redirects to homepage.

4. **Upload Workflow**:
   - [ ] Log in as a student (`sarah@notebridge.com`).
   - [ ] Navigate to **Upload Note**.
   - [ ] Select Department "Computer Science" — verify Course dropdown filters to CS courses.
   - [ ] Drag and drop a valid PDF file into the dropzone — file name and size appear in green preview box.
   - [ ] Submit form: redirected to **My Notes** with yellow "Pending" status badge.

5. **Search, Filtering, & Rating**:
   - [ ] Navigate to **Browse Notes**.
   - [ ] Type "Data" in Search and submit — only matching notes appear.
   - [ ] Filter by Department "Mathematics" — Calculus note appears.
   - [ ] Click on a note to open its detail page (`view_note.php`).
   - [ ] Hover over stars: stars illuminate gold.
   - [ ] Click star 5 and submit rating: rating saves and average recalculates.
   - [ ] Click **Bookmark**: button toggles to "Bookmarked" via AJAX without page reload.
   - [ ] Open **Bookmarks** page: note appears in saved list. Click **Remove**: card animates away.

6. **Download Counter Verification**:
   - [ ] On note detail page, observe the download count (e.g., 28).
   - [ ] Click **Download PDF**: browser downloads `Data_Structures___Big-O_Summary.pdf`.
   - [ ] Refresh page: download count increments to 29.

7. **Admin Moderation Demonstration**:
   - [ ] Log out, then log in as `admin@notebridge.com` (password `admin123`).
   - [ ] Click **Admin** in navbar to enter the Admin Dashboard.
   - [ ] The newly uploaded student note is visible in the **Pending** tab.
   - [ ] Click **View** or **Download** to inspect the file.
   - [ ] Click **Approve**: note moves to approved status and appears publicly on **Browse Notes**.
   - [ ] Switch to **Users** tab: view all accounts. Delete a test account to verify cleanup.

---

## 10. Authors & Acknowledgments

- **Project**: NoteBridge — University Study Note Sharing Platform
- **Course**: Web Application Development & Database Systems
- **Architecture**: LAMP / WAMP / XAMPP Stack
- **License**: Educational Open Source
