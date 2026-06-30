# StorefrontZA — Course Platform

A free, account-based web app where people register, work through the StorefrontZA
course step by step, and have their progress saved automatically.

## Stack

- PHP 8.2
- SQLite (single file database — no separate database server needed)
- Plain PHP + sessions for auth (no framework, easy to read and modify)

## Local testing (no hosting needed)

If you have PHP installed locally:

```
php -S localhost:8000 -t public
```

Then open `http://localhost:8000` in your browser.

## Deploying to free staging (Render.com)

1. Go to https://render.com and sign up free (you'll need to do this yourself —
   account creation isn't something done on your behalf).
2. Click **New > Web Service**.
3. Connect your GitHub account and select the `storefrontza` repository.
4. Render will detect the `Dockerfile` automatically — leave the default settings.
5. Choose the **Free** instance type.
6. Click **Create Web Service**.

Render will build and deploy automatically. Every time new code is pushed to the
`main` branch on GitHub, Render redeploys automatically.

**Important — free tier data note:** Render's free tier does not provide persistent
disk storage. This means the SQLite database (and therefore registered users and
their progress) will reset whenever the service restarts or redeploys. This is fine
for QA/testing the registration and login flow itself, but is NOT suitable for real
users yet. When you move to your own VPS, mount a persistent volume for the `data/`
folder so the database survives restarts and deploys.

## Moving to your own VPS later

1. Copy this repo onto your VPS (or `git clone` it directly there).
2. Either run it via the same Dockerfile, or directly with PHP + Apache/Nginx
   pointed at the `public/` folder (same setup pattern as your FundFinder app).
3. Make sure the `data/` folder is writable by the web server user and is NOT
   wiped on deploy — this is where all registered users and progress live.
4. Consider adding regular backups of `data/storefrontza.sqlite` once real users
   are registering — it's a single file, so backing it up is as simple as copying it.

## Project structure

```
public/          — web-accessible files (entry point for the web server)
  index.php      — landing page
  register.php   — sign up
  login.php      — log in
  logout.php     — log out
  dashboard.php  — step-by-step progress tracker
  assets/css/    — stylesheet
includes/        — non-public PHP logic
  db.php         — database connection + table setup
  auth.php       — registration, login, progress helper functions
  modules.php    — the list of course steps shown on the dashboard
data/            — SQLite database lives here (auto-created, git-ignored)
```

## Adding or editing course steps

Edit `includes/modules.php` — it's a single array. Add, remove, or reorder entries
and the dashboard updates automatically; no other code changes needed.
