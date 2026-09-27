# DWCL HRDO — Applicant Filtering & Faculty Ranking System

A Laravel 13 rebuild of the thesis *"Applicant Filtering and Faculty Ranking System using Simple Additive Weighting for Divine Word College of Legazpi"* (A. A. Buenaflor, 2024). The original plain-PHP app is kept, unchanged, in the parent folder.

## What it does

| Area | Who | Highlights |
|---|---|---|
| **Careers site** | Public | Vacancy listing with search and filters, vacancy detail showing how applicants are weighted |
| **Applicant portal** | Applicants | One reusable profile (personal data sheet + education), 5-step apply wizard, private document uploads, status tracking, accept/decline offer |
| **Applicant filtering (SAW)** | HRDO | Per-vacancy criteria weights, auto-scored criteria + HR ratings, live re-ranking, printable decision matrix (raw → normalized → weighted), shortlist → interview → offer pipeline with notifications |
| **Faculty ranking** | Faculty, DRC, CRTC/BERTC, VP, HRDO | Self-rating against the Faculty Manual rubric with evidence per claim, point caps enforced automatically, the full DRC → Council → VP → President → certificate workflow with return-for-revision and an audit trail, printable Certificate of Rank |
| **Administration** | HRDO | Account approval, committee accounts, editable rubrics and rank thresholds, campuses and departments |
| **Reports** | HRDO | Charts by month, status, department and campus, date/campus/vacancy filters, CSV exports, print view |

### Simple Additive Weighting

For the applicants *i* of one vacancy and criteria *j* (`app/Services/SawRanker.php`):

1. Decision matrix `x[i][j]`.
2. Normalize: benefit `r = x / max(x)`; cost `r = min(x) / x`.
3. Preference value `V[i] = Σ w[j] · r[i][j]`. Weights are re-scaled so they sum to 1.
4. Rank by `V` in descending order. Ties share a rank.

Default criteria (editable per vacancy): Experience 30%, Education 20%, Trainings & Certificates 10% (these three are auto-scored from the application), plus Technical Skills 20%, Soft Skills 10% and Interview 10% (rated 0–10 by HRDO).

## Requirements

- PHP 8.3+ with `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`
- Composer 2, Node 20+
- MySQL 8 (MySQL Workbench for administration)

## Setup

```bash
cd dwcl-hrs
composer install
npm install && npm run build
cp .env.example .env        # already done in this repo
php artisan key:generate    # already done in this repo
```

**Create the database.** In MySQL Workbench, connect to your server and run:

```sql
CREATE DATABASE dwcl_hrs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Put your MySQL password in `.env` under `DB_PASSWORD=`, then run:

```bash
php artisan migrate --seed     # schema, DWCL departments, rubrics, rank thresholds, demo data
php artisan serve              # http://localhost:8000
```

**Alternative (Workbench only):** open `database/mysql/dwcl_hrs.sql` with *File → Open SQL Script* and execute it. It creates the full schema and reference data, but no demo data. To get the EER diagram, use *Database → Reverse Engineer* on `dwcl_hrs`.

Demo data is skipped when `APP_ENV=production`.

## Accounts

The password for every account below is `password`. **Change the HRDO password after you first sign in.**

| Role | Email |
|---|---|
| HRDO Administrator | `hrdo@dwcl.edu.ph` |
| Department Ranking Committee | `drc@dwcl.edu.ph` |
| Rank & Tenure Council | `crtc@dwcl.edu.ph` |
| VPAA / VPBE | `vp@dwcl.edu.ph` |
| Faculty (College, submitted) | `faculty@dwcl.edu.ph` |
| Faculty (College, at Council) | `nursing@dwcl.edu.ph` |
| Faculty (Basic Ed, draft) | `teacher@dwcl.edu.ph` |
| Faculty (pending approval) | `newhire@dwcl.edu.ph` |
| Applicant | `applicant@example.com` |

## Before going live: verify the Tertiary rubric

- **Basic Education rubric:** copied from the legacy self-rating form (Faculty Manual, 2017 revision).
- **Tertiary rubric, section 1 (Educational Attainment):** copied from the legacy screen.
- **Tertiary rubric, sections 2–7:** the legacy system kept these only in its database, and no dump of that database was in the repository. They were rebuilt as a reasonable starting structure. **Check them against the Faculty Manual.** You can change them under *Rubrics & Ranks* without touching code.
- **Academic rank point thresholds:** these are placeholders. Adjust them on the same page.

## Architecture

```
app/
  Enums/            Role, statuses, levels — workflow rules live here (e.g. RankingStatus::next/actor)
  Services/         SawRanker, RubricCalculator (caps), RankingWorkflow (hand-offs + notifications)
  Actions/          SaveApplicantProfile (shared by profile page and apply wizard)
  Http/Controllers/ Public careers, Applicant/*, Faculty/*, Admin/*
  Notifications/    Alert — one in-app notification type for every event
resources/views/
  components/       Design system: field, badge, stat, modal, steps, chart, layouts (app/public/auth/print)
  rankings/         Shared rubric score sheet (faculty + committees), live cap-aware totals
database/
  migrations/       25 tables with foreign keys and unique constraints
  seeders/          DWCL organization, rubrics, demo data
  mysql/            Workbench-ready SQL script
```

**UI.** Tailwind CSS 4 with a navy-and-gold corporate theme, Alpine.js and Chart.js. Page changes use cross-document View Transitions: only the content area animates, while the sidebar and top bar stay in place. A progress bar shows while a page loads. Links are prefetched on hover through the Speculation Rules API, and form buttons show a loading state. Browsers without View Transitions get a CSS fallback animation. Motion is disabled when the user has `prefers-reduced-motion` set.

## Changes from the legacy system

| Problem in the legacy system | Fix |
|---|---|
| Passwords stored in plain text | Stored as bcrypt hashes. Login attempts are rate-limited. |
| SQL built by string concatenation (SQL injection) | All queries go through Eloquent or the query builder with bound parameters. |
| No CSRF protection | CSRF protection on every form |
| Uploads in a public folder | Uploads on a private disk, sent only to the owner or authorized staff |
| Government ID numbers stored in plain text | Government ID numbers encrypted at rest |
| Duplicate applications (a bug the legacy commits tried to fix) | Unique constraint on (applicant, vacancy) |
| Point scoring that only checked whether a field was filled | Normalized SAW with a weight for each criterion |
| Ranking stored one score and never enforced caps | Three score columns (SR / DRC / Council). Caps are enforced for each rubric node. |
| No review workflow | The full review workflow, with an audit trail |
| Rubric criteria hard-coded | Rubric criteria are data that HRDO can edit (thesis recommendation #2) |
| Two separate login pages | One login page for every role |

## Tests

```bash
php artisan test
```

16 tests with 103 assertions. They cover SAW arithmetic (checked by hand), ties and withdrawals, rubric caps, the complete ranking workflow up to the certificate, returning a ranking for revision, account approval, the applicant pipeline and offer response, document privacy, role boundaries, and a render check of every screen. The suite passes on both SQLite and MySQL 8.
