# Marketing content queue

Running list of shipped / shipping features that need marketing content, and what
already exists. **Whoever handles content (the `content-drafter` agent, or a
content session) works from this file.**

Per-feature deliverable set (all into `suite/docs/marketing/`):
`<feature>-content.md` · `<feature>-tiktok.md` · `<feature>-poster.html` (+ design
working files). Everything is a **draft for Xoliswa to review** — never post,
push, or open a PR.

**Keep this current.** Features come from many parallel sessions. Before drafting,
run `gh pr list --state merged --limit 25` and `gh pr list --state open`, and
skim recent `feat(` commits, then reconcile against the table below.

Legend: ✅ done · ⏳ drafted, needs review · ⬜ not started · — not needed

---

## Outstanding

| Feature | Brief | content.md | tiktok.md | poster | Notes |
|---|---|---|---|---|---|
| **Staff accounts (team logins)** | ✅ `staff-accounts-brief.md` | ⏳ `staff-accounts-content.md` | ⏳ `staff-accounts-tiktok.md` | ⏳ `design/staff-accounts/` → artifact `fc0a822c-b9cc-435e-86bf-8f68f8fe9c62` | Drafted 2026-09-09, needs Xoliswa review. Built + 8-agent reviewed + tested, not yet a PR (branch `feature/founding-20-automation`) — hold publishing until it merges. Lead angle: each staff member gets their own Employee/Manager login, **no email invite** — owner sets the password and hands it over. Open questions in `staff-accounts-content.md`: pricing (per-seat?), Employee revenue visibility, delete-vs-deactivate in UI. |
| Module-aware dashboard + onboarding checklist | ⬜ | ⬜ | ⬜ | — | PR #70 (merged 2026-08-26). New tenants get a guided checklist + a dashboard that only shows their active modules. Small but good onboarding story. Poster probably not needed. |
| Near-live client chat | ⬜ | ⬜ | ⬜ | — | PR #74. Messages to clients now deliver near-instantly. Minor — maybe one IG/WhatsApp post, no poster. |

## Has content already

| Feature | content.md | tiktok.md | poster/flyer |
|---|---|---|---|
| Founding 20 programme | ✅ `founding-20-content.md` | — | — |
| Both-sides referral program | ✅ `referral-program-content.md` | ⬜ | ⬜ |
| Booking terms & cancellation policy (PR #98) | ✅ (in `tiktok-booking-terms.md` context) | ✅ `tiktok-booking-terms.md` | ✅ `flyer-booking-terms.html` + `design/booking-terms-poster.html` |

## Watch list (not merged yet — draft when they land)

- **Service photos** — a parallel session is building photo uploads/galleries for
  services (uncommitted in the tree: `ServicePhoto` model, `ServicePhotoController`,
  admin moderation, public report flow). Good visual feature — worth a poster when
  it merges.
- **Coming-soon page + public Q&A** — open PR #99. Marketing infrastructure
  itself; may not need its own campaign.

---

## How to pick up an item

1. Read the brief if one exists, then read the files it points to.
2. Draft the three deliverables into `suite/docs/marketing/`, matching the
   structure of the existing `*-content.md` / `tiktok-*.md` files.
3. Poster via the `design` skill in the approved system — see the
   `project-poster-design-system` memory.
4. Update this table (⬜ → ⏳), and hand Xoliswa a review list.
