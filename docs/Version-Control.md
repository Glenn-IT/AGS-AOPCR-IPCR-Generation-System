# Version Control — AGS AOPCR/IPCR Generation System

## Rollout Schedule

| Version | Feature                                   | Pages Unlocked                                                         | Pages Still Gated       |
| ------- | ----------------------------------------- | ---------------------------------------------------------------------- | ----------------------- |
| v1.00   | Login / Register / Forgot Password        | index.php, register.php, forgot-password.php                           | All views/\* pages (20) |
| v1.01   | SuperAdmin: Dashboard                     | views/superadmin/dashboard.php                                         | 19 remaining            |
| v1.02   | SuperAdmin: Accounts Management           | views/superadmin/accounts.php                                          | 18 remaining            |
| v1.03   | SuperAdmin: Settings                      | views/superadmin/settings.php                                          | 17 remaining            |
| v1.04   | SuperAdmin: Set Target (OPCR)             | views/superadmin/set-target.php                                        | 16 remaining            |
| v1.05   | SuperAdmin: Review OPCR + Accomplishments | views/superadmin/review-opcr.php, views/superadmin/accomplishments.php | 14 remaining            |
| v1.06   | SuperAdmin: Reports + Account             | views/superadmin/reports.php, views/superadmin/account.php             | 12 remaining            |
| v1.07   | Admin: Dashboard                          | views/admin/dashboard.php                                              | 11 remaining            |
| v1.08   | Admin: Set Target (OPCR) + Review IPCR    | views/admin/set-target.php, views/admin/review-ipcr.php                | 9 remaining             |
| v1.09   | Admin: Accomplishments + Reports          | views/admin/accomplishments.php, views/admin/reports.php               | 7 remaining             |
| v1.10   | Admin: IPCR Form + Account                | views/admin/ipcr-form.php, views/admin/account.php                     | 5 remaining             |
| v1.11   | User: Dashboard                           | views/users/dashboard.php                                              | 4 remaining             |
| v1.12   | User: IPCR Form + Status Tracking         | views/users/ipcr-form.php, views/users/status.php                      | 2 remaining             |
| v1.13   | User: Evidence Upload                     | views/users/evidence.php                                               | 1 remaining             |
| v1.14   | User: Account (Profile) — Full System     | views/users/account.php                                                | None — system complete  |

---

## Under Construction Strategy

Every page not yet unlocked for the current version has this gate as its very first line:

```php
<?php require_once '../../components/under-construction.php'; ?>
```

`components/under-construction.php` defines `CURRENT_VERSION` and renders a full-page
styled card, then calls `exit` so no page content is ever loaded. To unlock a page for
a new version, remove that single gate line and bump `CURRENT_VERSION` in the component.

---

## Git Commands Per Version

```bash
# Stage files changed this version
git add components/under-construction.php views/path/to/unlocked-page.php

# Commit
git commit -m "feat: implement vX.XX - unlock [Feature Name]"

# Tag the version
git tag vX.XX

# Push commit and tag
git push origin main
git push origin vX.XX
```

---

## How Git Tags Work

Each version is a permanent snapshot of the codebase at that presentation state.
Tags are lightweight pointers to specific commits. Even as `main` moves forward,
`git checkout vX.XX` will always restore the exact state that was presented.

---

## GitHub Release Tags

| Version | Tag Name | Commit Hash                              |
| ------- | -------- | ---------------------------------------- |
| v1.00   | v1.00    | 8b747bdb591be583b1877ff9bb9222cde068e27c |
| v1.01   | v1.01    | 2a9b476f658e774a927df80c665b7c02af06f83a |
| v1.02   | v1.02    | 344a2fdeb4ffd63116689a5c739dc011bdb8ebf7 |
| v1.03   | v1.03    | 2deb2d86ed6e93816e7d398f358404ffd0fe6a05 |
| v1.04   | v1.04    | 0350e4eb4e0080649105575850133366d7599449 |
| v1.05   | v1.05    | 358d809d27469cc94d82f94aaac4e482c97cc1c2 |
| v1.06   | v1.06    | 4913c2aea25e944ce436b12be187759a0481689c |
| v1.07   | v1.07    | 4e7a1a84fa36bd17e19b2d0f047a4ac6ca9e5ee5 |
| v1.08   | v1.08    | 72ece51518a1c4560ad93ef0bd56530cb31f2048 |
| v1.09   | v1.09    | f21096a845740dcde6924835573e4b23b2f94bb5 |
| v1.10   | v1.10    | 7f3e54a6eaa536a834a478eecb3914a1b40af508 |
| v1.11   | v1.11    | d30d1599f79c0b9781730f1eac9ccd4603e7101b |
| v1.12   | v1.12    | f0690e2d862969b211a61ee7fc3e2a5e4a18a336 |
| v1.13   | v1.13    | fd11e3897e1da2a9f82d7d8c6b9f1b74cd6bf386 |
| v1.14   | v1.14    | da183858bd34b7b9a1b40bff46c10a019f9add8d |

---

## When a Prof or Client Requests Changes After a Presentation

```bash
# Fix on main first
git checkout main
git add .
git commit -m "feat: update [page] per feedback"
git push origin main

# Delete old tag and re-create it pointing to the new commit
git tag -d vX.XX
git push origin :refs/tags/vX.XX
git tag vX.XX
git push origin vX.XX
```
