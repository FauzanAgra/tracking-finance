# AI Agent Guidelines & Context Directive

> **IMPORTANT**: This repository contains strict architecture, database, and contribution specifications.  
> Any AI agent (Claude, Cursor, Copilot, ChatGPT, Windsurf, Aider, etc.) operating on this codebase **MUST** read and adhere to the project blueprints and governance rules before creating, editing, or committing code.

---

## 📌 Project Architecture & Blueprints

Before executing any task, always read the following documentation files in the `docs/` folder:
1. `docs/PRD.md` — Product Requirement Document (Features, business rules, formulas for DCA, debt installments, and FX conversion).
2. `docs/ERD.md` — Complete Entity Relationship Diagram & Data Dictionary for 12 MySQL tables.
3. `docs/TECH_SPEC.md` — Technical Specification (Controller-Service-Model architecture, third-party API contracts, and conventions).

---

## 🎯 Strict Rules for AI Coding Assistants

### 1. Tech Stack Integrity
* Backend: **Laravel 13** (**PHP 8.4**).
* Frontend: **Laravel Blade + Tailwind CSS v4.3 + Alpine.js**.
* Asset Bundler: **Vite** (via `@tailwindcss/vite`).
* Database: **MySQL 8.0.x** (Local WSL: 8.0.46).
* **DO NOT** migrate or refactor this codebase to React, Vue, Inertia, or Livewire unless explicitly commanded by the user.

### 2. Database Schema Compliance
* Always adhere strictly to `docs/ERD.md`.
* Never invent arbitrary column names or table names. If a new column or table is needed, discuss with the user and update `docs/ERD.md` first.

### 3. Architecture & Service Layer (Clean Code)
* **Skinny Controllers**: Controllers must only handle HTTP request validation calls, trigger the relevant Service, and return a view or redirect response.
* **Service Layer (`app/Services/`)**: All business logic, mathematical calculations (e.g. DCA, unrealized PnL, FX conversion), wallet mutations, and external API calls (Gemini, CoinGecko, Yahoo) **must** reside in dedicated Service classes.
* **Database Transactions**: Every multi-step balance mutation (wallet transfers, debt installments, asset trades) **must** be wrapped in `DB::transaction()` to ensure ACID consistency.

### 4. Git Commit Standards (Strict Conventional Commits v1.2.0)
All git commits made by an AI assistant or developer **must** follow the Conventional Commits specification:
$$\text{Format: } \texttt{<type>(<scope>): <subject>}$$

* **Allowed Types**:
  * `feat`: A new user-facing feature.
  * `fix`: A bug fix.
  * `docs`: Documentation only changes.
  * `style`: Formatting, missing semi-colons, whitespace (no code logic change).
  * `refactor`: Refactoring production code without fixing a bug or adding a feature.
  * `perf`: Performance improvement.
  * `test`: Adding or correcting tests.
  * `build`: Changes affecting the build system, composer, npm, or dependencies.
  * `ci`: Continuous Integration configuration changes.
  * `chore`: Maintenance tasks, config tweaks, or gitignore updates.
* **Subject Rules**:
  * Use the **imperative, present tense mood**: `"add"`, `"fix"`, `"update"`, `"refactor"` (NEVER `"added"`, `"fixing"`, `"adds"`).
  * Do not capitalize the first letter of the subject.
  * Do not place a period (`.`) at the end of the subject line.
  * Max 72 characters on the subject line.
  * **Atomic Commits**: Keep commits focused on a single logical change. Do not bundle unrelated changes together.
* **Examples**:
  * `feat(wallet): add multi-currency balance mutation in WalletService`
  * `fix(dca): correct weighted average price calculation on partial sell`
  * `docs(erd): add debt payments relation table`
  * `refactor(controller): extract exchange rate logic into CurrencyService`

### 5. Scope Guardrails (Anti Out-Of-Topic)
* **Never assume or add unauthorized features**: Only implement features explicitly outlined in `docs/PRD.md` or requested by the user in the prompt.
* **Never install arbitrary external packages** without user consent.
* If user asks to build a new feature not currently in the PRD, propose updating `docs/PRD.md` first before writing application code.

### 6. Pedagogical Persona (Learning-First Approach)
* The user is an aspiring software developer learning Laravel.
* Always explain code modifications clearly and concisely before and after applying changes.
* Break down complex logic (e.g. Eloquent queries, Blade components, JavaScript hooks) with clear educational commentary.
* Write clean, readable, and well-commented code.

### 7. Verification & Safety Directives
* Before committing or claiming completion of a task, always verify syntax and run relevant test/build commands (e.g. `npm run build`, `php artisan test`).
* Never execute destructive Git commands (`git reset --hard`, `git push --force`, deleting untracked files) without explicit user approval.

### 8. Task Board Maintenance (`docs/TASKS.md`)
* Every time a task or sub-task from `docs/TASKS.md` is completed and verified, the AI **must** update `docs/TASKS.md`:
  * Change `[ ]` to `[x]`.
  * Fill in the corresponding Git commit hash in the `Commit` column.
  * Update the completion progress counter at the top of the file (e.g. `1 / 41 Selesai`).
