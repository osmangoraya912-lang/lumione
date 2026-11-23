# LumiCore One

Your whole business in one place — a single-user business OS in the browser.
Plain **PHP 8 + MySQL (PDO) + Bootstrap 5**, no frameworks, no React, no Node,
no paid APIs. Bootstrap is vendored locally (`assets/vendor/`), so the app has
**no CDN dependency** and stays fast even on a slow connection.

## What it does

| App | What it is |
| --- | --- |
| **Dashboard** | Your whole business at a glance: product count, sales count, total income, tasks due today, posts planned this week, income-by-month chart (pure CSS bars), recent sales, tasks due today. Every number is computed live from the database. |
| **Products** | All your products in one place. Add / edit / delete, search by name or category, filter by store (goraya244 / mangoraya / WPBay / Other). Click a product name to open its real store link. |
| **Sales** | Every sale, recorded. Add a sale with product, platform, amount, date and an optional buyer note. Totals update automatically. |
| **Income** | All your money in one view: totals by platform (Gumroad goraya244, Gumroad mangoraya, WPBay, Fiverr, Stock images, Other) and totals by month — counted from real sales only. |
| **Tasks** | Your daily work list. Add a task, set a due date and priority, tick it done. Overdue tasks turn red. Filters: All / Today / Open / Done. |
| **Posts** | Your content calendar. Plan a post with platform, date and time, and mark it Posted when it goes live. |
| **Notes** | Quick notes for yourself — rules, ideas, reminders. |
| **Links** | All your important links in one place — stores, profiles, tools. One click opens any of them. |

Everything is explained inside the app: every page starts with 1–2 lines in very
easy English explaining what it does and how to use it.

## Real data from minute one

The installer seeds your **real** data — no fake or sample data anywhere:

- **25 products** — your live Gumroad products (14 goraya244 + 11 mangoraya, with exact prices and real links).
- **6 links** — your Gumroad stores, WPBay Digishop, Fiverr, YouTube and Portfolio.
- **4 tasks** — your real daily schedule (20 Gumroad products, 70 Shutterstock images, 70 Dreamstime images, Adobe Stock Payoneer).
- **2 notes** — your discount-code rules and daily schedule.
- **2 planned posts** — the M-B01 Messaging Trio posts for Threads + Bluesky.
- **0 sales** — you have 0 sales so far, and the app says so honestly. Income starts at $0 until a real sale is added.

## Install

### XAMPP (local)
1. Copy this folder into `htdocs` (e.g. `C:\xampp\htdocs\lumione`).
2. Start Apache + MySQL in the XAMPP control panel.
3. Open `http://localhost/lumione/install.php`, fill in the database details
   (host `127.0.0.1`, port `3306`, database `lumione`, user `root`, empty password
   is the XAMPP default) and create your admin account.
4. **Delete `install.php`** after a successful install, then log in at
   `http://localhost/lumione/login.php`.

### InfinityFree (live demo)
1. Upload the folder into `htdocs` (e.g. `htdocs/lumione/`).
2. In the InfinityFree control panel, create a MySQL database and note the
   host, database name, username and password.
3. Open `http://yourdomain/lumione/install.php` and fill in those details.
4. **Delete (or move out of `htdocs`) `install.php`** after a successful
   install, then log in.

## Security

- Passwords are hashed with `password_hash()` — never stored in plain text.
- All writes use POST + a CSRF token; all output is escaped with `e()`.
- All queries use PDO prepared statements.
- Every app page requires login (`require_login()` in the shared header).
- `uploads/.htaccess` blocks PHP execution from the uploads folder.

## Honest limits

- **No live Gumroad / WPBay / Fiverr sync.** Sales are entered by hand on the
  Sales page; the app does not talk to any platform API. When a sale happens,
  add it there and the income totals update.
- **Single user.** One admin account, created by the installer. There is no
  multi-user or team support.
- **No automatic posting.** The Posts page is a planner/calendar — it does not
  publish to Threads, Bluesky or anywhere else by itself.
- **No email or notifications.** Tasks do not send reminders; check the
  Dashboard each day.

## Files

```
config.php            DB settings (rewritten by install.php)
install.php           Browser installer (delete after install)
schema.sql            Tables + real seed data
index.php             Dashboard
products.php          Product list      product-form.php   Add/edit product
sales.php             Sale list         sale-form.php      Add/edit sale
income.php            Income summary (read-only)
tasks.php             Task list         task-form.php      Add/edit task
posts.php             Post calendar     post-form.php      Plan/edit post
notes.php             Notes             note-form.php      Add/edit note
links.php             Link hub          link-form.php      Add/edit link
login.php / logout.php
includes/             db.php (PDO), functions.php (helpers), header.php (dock + top bar), footer.php
assets/css/style.css  Cream + red luxury theme
assets/js/app.js      Live clock + delete confirmations (no libraries)
assets/vendor/        Bootstrap 5.3.3 + Bootstrap Icons (local, no CDN)
uploads/              Protected by .htaccess
```
