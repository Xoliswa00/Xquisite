# TikTok — Staff accounts (team logins)

**The feature:** every person who works for you gets their own login instead of
everyone sharing the owner's password. For each person the owner sets a name, a
login, and a role — Employee or Manager. The owner sets a temporary password
(or taps to generate one), it shows on the next screen with a Copy button, and
they hand it over in person or on WhatsApp. No email invite, no verification
link. The staff member picks their own password the first time they sign in.
Employee handles bookings, customers, sales and reports; Manager can also manage
staff, pricing, stock and settings.

**Audience:** SA salon / barbershop / nail / beauty / wellness / spa owners with
two or more staff. The felt pain: everyone logs in as the owner, so nobody knows
who cancelled a booking or changed a price, and the owner is nervous about junior
staff seeing pricing and settings.

**Format:** 9:16 vertical, 1080×1920. About 20–35 seconds. Hook on screen at
0:00, spoken hook by 0:02. Burned-in captions. Trending audio low under a
voiceover, or straight to camera. Soft CTA.

---

## Script 1 — "My receptionist could see everything I make" (to camera + screen inserts)

**Hook (on screen + spoken):** For two years, everyone in my salon logged in as
me. My receptionist could see every rand I made.

**Body (voiceover):**

> She could see my figures. She could change a price. And when a booking got
> cancelled, I had no idea which of us did it.
>
> Now everyone has their own login. *[screen: Settings → Staff list]*
>
> I add a person, pick Employee or Manager, and set a password. *[screen: Add
> Staff Member form, the role dropdown]*
>
> An Employee can take bookings and ring up sales. They can't change my prices,
> void a sale, or open my settings. *[screen: the plain-language permission
> labels on the form]*
>
> No email, no invite link. I set the password and send it on WhatsApp. *[screen:
> the temporary-password card with the Copy button]*
>
> First time she signs in, she picks her own. I never see it again.

**CTA:** Link in bio if your whole team still shares one password.

**On-screen text beats:**
- 0:00 — "Everyone logged in as me for 2 years"
- "She could see everything I make"
- "Now everyone gets their own login"
- "Employee or Manager"
- "Can't change my prices. Can't void a sale."
- "No email invite. I just send the password."
- "She picks her own on first sign-in"

---

## Script 2 — "POV" (screen recording only, no face, trending audio)

**Text at 0:00:** POV: you finally stop sharing one login with your whole team.

**Screen sequence (silent, audio over the top):**
1. Settings → Staff. Tap **Add Staff Member**.
2. Type a name: *Thandi Mkhize*. Login: an email address the owner controls.
3. Role dropdown — pick **Employee**. On-screen note pointing at the permission
   labels: *"Bookings, customers, sales. Not pricing. Not settings."*
4. Temporary Password field — type *Salon123*, or tap generate.
5. Tap **Create Staff Account**. The temporary-password card appears. Tap
   **Copy**.
6. Cut to WhatsApp — paste the password into a chat with "Thandi".
7. Text on screen: *She'll set her own password on first sign-in.*

**End card text:** One login each. Done in a minute.

---

## Script 3 — "Steal my rule for what staff should see" (value, high save rate)

**Hook (on screen + spoken):** Steal my rule for what salon staff should and
shouldn't see. Then I'll show you where to set it.

**Body (voiceover):**

> Here's the line I use:
>
> - Staff can take bookings, add customers, and ring up sales.
> - Staff cannot change prices, void a completed sale, manage stock, or open
>   settings.
> - One senior person I trust gets Manager, so I'm not the only one who can add
>   staff or fix a price.
>
> That's the exact split Xquisite uses. Two roles: Employee and Manager.
>
> In Settings → Staff you add a person, pick the role, set a temporary password,
> and hand it over. No email invite. They choose their own password the first
> time they sign in.
>
> Give your senior person Manager. Everyone else is an Employee.

**CTA:** Save this for the next time you hire.

---

## Caption + hashtags

> If your whole salon still logs in with your password, this is the fix. Everyone
> gets their own login, you choose what each person can see, and there's no email
> invite — you set a temporary password and hand it over. About a minute per
> person.
>
> #salonowner #salontok #smallbusinesssa #salonbusiness #hairsalon #salonlife
> #entrepreneursa #bookingsystem #salonsoftware #beautybusiness #barbershop
> #nailtech

---

## Posting notes

- **Script 2 is the fastest to make** — screen recording plus text, no camera,
  trending audio low.
- **Film Script 1's first line a few ways** and post the strongest. The "she
  could see every rand I made" version is the hook.
- **Pin a comment** with the app link and the Employee-vs-Manager split.
- **Use fake names and demo data** in any screen recording. Record from the
  seeded "Marigold" demo tenant, never a real salon's account.
- **Series potential:** part of a "stop being the bottleneck in your own salon"
  set — team logins, letting staff take their own bookings, handing over the
  front desk, staff accountability. Same format each time.
- **Reply to every "what app is this" comment** — that's the point of the post.

---

## Production status

Script 2's screen recording can be captured from the seeded "Marigold" demo
tenant via the Playwright harness at `suite/tests/Browser/DemoCapture/` at a
1080×1920 viewport. It needs the feature's code on a runnable branch and MySQL
(XAMPP) up. The feature is currently uncommitted on
`feature/founding-20-automation`, so the capture is **pending** — the scripts are
ready to shoot the moment it's on a runnable branch here. Face-to-camera pieces
(Scripts 1 and 3) are Xoliswa's to film.

---

*All of the above is unposted draft for Xoliswa to review.*
