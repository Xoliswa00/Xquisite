# Feature brief — Staff accounts (team logins)

**For the `content-drafter` agent / whoever handles marketing content.** This
feature is **not a PR yet** — it lives uncommitted on branch
`feature/founding-20-automation`. You can't `gh pr view` it, so work from this
brief plus the files listed at the bottom.

Status: **built + reviewed (8-agent) + fixes applied + 17 tests passing.** Awaiting
commit/PR. Safe to draft content now; hold publishing until it merges.

---

## One-line

Give each person who works for you their own login, with the right level of
access — set up in under a minute, no email invite needed.

## The problem it solves

Right now a salon/spa/studio owner on Xquisite has **one login** and everyone
shares it. That means:

- No idea who cancelled a booking, voided a sale, or changed a price.
- The receptionist can see the owner's revenue, billing and settings.
- The owner has to be the one to add every appointment, because staff can't log in.
- You can't safely hand the laptop to a junior.

Every other "add your team" tool assumes each staff member has an email address
and will click an invite link. A lot of SA salon staff don't use email, or won't
complete an invite. So owners just keep sharing one password.

## What we built

1. **A "Staff" screen** under Settings where the owner (or a manager) adds people.
2. For each person: name, a login (their email, or one the owner controls), and a
   **role — Employee or Manager**.
3. The owner **sets a temporary password themselves** (or taps to generate one).
   It's shown on the next screen with a **Copy** button — hand it over in person
   or paste it into WhatsApp. **No email invite, no verification link.**
4. First time that person signs in, they're forced to choose their own password.
5. The owner can later reset a password (new one is shown again, with Copy),
   deactivate someone who's left, or delete them (their history is kept).

## Employee vs Manager (this is the core "controlled access" story)

| | **Employee** | **Manager** |
|---|---|---|
| Bookings & calendar | ✅ | ✅ |
| Customers | ✅ | ✅ |
| Ring up sales / orders | ✅ | ✅ |
| Reports & revenue | ✅ | ✅ |
| Their own work screen on login (not the whole-business dashboard) | ✅ | — sees full dashboard |
| Add / edit / remove staff | ❌ | ✅ |
| Edit products, services & **pricing** | ❌ | ✅ |
| Stock, suppliers, purchase orders | ❌ | ✅ |
| Void a completed sale | ❌ | ✅ |
| Store / business settings | ❌ | ✅ |
| Property Management (leases, rent) | ❌ | ✅ |

Only the **owner** can add or change another Manager's account.

## Why it's different (talking points)

- **No email dance.** You set the password and hand it over. Works for staff with
  no inbox. This is the headline — lead with it for the SA market.
- **One minute.** Name, login, pick a role, done.
- **Real roles, not just "extra logins".** "Employee" genuinely can't see your
  margins or change your prices.
- **Nothing to lose.** Staff who leave get deactivated; their past bookings and
  sales stay on the record.
- **Accountability.** Every action is now tied to a name (audit log already
  exists platform-wide).

## Honesty guardrails — do NOT claim

- Don't say staff get a "personalised dashboard" or "their own calendar view
  showing only their clients." Right now an Employee lands on the shared
  calendar / POS terminal, not a per-person view. Say "they land on their work
  screen, not your business dashboard" — that's true and enough.
- Don't imply fine-grained per-feature permissions in the UI. It's two roles
  (plus a few optional tick-boxes). Keep it to "Employee or Manager."
- Property Management is **Manager-only** for now — don't show an "Employee"
  managing leases.
- Don't promise clock-in/out, shift rosters, commission tracking, or a staff
  mobile app. Those are ideas, not shipped.

## Who it's for

Owners with 2+ people: salons, barbershops, nail & beauty studios, spas, wellness
& fitness studios, and small property managers. Especially: an owner who's tired
of being the only one who can log in, or who's nervous about staff seeing the
money.

## Suggested angles per channel

- **WhatsApp broadcast / IG caption:** "Still sharing one login with your whole
  team? 😬" → the 4-step how-it-works → "no email needed."
- **TikTok POV screen-recording:** record the real flow — add a stylist, set
  password `Salon123`, copy it, show the "they'll change it on first login"
  screen. ~20 seconds. Caption about never sharing a password again.
- **TikTok to-camera:** the pain ("my receptionist could see everything I
  make…") → the fix.
- **Poster:** headline ≤5 words — e.g. **"Stop sharing one login"** — then the
  black "how it works" cards (Add your team / Pick a role / Hand over the
  password / They're in), big gold-gradient "1 min" numeral as the payoff, CTA
  banner to the app. Approved poster system — see `project-poster-design-system`
  memory.

## Screens worth capturing for content

- Settings → **Staff** list
- **Add Staff Member** form (shows the role picker + "Temporary Password
  (optional)" + the plain-language permission labels)
- The **temporary-password card** with the Copy button (appears right after you
  create someone)
- **Staff roles** help page (`/admin/users/team-guide`) — clean side-by-side of
  what each role can do

## Files to read (for accurate copy)

- `suite/app/Http/Controllers/Admin/UserManagementController.php` — the screen
- `suite/resources/views/admin/users/create.blade.php` — the add form + copy
- `suite/resources/views/admin/users/team-guide.blade.php` — the Employee vs
  Manager breakdown, already written in plain language — reuse this wording
- `suite/resources/views/admin/users/show.blade.php` — the temp-password card
- `suite/database/seeders/PermissionRoleSeeder.php` — exactly what each role can do
- `suite/routes/web.php` — search `can:manage-` to see which areas are gated
- `suite/app/Support/PermissionLabels.php` — the human labels for each permission

## Deliverables (into `suite/docs/marketing/`)

- `staff-accounts-content.md` — match `founding-20-content.md` structure
- `staff-accounts-tiktok.md` — match `tiktok-booking-terms.md`
- `staff-accounts-poster.html` + design working files — approved poster system

All drafts for Xoliswa to review. Don't post, don't push, don't open a PR.
