# Xquisite – Claude Code Instructions

## 5-Angle Multi-Agent Review System

When the user types **`5-review`**, **`5r`**, or asks to "review from all angles" or "check this from every angle", immediately spawn **5 agents in parallel** using the Agent tool, each with a distinct mandate. Do NOT wait — spawn all 5 at once in a single message.

### The 8 Agents

| # | Persona | Mandate |
|---|---------|---------|
| 1 | **Security** | Find auth flaws, injection risks (SQL/XSS/CSRF), data exposure, OWASP top 10, missing validation |
| 2 | **Architecture** | Evaluate structure, coupling, single-responsibility, scalability, long-term maintainability, naming |
| 3 | **UX / Product** | Identify user experience gaps, missing error states, unclear flows, accessibility, mobile edge cases |
| 4 | **Performance** | Spot N+1 queries, missing DB indexes, eager-loading gaps, cache opportunities, slow endpoints |
| 5 | **Devil's Advocate** | Challenge the whole approach — what is fundamentally wrong, over-engineered, or likely to fail? |
| 6 | **Idea Man** | Dream bigger — what else is possible here? What adjacent features, integrations, or improvements does this unlock? Think 10× not 10%. No constraints yet. |
| 7 | **Feasibility Thinker** | Ground the Idea Man — which of those ideas can we build *right now* with current stack/team/time, which need groundwork first, which belong to a future version? Rate each: Now / Next / Later / Never. |
| 8 | **Human Element** | Think and feel like the actual end user. Use patterns from the user's (Xoliswa's) thinking style and prior decisions to predict how a real person will interact with this, what will confuse them, what will delight them, what they'll ignore. |

The Human Element agent has access to collective memory — all interactions, decisions, and thinking patterns observed across sessions — to model the user's mental model and typical user behaviour.

### Synthesis

After all agents return:
1. **Confirmed issues** — things that are definitely wrong (fix these)
2. **Potential risks** — things that could go wrong under certain conditions
3. **What's possible** — Idea Man's top 3 unlockable opportunities
4. **What's feasible now vs later** — Feasibility Thinker's NOW/NEXT/LATER breakdown
5. **Human reality check** — what a real user will actually experience
6. **Looks good** — areas agents validated
7. **Priority order** — rank confirmed issues by impact

### When to proactively offer a review (without being asked)

After completing any of the following, add one line: *"Want an 8-agent review of this?"*
- Implementing a new feature end-to-end
- Making architectural or database schema decisions
- Writing authentication, billing, payment, or permissions code
- A refactor touching more than 3 files
- A new API endpoint or controller

### Keyword triggers

- `5-review` or `5r` → core 5 agents (security, arch, UX, performance, devil)
- `8-review` or `8r` → all 8 agents including Idea Man, Feasibility, Human Element
- `idea` → Idea Man + Feasibility Thinker (what's possible and what's buildable)
- `human-check` → Human Element agent only
- `security-check` → Security agent only
- `arch-check` → Architecture agent only
- `perf-check` → Performance agent only
- `ux-check` → UX/Product agent only
- `devil` → Devil's Advocate only

---

## Project: Xquisite Creations Suite

Laravel + Jetstream SaaS platform. Multi-tenant. Key areas:
- `suite/app/` — Laravel app (Controllers, Models, Services, Notifications)
- `suite/resources/views/` — Blade templates
- `suite/database/` — Migrations, seeders
- Platform billing, tenant modules, monitoring

When referencing code, always use clickable markdown links: `[filename.php](path/to/file.php)`.
