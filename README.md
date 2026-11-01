# CampusVote

An online voting system built with PHP 8, MySQL and Alpine.js, Axios and Chart.js. A voter logs in with a voter code, picks one candidate, confirms on a review screen, and watches live results. An election officer can open or close voting from an admin page.

![Home page](screenshots/home.png)

![Ballot](screenshots/ballot.png)

![Results](screenshots/results.png)

## Demo logins

| Role | Login | Password |
|------|-------|----------|
| Voter | V1001 to V1008 | voter123 |
| Admin | admin@campusvote.test | admin123 |

The admin page is at `/admin/login.php`.

## Features

**Voters**
- Log in with a voter code and password
- Ballot with candidate cards, a review and confirm screen, and a double click guard
- Live results page that refreshes itself every 5 seconds, with votes, percentages, turnout and a highlighted leader
- Works on phones, tablets and desktops

**Election officer (admin)**
- Open or close voting
- See turnout, results and a list of who has voted (never who they voted for)
- Reset the election

## What it shows (for your resume)
- **Secret ballot design.** The `votes` table stores only the candidate, never the voter. The `has_voted` flag on the voter is what stops a second vote.
- **One person, one vote, even with fast repeat requests.** The vote is claimed with `UPDATE voters SET has_voted = 1 WHERE id = ? AND has_voted = 0` inside a transaction. If no row changes, the vote is refused. I tested this with 5 requests at the same moment and only 1 vote was counted.
- PDO prepared statements, bcrypt password hashing, `session_regenerate_id` on login
- Separate voter and admin sessions
- A small JSON feed (`results.php?json=1`) and `fetch` for live results
- Server side checks for closed elections and invalid candidates

## Run it with XAMPP
1. Start Apache and MySQL in the XAMPP control panel.
2. Copy this folder to `C:\xampp\htdocs\online-voting`.
3. Open http://localhost/phpmyadmin, click **Import**, choose `db/schema.sql`, and click **Import** at the bottom. This creates the `online_voting` database.
4. Open http://localhost/online-voting/

If your MySQL has a password, change it in `config.php`.

## Folder map
- `index.php`, `login.php`, `vote.php`, `results.php`, `logout.php`
- `admin/` election officer pages
- `includes/` shared header and footer
- `assets/` CSS, JavaScript, logo, and `vendor/` (Alpine.js, Axios, Chart.js)
- `db/schema.sql` tables and sample data

The election, candidates and voters are made up. This is a demo and not meant for real elections.
