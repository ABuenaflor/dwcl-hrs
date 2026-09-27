# Moving the live site to a permanent hosted database

> **Status: not done yet.** The live site (https://dwcl-hrs.vercel.app) still runs in **demo mode**. It uses a temporary SQLite database that resets to demo data whenever Vercel restarts the app. This guide moves it to a permanent hosted MySQL-compatible database.

## Contents

1. [Which database host to use](#1-which-database-host-to-use)
2. [Part A: set up TiDB Cloud (you)](#part-a--set-up-tidb-cloud-you)
3. [Part B: switch the app to TiDB (developer)](#part-b--switch-the-app-to-tidb-developer)
4. [Part C: connect MySQL Workbench (optional)](#part-c--connect-mysql-workbench-optional)
5. [Troubleshooting](#troubleshooting)
6. [Still to do: uploaded files](#still-to-do-uploaded-files)

---

## 1. Which database host to use

**Recommended: TiDB Cloud Starter.** It's free and needs no credit card.

| | **TiDB Cloud Starter** ✅ | Aiven free MySQL |
|---|---|---|
| Engine | MySQL-compatible (works with Laravel's MySQL driver and Workbench) | Genuine MySQL 8 |
| Free capacity | 5 GiB storage, 50M request units/month, up to 400 connections | 1 CPU, 1 GB RAM, up to 8 GB |
| **When idle** | Scales to zero and **wakes by itself** on the next request | **May be powered off** after a period with no activity |
| **Region** | **You pick it**, so it can sit next to Vercel | No choice of cloud or region |
| Credit card | Not needed | Not needed |

**Why TiDB:**

- A thesis demo can sit unused for weeks. A database that switches itself off when idle would make the site fail in front of the panel. TiDB wakes up on its own.
- You can place the database in the same region as Vercel's servers, which keeps pages fast.

**The risk:** TiDB is MySQL-compatible, not identical to MySQL. That's why step B2 below runs every migration and the full test suite against TiDB before the live site is switched.

**Sources** (checked September 2026; free-tier terms change, so re-check them before you start):
- [Aiven free plan](https://aiven.io/docs/platform/concepts/free-plan)
- [TiDB Cloud: Select a Plan](https://docs.pingcap.com/tidbcloud/select-cluster-tier/)
- [TiDB Cloud Starter limitations and quotas](https://docs.pingcap.com/tidbcloud/serverless-limitations/)
- [TiDB Cloud Starter FAQs](https://docs.pingcap.com/tidbcloud/serverless-faqs/)

---

## Part A: set up TiDB Cloud (you)

### A1. Create the account
1. Go to **https://tidbcloud.com** and click **Sign up**. Signing in with **GitHub** is easiest.
2. Skip any onboarding survey.

### A2. Create the database instance
1. Click **Create Cluster** (it may say **Create Instance**).
2. Choose **Starter** (the free plan).
3. **Cloud provider:** AWS. **Region:** **N. Virginia (us-east-1)**, the same area as Vercel's default region (`iad1`).
4. **Name:** `dwcl-hrs`.
5. Leave the spending limit at **$0** so it can never charge you.
6. Click **Create**. Wait about 30 seconds for the status to show **Available**.

### A3. Get the connection details
1. Open the instance and click **Connect** (top right).
2. In the dialog set:
   - **Connection type:** Public
   - **Connect with:** General
3. Click **Generate Password**. **Copy it right away, because it's shown only once.** If you lose it, generate a new one. That replaces the old password, so any place already using the old one will need the new one.
4. Note these values:
   - **Host**, e.g. `gateway01.us-east-1.prod.aws.tidbcloud.com`
   - **Port**: `4000`
   - **User**, e.g. `3xAbCdEfG.root`. The user has a prefix before `.root`, so copy it exactly.

### A4. Save the details locally (never in chat, never in Git)
Create the file **`dwcl-hrs/.env.tidb`**:

```ini
DB_HOST=gateway01.us-east-1.prod.aws.tidbcloud.com
DB_PORT=4000
DB_USERNAME=xxxxxxxx.root
DB_PASSWORD=your-generated-password
```

`.gitignore` already covers `.env*`, so this file is never committed. Check it with:

```bash
git check-ignore -v .env.tidb     # should print the .gitignore rule that matches
```

Then hand over to Part B, or ask Claude to "do Part B of docs/DATABASE_HOSTING.md".

---

## Part B: switch the app to TiDB (developer)

Run every command below from the `dwcl-hrs/` folder.

### B1. TLS certificate for local commands
TiDB only accepts encrypted (TLS) connections. PHP on Windows has no system list of trusted certificates, so download one:

```bash
curl -sSo storage/cacert.pem https://curl.se/ca/cacert.pem
```

`storage/` is already ignored by `.gitignore` and `.vercelignore`.

The PHP runtime on Vercel runs on Amazon Linux, which ships a certificate bundle at `/etc/pki/tls/certs/ca-bundle.crt`. Check that this path still exists before relying on it (see B6).

The app needs no code change for TLS: `config/database.php` already reads `MYSQL_ATTR_SSL_CA`.

### B2. Create the database, migrate and seed from your laptop
Load the TiDB settings into the shell. Set `DB_DATABASE` in the same shell, because otherwise it comes from `.env` and points at your local MySQL database.

```bash
set -a; . ./.env.tidb; set +a
export DB_CONNECTION=mysql DB_DATABASE=dwcl_hrs MYSQL_ATTR_SSL_CA="$PWD/storage/cacert.pem"
```

Create the database:

```bash
php -r '$p=new PDO("mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT"),getenv("DB_USERNAME"),getenv("DB_PASSWORD"),[PDO::MYSQL_ATTR_SSL_CA=>getenv("MYSQL_ATTR_SSL_CA")]); $p->exec("CREATE DATABASE IF NOT EXISTS dwcl_hrs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"); echo "ok\n";'
```

Build the tables and load the base data:

```bash
php artisan migrate --seed --force
```

The seeders create:
- the DWCL campuses, departments and rubrics
- the HRDO login, `hrdo@dwcl.edu.ph` / `password`
- the demo data, because `APP_ENV` isn't `production`

To load only the base data without the demo data, run the seeders with `APP_ENV=production` instead.

### B3. Prove TiDB compatibility before switching
Run the whole test suite against TiDB, in a **separate** database so the real one isn't wiped:

```bash
php -r '$p=new PDO("mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT"),getenv("DB_USERNAME"),getenv("DB_PASSWORD"),[PDO::MYSQL_ATTR_SSL_CA=>getenv("MYSQL_ATTR_SSL_CA")]); $p->exec("CREATE DATABASE IF NOT EXISTS dwcl_hrs_test"); echo "ok\n";'
DB_DATABASE=dwcl_hrs_test php artisan test
```

**All 16 tests must pass before you continue.** Afterwards, drop `dwcl_hrs_test` from the TiDB console, because it counts toward the free storage.

### B4. Store the credentials in Vercel (encrypted)

```bash
for k in DB_HOST DB_PORT DB_USERNAME DB_PASSWORD; do
  v=$(grep "^$k=" .env.tidb | cut -d= -f2-)
  printf '%s' "$v" | npx vercel env add $k production
done
printf 'mysql'    | npx vercel env add DB_CONNECTION production
printf 'dwcl_hrs' | npx vercel env add DB_DATABASE production
printf '/etc/pki/tls/certs/ca-bundle.crt' | npx vercel env add MYSQL_ATTR_SSL_CA production
npx vercel env ls
```

`npx vercel env ls` should list all seven variables, with `APP_KEY` alongside them.

### B5. Update `vercel.json`
In the `"env"` block of `vercel.json`:

1. **Delete** these two lines. If you don't, they override the TiDB settings from B4 and the site stays on SQLite.
   ```json
   "DB_CONNECTION": "sqlite",
   "DB_DATABASE": "/tmp/dwcl.sqlite",
   ```
2. **Change** `"APP_ENV": "demo"` to `"APP_ENV": "production"`. This stops the seeders from ever loading demo data into the live database.
3. **Add** `"regions": ["iad1"]` at the top level of `vercel.json`, so the function stays in the same region as the database.

`api/index.php` needs no change. Its first-boot seeding only runs when `DB_CONNECTION` is `sqlite`.

Also update the demo-mode notes in `README.md` and in the comment at the top of `api/index.php`.

### B6. Deploy and verify

```bash
git add -A && git commit -m "Use TiDB Cloud as the production database" && git push
npx vercel deploy --prod
```

Checklist:
- [ ] `https://dwcl-hrs.vercel.app` loads, and signing in as `hrdo@dwcl.edu.ph` works.
- [ ] Post a test vacancy, wait a few minutes (or redeploy), and check it's still there. This proves the data is permanent.
- [ ] If pages return **500**, run `npx vercel logs dwcl-hrs.vercel.app` and look for SSL or connection errors (see Troubleshooting).
- [ ] **Change the HRDO password** under *Change password*. The live data is now permanent and the default password is public in the README.
- [ ] Delete the demo accounts under *User Accounts* if the panel no longer needs them.

---

## Part C: connect MySQL Workbench (optional)

1. In Workbench, click **⊕** next to *MySQL Connections*.
2. Fill in:
   - **Connection Name:** `TiDB – dwcl-hrs`
   - **Hostname:** your host from A3
   - **Port:** `4000`
   - **Username:** your user from A3, including the prefix
3. On the **SSL** tab, set **Use SSL** to **Require**.
4. Click **Test Connection** and enter the password.
5. Open the connection. The **`dwcl_hrs`** schema shows the 25 tables.
6. For the ER diagram, use **Database → Reverse Engineer…**, pick this connection and select `dwcl_hrs`.

Workbench may warn that the server version isn't fully supported. Click **Continue anyway**; TiDB reports itself as a MySQL 8.0-compatible server.

---

## Troubleshooting

| Symptom | Cause / fix |
|---|---|
| `Connections using insecure transport are prohibited` | `MYSQL_ATTR_SSL_CA` isn't set, or points to a file that doesn't exist. Recheck B1 or B4. |
| `SSL certificate problem` / `unable to get local issuer certificate` | Wrong CA path. On Vercel, check that `/etc/pki/tls/certs/ca-bundle.crt` still exists for the runtime; `/etc/ssl/certs/ca-bundle.crt` is a common alternative. |
| `Access denied for user` | The username must include the prefix (`xxxx.root`). If the password was lost, regenerate it in TiDB and update `.env.tidb` and the Vercel env var. |
| `Unknown database 'dwcl_hrs'` | Step B2's `CREATE DATABASE` wasn't run. |
| The site still shows demo data after redeploying | `DB_CONNECTION` / `DB_DATABASE` are still in `vercel.json` (see B5). |
| The first request after a long idle period is slow | Normal. TiDB Starter wakes from zero in a moment. |
| Free quota warnings | Check usage in the TiDB console. This app is small, so 5 GiB and 50M request units are far above what a thesis demo uses. |

---

## Still to do: uploaded files

This guide makes the **data** permanent. **Uploaded files** (résumés, certificates, ranking evidence) are still lost when Vercel restarts the app, because they sit in `/tmp`. Vercel also rejects request bodies over about **4.5 MB**, while the app allows 5 MB uploads.

Planned fix: move uploads to object storage, such as **Vercel Blob** (free tier) or any S3-compatible bucket:

- Add the S3 or Blob driver and a `documents` disk in `config/filesystems.php`.
- Change the `Storage::disk('local')` calls in `DocumentController`, `ApplicationDocument`, `RankingScores` and `Applicant\ApplicationController` to use that disk.
- Lower the upload limit to 4 MB, or upload directly to storage from the browser.
