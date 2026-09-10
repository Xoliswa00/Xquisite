# Staff accounts (team logins) — content pack

Source of truth for this copy: `resources/views/admin/users/*.blade.php`
(`create`, `edit`, `show`, `team-guide`), `app/Http/Controllers/Admin/UserManagementController.php`,
`database/seeders/PermissionRoleSeeder.php`, `app/Support/PermissionLabels.php`,
and the feature brief `staff-accounts-brief.md`. Everything below matches what the
code actually does — no invented capabilities. Anything unconfirmed is bracketed
for Xoliswa.

**Status:** built and reviewed on branch `feature/founding-20-automation`, not yet
a PR. Draft now, publish once it merges.

**What the feature is:** every person who works for you gets their own login
instead of everyone sharing the owner's. For each person you set a name, a login,
and a role — Employee or Manager. You set a temporary password yourself (or
generate one), and hand it over in person or on WhatsApp. There is no email
invite and no verification link, so it works for staff who don't use email. The
first time that person signs in, they choose their own password.

---

## The feature in one line

Give every staff member their own login with the right level of access, Employee
or Manager, set up in under a minute, with no email invite.

---

## Landing / section copy

*(Recommended placement: a section on the features area of the site, and an
in-app prompt on the dashboard for owners who still have only one user. Light
theme, `#0078D4` blue, `#D4AF37` gold, same as the rest of the marketing site.)*

### Hero

**Give every staff member their own login**

Everyone in your business has been sharing one password. Now each person gets
their own login with the right level of access: Employee for the floor, Manager
for running the business. You set it up in under a minute, and there is no email
invite. You set a temporary password and hand it over.

`[ Open Settings → Staff ]`  ·  about a minute per person

### What you get

- **Their own login, not your password.** Every booking, sale and change is
  recorded against a name, so you can see who did what.
- **Two clear roles.** An Employee handles day-to-day work: bookings, the
  calendar, customers, sales and reports. A Manager can do all of that and also
  manage staff, edit products, services and pricing, manage stock and suppliers,
  void a completed sale, and change business settings.
- **No email invite.** You set a temporary password, or tap to generate one, then
  copy it and hand it over in person or paste it into WhatsApp. Nothing is sent
  to the staff member and there is no link to click.
- **They pick their own password on first sign-in.** After that, you don't know
  it.
- **People who leave get switched off, not deleted.** They can no longer sign in,
  but their past bookings and sales stay on the record against their name. You can
  reactivate them later.
- **Reset a forgotten password yourself.** The new one is shown once, with a Copy
  button, the same way the first one was.

### Who it's for

- You run a salon, barbershop, nail or beauty studio, spa, or wellness and
  fitness studio with two or more people.
- You're still the only one who can log in, so every appointment has to go
  through you.
- Your team shares one password and you can't tell who cancelled a booking or
  changed a price.
- You want staff to book clients and ring up sales without being able to change
  what you charge, see your supplier costs, or open your settings.

### How it works

1. Open **Settings → Staff** and tap **Add Staff Member**.
2. Enter their **name** and a **login**. Use their own email, or one you control
   if they don't have one.
3. Pick a **role**, Employee or Manager. Set a **temporary password** or tap to
   generate one.
4. **Hand over the password.** They sign in, choose their own, and they're in.

### Employee or Manager

| | Employee | Manager |
|---|---|---|
| Bookings and the calendar | Yes | Yes |
| Customers | Yes | Yes |
| Ring up sales and orders | Yes | Yes |
| Reports and revenue | Yes | Yes |
| Add, edit and remove staff | No | Yes |
| Edit products, services and pricing | No | Yes |
| Stock, suppliers and purchase orders | No | Yes |
| Void a completed sale | No | Yes |
| Store and business settings | No | Yes |
| Property Management (leases, rent) | No | Yes |

Only the **owner** can add or change another Manager's account.

### FAQ

**Do my staff need an email address?**
No. If a staff member doesn't use email, put in an email address you control as
their login. Nothing is sent to it and there is no verification link. You set the
password and hand it over.

**What's the difference between Employee and Manager?**
An Employee does the day-to-day work: bookings, the calendar, customers, sales
and reports. A Manager can do all of that and also manage staff, edit products,
services and pricing, manage stock and suppliers, void a completed sale, and
change business settings. Property Management, if you use it, is Manager only.

**Can an Employee see how much the business makes?**
An Employee can open reports and revenue, but cannot see your supplier or cost
prices, change your prices, or void a completed sale. [Xoliswa: see open question
2 — do you want an option to hide revenue from Employees?]

**Can I change someone's role later?**
Yes. Open their staff page, tap Edit, and change the role.

**What happens when a staff member leaves?**
Deactivate their account. They can't sign in, but their past bookings and sales
stay linked to their name. You can reactivate the account later or delete it
entirely; the work history is kept either way.

**Who can add staff?**
The owner and any Manager. Only the owner can add or change another Manager's
account.

**Where do staff sign in?**
At the same web address you use, on any phone or computer. There is no separate
staff app.

**Does this cost extra?**
[Xoliswa: are staff accounts included in the plan, or priced per person? Copy
currently doesn't say.]

### Closing

You've run the whole business on one login long enough. Give your team their own.

`[ Open Settings → Staff ]`

---

## WhatsApp broadcast

> Still sharing one login with your whole team? You can now give each person their
> own.
>
> Open Settings → Staff, add their name, pick Employee or Manager, and set a
> temporary password. Hand it over in person or on WhatsApp. No email invite, so
> it works even for staff with no inbox. About a minute per person.
>
> Employee can take bookings, manage customers and ring up sales. Manager can
> also manage staff, products and pricing, and settings. Staff who leave get
> switched off, and their history stays on the record.
>
> [link]

---

## Instagram / Facebook caption

> Still sharing one login with your whole team?
>
> A booking gets cancelled or a sale gets voided and there's no way to tell who
> did it. Staff who use your login can see settings and figures that are really
> yours alone.
>
> Staff accounts fix that. Each person gets their own login with the right level
> of access: Employee for the floor, Manager for running the business. You set it
> up in under a minute, and there's no email invite. You set a temporary password
> and hand it over, so it works for staff who don't use email. They choose their
> own password the first time they sign in.
>
> Link in bio.

**Story / short version:**

> One password for the whole team? Give each person their own login (Employee or
> Manager) in under a minute. No email invite. Link in bio.

---

## Outreach DM (one to one)

> Hi [name], quick one. Xquisite can now give each of your staff their own login
> instead of everyone sharing yours. You pick Employee or Manager for each
> person, set a temporary password and hand it over. No email invite, so it works
> for staff without an inbox. About a minute each. Want me to show you where it
> is?

---

## Email

**Subject line options**

- Give your team their own logins
- Stop sharing one password with your whole team
- Staff accounts are ready: Employee or Manager, a minute to set up

**Body**

> Hi [name],
>
> Until now, everyone in your business has shared one Xquisite login. That means
> no record of who cancelled a booking or changed a price, and staff can reach
> settings and figures that are really yours alone.
>
> You can now give each staff member their own login. For each person you enter a
> name, a login, and a role:
>
> - Employee: bookings, the calendar, customers, sales and reports.
> - Manager: all of that, plus staff, products and pricing, stock and suppliers,
>   voiding a sale, and business settings.
>
> There's no email invite. You set a temporary password, or generate one, and
> hand it over in person or on WhatsApp. The first time they sign in, they choose
> their own password. Staff who leave get switched off, and their history stays
> on the record.
>
> It's in Settings → Staff, and it takes about a minute per person.
>
> [signoff]

---

## Open questions before this goes live

1. **Pricing.** Are staff accounts included in the plan, or charged per person?
   All copy currently avoids the question.
2. **Employee revenue visibility.** The Employee role currently includes "View
   reports and revenue" (`PermissionRoleSeeder.php`). The brief's problem
   statement leads with owners being "nervous about staff seeing the money," but
   an Employee can still open revenue reports — they just can't see cost prices,
   change prices, or void a sale. Do you want a tick-box to hide reports and
   revenue from Employees? Until there is one, the copy can't claim staff won't
   see turnover, and it doesn't.
3. **Nav label and path.** In-app the link sits under **Settings → Staff**
   (`admin.users.index`, gated by `manage-staff`). Note there is a *separate*
   "Staff" item under Bookings (`staff.index`) for practitioner records — a
   different screen. Confirm the copy should say "Settings → Staff" and whether
   that second item ever causes confusion worth addressing.
4. **Delete vs deactivate in the UI.** `show.blade.php` offers Deactivate and
   Reactivate; the brief also mentions deleting a person (history kept). Confirm
   delete is actually exposed in the UI so the FAQ line is correct.
5. **"Extra permissions" tick-boxes.** The add/edit forms have an optional "Extra
   permissions" fieldset (e.g. grant one Employee "Manage products, services and
   pricing"). Per the brief's honesty guardrail, all copy stays at "Employee or
   Manager" and doesn't mention this. Confirm that's how you want it marketed.
6. **CTA target.** Copy says "Open Settings → Staff". Is there a help article or a
   deep link you'd rather point owners to, especially new ones?

---

*All of the above is unposted draft for Xoliswa to review.*
