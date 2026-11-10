# Claude Context & Project Directives

Please read [AGENTS.md](AGENTS.md) and the comprehensive documentation in [docs/](docs/):
- [PRD](docs/PRD.md)
- [ERD](docs/ERD.md)
- [Tech Spec](docs/TECH_SPEC.md)
- [Tasks & Backlog](docs/TASKS.md)

Strict rules:
- Stack: Laravel 13 (PHP 8.4) + Blade + Tailwind CSS v4.3 + Alpine.js + MySQL 8.0.
- Follow Controller-Service-Model pattern (`app/Services/`).
- Adhere strictly to database schema in `docs/ERD.md`.
- Adhere to Conventional Commits v1.2.0 (`<type>(<scope>): <subject>`) for all commits.
- Anti Out-Of-Topic: Do not implement features outside `docs/PRD.md` without confirmation.
- Task Tracking: Mark tasks `[x]` with date in `docs/TASKS.md` before committing, and commit together in a single commit without ticket IDs in the commit message.
- Explain changes step-by-step for the user.
