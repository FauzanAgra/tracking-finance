# AI Agent Guidelines & Context Directive

> **IMPORTANT**: This repository contains strict architecture and database specifications.  
> Any AI agent (Claude, Cursor, Copilot, ChatGPT, Windsurf, Aider, etc.) operating on this codebase **MUST** read and adhere to the project blueprints before creating or modifying code.

---

## 📌 Project Architecture & Blueprints

Always read the following documentation files in the `docs/` folder:
1. `docs/PRD.md` — Product Requirement Document (Features, business rules, formulas for DCA, debt installments, and FX conversion).
2. `docs/ERD.md` — Complete Entity Relationship Diagram & Data Dictionary for 12 MySQL tables.
3. `docs/TECH_SPEC.md` — Technical Specification (Controller-Service-Model architecture, third-party API contracts, and conventions).

---

## 🎯 Strict Rules for AI Coding Assistants

1. **Tech Stack Integrity**:
   - Backend: **Laravel 13** (**PHP 8.4**).
   - Frontend: **Laravel Blade + Tailwind CSS v4.3 + Alpine.js**.
   - Asset Bundler: **Vite** (via `@tailwindcss/vite`).
   - Database: **MySQL 8.0.x** (Local WSL: 8.0.46).
   - **DO NOT** migrate or refactor this codebase to React, Vue, Inertia, or Livewire unless explicitly commanded by the user.

2. **Database Schema Compliance**:
   - Always adhere to `docs/ERD.md`.
   - Never invent arbitrary column names or table names. If a new column is needed, update `docs/ERD.md` first.

3. **Architecture & Service Layer**:
   - Controllers must remain skinny.
   - All balance mutations, complex calculations, and external API calls must reside in `app/Services/` (e.g., `WalletService`, `DebtService`, `InvestmentService`, `MarketPriceService`, `GeminiReceiptService`).
   - Every balance mutation **must** be wrapped in `DB::transaction()`.

4. **Pedagogical Persona**:
   - The user is an aspiring software developer learning Laravel.
   - Explain code modifications clearly and concisely before and after applying changes.
   - Write clean, well-commented code.
