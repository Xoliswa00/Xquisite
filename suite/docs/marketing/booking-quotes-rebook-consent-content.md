# Quotes from photos, rebook reminders and sharing consent: content pack

Source of truth for this copy: PR #165 (`feature/looks-consent-rebook-quotes`,
merged to `dev` 2026-10-04, already on `main`) and the views it touches:
`booking/my-bookings.blade.php`, `booking/confirm.blade.php`,
`appointments/show.blade.php`, `services/edit.blade.php`,
`customers/show.blade.php`, `emails/appointments/quote-ready.blade.php` and
`emails/appointments/rebook-reminder.blade.php`, plus `SendRebookReminders`,
`AppointmentQuoteService` and `RebookReminderController`. UI names below are
quoted from those views. Anything not established is in [brackets] for Xoliswa.

**Status:** live. All three are included in every plan (confirmed by Xoliswa).

**How this fits the earlier pack:** these three build on inspiration photos and
saved looks. See `booking-inspiration-photos-content.md` and
`booking-inspiration-photos-tiktok.md`. Quotes only make sense because clients can
already add photos; rebook reminders lead to a saved look; sharing consent is asked
on a saved look. Post inspiration photos first if it hasn't gone out, then lead
with quotes.

**Recommended order to post:** quotes from photos first (strongest, and the one
nobody else seems to have), rebook reminders second, sharing consent third as its
own smaller post. One combined email at the end ties them together.

**What the three features are not (so the copy doesn't overclaim):**
- Quotes do not take a deposit or payment. Accepting updates the booking's time on
  the calendar and its price at checkout. Declining cancels with nothing to pay.
- Rebook reminders are one message per completed visit. They only go out for
  visits marked completed, and they never go to a business's whole old client list
  when first switched on.
- Consent is a record of what the client agreed to. It is not a legal sign-off,
  and we do not call it "POPIA compliant".

---

## Feature 1: Quotes from photos

### The feature in one line

Tick one box on a service, and clients who book it get a price and time from you,
based on their photos, before the booking is final.

### Landing / section copy

#### Hero

**Stop pricing in the DMs.**

Long braids, custom nail art and big colour changes can't be priced until you see
the look. Tick "Confirm price and time from the client's photos" on the service.
The client books and adds photos, you send a price, time and note from the booking
page, and they accept in My Bookings. Included in every plan.

`[ Open Services ]`

#### What you get

- **The slot is held while you quote.** The client books and adds their photos. On
  the confirm step they see "Price and time are confirmed from your photos", and in
  My Bookings they see that you're working on it.
- **Your staff are told.** A "Quote needed" alert lands when a client books a
  service that needs one. Open the booking, look at the look they want, and send
  your quote.
- **A price, a time and a note.** You enter the total price, the time needed in
  minutes and an optional note for the client, for example "Waist length knotless
  with beads. Hair included." You can change a quote you've already sent.
- **The client hears about it three ways.** In the app, as a push, and by email
  with the subject "Your quote is ready". They tap **Accept quote** or **Decline**
  in My Bookings.
- **Accepting updates the booking.** The calendar uses the new time and the
  checkout uses the accepted price, so the client, the calendar and the till all
  show the same numbers.
- **Declining cancels it.** The booking is cancelled and there's nothing to pay.
  Owners, managers and the assigned stylist are told either way.
- **A warning if the new time clashes.** If a longer time overlaps other bookings,
  you see a warning before you send. You can still send it.

#### Who it's for

- Braiders, locticians and weavers whose price depends on length, size and how much
  hair is needed.
- Nail techs doing custom art, extensions or soak-offs.
- Colourists and anyone whose real price only shows once they see the photos.
- Owners who answer "how much for this?" at 10pm and then lose the booking in the
  thread.

#### How it works

1. **Owner ticks it on the service.** Edit the service and tick "Confirm price and
   time from the client's photos". Leave other services as they are.
2. **Client books and adds photos.** The slot is held and the client sees "Price
   and time are confirmed from your photos".
3. **Staff send the quote.** They get "Quote needed", open the booking and send
   the price, time needed and a note.
4. **Client accepts or declines.** They get the quote in the app, by push and by
   email, and tap **Accept quote** or **Decline** in My Bookings.
5. **The booking updates.** Accepted: the calendar time and checkout price change
   to match. Declined: it's cancelled with nothing to pay.

#### FAQ

**Which services need a quote?**
Only the ones you tick. It's a switch on each service: "Confirm price and time from
the client's photos". The service's normal price and time are used until the quote
is accepted.

**Does the client pay anything when they book?**
No. Booking holds the slot. Nothing is paid by accepting the quote either; the
accepted price is what shows at checkout.

**What does the client see while they wait?**
"Waiting for {business} to send your price and time. Your slot is held."

**What if I need to change the quote?**
Open the booking and tap "Change the quote", then "Send updated quote". The client
gets the new one.

**What if the client declines?**
The booking is cancelled and there's nothing to pay.

**What if the new time overlaps another booking?**
You get a warning before you send. You can still send it.

**Does the client have to add photos?**
Photos are how you price it, so the confirm step asks for them as for any look in
mind. [Confirm: are photos required for a quote service, or optional? The confirm
step wording is "Optional" for photos in general.]

**Does it cost extra?**
No. It's included in every plan.

#### Closing

Price it once, in the booking, with the photos open. The client says yes with one
tap and the calendar and till already have the right numbers.

`[ Open Services ]`

### WhatsApp broadcast

> "How much for this?" at 10pm, with a screenshot, is how a lot of braiders and
> nail techs still quote.
>
> On Xquisite you can now tick "Confirm price and time from the client's photos" on
> a service. The client books and adds their photos, and their slot is held. You get
> a "Quote needed" alert, look at the photos, and send a price, the time needed and
> a note from the booking page.
>
> They get it in the app, by push and by email, and tap Accept quote or Decline in My
> Bookings. Accepting puts the right time on your calendar and the right price at
> checkout. Declining cancels it with nothing to pay.
>
> It's on any service you choose and included in every plan.
>
> [link]

### Instagram / Facebook caption

> A client sends a photo of waist-length knotless braids and asks "how much?"
>
> Price it too low and you work for free. Book it into a 3-hour slot and the day
> falls apart.
>
> Now you can tick "Confirm price and time from the client's photos" on a service.
> The client books and adds photos. Their slot is held. You send a price, the time
> you need and a note, and they tap Accept quote in My Bookings.
>
> When they accept, your calendar and your checkout update to match. If they
> decline, it's cancelled and they pay nothing.
>
> Included in every plan. Link in bio.

**Story / short version:**

> Price the look from her photos, inside the booking. She accepts in My Bookings and
> your calendar and till update. Link in bio.

### Outreach DM (one to one)

> Hi [name], quick one. Do you ever quote long braids or custom nails over DM? On
> Xquisite you can tick "Confirm price and time from the client's photos" on a
> service. The client books with photos, you send a price, time and note, and she
> taps Accept quote. Your calendar and checkout update when she does. Included in
> every plan. Want me to show you where to tick it?

---

## Feature 2: Rebook reminders

### The feature in one line

Set how many days after a visit a client is usually due, and Xquisite sends them one
"Time for your next {service}?" message.

### Landing / section copy

#### Hero

**Let regulars rebook themselves.**

On each service, set "Remind clients to rebook after" a number of days: 21 for gel
fills, 42 for braids, 14 for fades. When it's due, the client gets one message in
the app, by push and by email, with a link to book. Included in every plan.

`[ Open Services ]`

#### What you get

- **A timing for each service.** Set "Remind clients to rebook after" N days on a
  service. Leave it blank and that service sends nothing.
- **One message, not a campaign.** The client gets one "Time for your next
  {service}?" per completed visit, in the app, by push and by email.
- **A shortcut to their own look.** If you saved their look, the message leads to
  **Book this look again**. Otherwise the button says "Book your next visit".
- **It skips people it shouldn't message.** Clients who have already booked again,
  clients who opted out, and inactive clients or businesses.
- **Switching it on is safe.** Visits that are already 14 or more days past due are
  skipped, so turning it on doesn't message every past client at once.
- **Easy to stop.** Every email has a one-click "Stop rebook reminders" link that
  works without logging in. Clients can also switch it off in My Bookings, under
  "Rebook reminders are on". Appointment reminders aren't affected.

#### Who it's for

- Anyone with a natural cycle: gel and acrylic fills, lash infills, braids, barbers,
  colour touch-ups, facials.
- Owners whose regulars drift for six weeks because nobody nudged them.
- Owners who don't have time to message each client by hand.

#### How it works

1. **Set a number of days** on the service, for example 21 for gel fills.
2. **Complete the visit.** Reminders go out for completed appointments.
3. **When it's due**, the client gets one "Time for your next {service}?" message.
4. **They tap the button.** "Book this look again" if you saved their look, or "Book
   your next visit".
5. **If they've already rebooked**, nothing is sent.

#### FAQ

**When does the message go out?**
Once a day, at 09:00, for visits that have reached the number of days you set.

**What if a visit has several services with different timings?**
The earliest timing is used. [Confirm wording with Xoliswa: the code uses the
shortest `rebook_after_days` among the visit's services.]

**Will it message all my old clients when I switch it on?**
No. A visit that is already 14 or more days past due is skipped, so only recent
visits are picked up.

**What if the client has already booked again?**
They're skipped. No message.

**Can clients turn it off?**
Yes, in one click from the email ("Stop rebook reminders", no login) or from My
Bookings. Appointment reminders keep working.

**Does the client get it by WhatsApp?**
No. It goes in the app, by push and by email. [Confirm: no WhatsApp or SMS channel
for this one.]

**Do I have to mark the visit complete?**
Yes. Reminders are sent for completed appointments.

**Does it cost extra?**
No. It's included in every plan, not an add-on.

#### Closing

The visit you finished three weeks ago is the booking you haven't got yet. Set the
days once per service and let the reminder do the asking.

`[ Open Services ]`

### WhatsApp broadcast

> Your regulars aren't leaving. They just forget to rebook.
>
> On Xquisite you can now set "Remind clients to rebook after" a number of days on
> each service. 21 for gel fills, 42 for braids, 14 for fades, whatever your cycle
> is.
>
> When it's due the client gets one "Time for your next {service}?" in the app, by
> push and by email. If you saved their look, the button is "Book this look again".
>
> It skips anyone who has already rebooked, and clients can stop it in one tap. It
> won't message your whole old client list when you switch it on.
>
> Included in every plan.
>
> [link]

### Instagram / Facebook caption

> Most regulars don't leave. They just don't think about it until their nails have
> grown out.
>
> On each service you can now set "Remind clients to rebook after" N days. Gel fills
> 21, braids 42, fades 14.
>
> When it's due, the client gets one "Time for your next gel fill?" in the app, by
> push and by email. If you saved their look, it leads straight to "Book this look
> again". If they've already rebooked, nothing is sent.
>
> They can turn it off in one click from the email or in My Bookings.
>
> It's included in every plan. Link in bio.

**Story / short version:**

> One "Time for your next fill?" message per visit, sent when it's due. Skips anyone
> who's already rebooked. Link in bio.

### Outreach DM (one to one)

> Hi [name], when does a client normally need you again? On Xquisite you can set
> that per service ("Remind clients to rebook after 21 days"), and the client gets
> one "Time for your next gel fill?" when it's due. Skips anyone who's already
> rebooked. Included in every plan. Want me to show you the setting?

---

## Feature 3: Client consent for sharing a look

### The feature in one line

Clients say yes or no to you sharing their saved look, and you can see the dated
answer on the booking and on their record.

### Landing / section copy

#### Hero

**A dated yes before you post it.**

Each saved look in My Bookings asks the client "Happy for {business} to share this
look?" with Yes, share it and Stop sharing. You see the answer on the booking and
the customer record, with the date. Included in every plan.

`[ Open a customer record ]`

#### What you get

- **The client is asked, on the look itself.** In My Bookings, under "Your saved
  looks": "Happy for {business} to share this look?" with **Yes, share it**. After
  they agree it reads "You're happy for {business} to share this look" with **Stop
  sharing**.
- **You see the answer where you work.** The booking shows "OK to share: {client}
  agreed on {date}" or "Not cleared for sharing". The customer record shows the same
  next to each saved look.
- **A consent history.** The customer record has a "Consent history" with each
  entry and its date and time: a look being saved (client notified), a look being
  removed, sharing granted or withdrawn, and rebook reminders switched on or off.
- **It can't be quietly edited.** The history is append-only. Entries are added when
  something happens and are not rewritten.
- **The client stays in control.** They can stop sharing, or remove the look, at
  any time.

#### Who it's for

- Salons, braiders, nail techs and barbers who post their work on Instagram or
  Facebook.
- Owners who ask clients "can I post this?" over WhatsApp and then can't find the
  reply.
- Anyone with staff posting from the business account.

#### How it works

1. **You save the look** after the appointment (How it turned out, then Save as
   client's look). The client is told.
2. **The client sees the question** in My Bookings: "Happy for {business} to share
   this look?"
3. **They tap Yes, share it** (or leave it).
4. **You see "OK to share: {client} agreed on {date}"** on the booking and the
   customer record. If they haven't, you see "Not cleared for sharing".
5. **They can change their mind.** Stop sharing puts it back to not cleared, and
   the change goes in the Consent history.

#### FAQ

**Does this make me POPIA compliant?**
No. It keeps a record of what the client agreed to and when. What you do with the
photos, and your wider obligations, are still yours. [Xoliswa: confirm whether a
POPIA mention should appear at all in public copy.]

**Does it post the photo for me?**
No. It only records whether the client agreed. Posting is up to you.

**What if the client hasn't answered?**
You see "Not cleared for sharing". Only post the look once the client agrees in My
Bookings.

**Can the client change their mind?**
Yes. They tap Stop sharing, and the booking and customer record show "Not cleared
for sharing" again. The change is in the Consent history.

**What goes in the Consent history?**
A look being saved (client notified), a look being removed, permission to share
being granted or withdrawn, and rebook reminders being switched on or off. Each has
a date and time.

**Does it work for photos the client sent before this?**
It applies to saved looks. [Confirm: nothing is back-filled for looks saved before
2026-10-04.]

**Does it cost extra?**
No. It's included in every plan.

#### Closing

Ask once, in the app, and keep the answer next to the photo. Then posting it is a
check, not a guess.

`[ Open a customer record ]`

### WhatsApp broadcast

> Do you post client photos on Instagram? Can you find where she said yes?
>
> On Xquisite, every saved look now asks the client "Happy for {your salon} to share
> this look?" in My Bookings. She taps Yes, share it, or Stop sharing later.
>
> You see "OK to share: Thandi agreed on 12 Oct" on the booking and her customer
> record, or "Not cleared for sharing" if she hasn't. There's a Consent history on
> the record with the dates.
>
> It keeps a record of what she agreed to. It doesn't post anything for you.
>
> Included in every plan.
>
> [link]

### Instagram / Facebook caption

> "Yes, you can post it" sent on WhatsApp three months ago is hard to find when you
> need it.
>
> Now every saved look in Xquisite asks the client "Happy for {salon} to share this
> look?" in My Bookings. She taps Yes, share it. You see "OK to share: she agreed on
> 12 Oct" on the booking and her customer record.
>
> If she hasn't agreed, you see "Not cleared for sharing". If she changes her mind,
> she taps Stop sharing, and it's all in a Consent history with dates.
>
> It's a record of what she agreed to, kept next to the photo. Included in every
> plan. Link in bio.

**Story / short version:**

> Ask the client once, in the app, and keep a dated yes next to her photo. Link in
> bio.

### Outreach DM (one to one)

> Hi [name], do you post your clients' looks online? Xquisite now asks the client
> "Happy for {salon} to share this look?" on each saved look, and shows you "OK to
> share" with the date, or "Not cleared for sharing". There's a Consent history on
> her record too. Included in every plan. Want me to show you where it sits?

---

## Email (all three)

**Subject line options**

- Price it from her photos: quotes now live in your booking
- Three booking updates: quotes, rebook reminders and a dated yes
- "How much for this?" can now be answered inside the booking

**Body**

> Hi [name],
>
> Three updates to bookings, all included in your plan.
>
> **Quotes from photos.** Tick "Confirm price and time from the client's photos" on
> a service like long braids or custom nails. The client books and adds photos, and
> their slot is held. You get a "Quote needed" alert, then send a price, the time
> needed and a note from the booking page. The client gets it in the app, by push
> and by email ("Your quote is ready"), and taps Accept quote or Decline in My
> Bookings. Accepting puts the new time on your calendar and the price at checkout.
> Declining cancels it with nothing to pay.
>
> **Rebook reminders.** On each service, set "Remind clients to rebook after" a
> number of days, such as 21 for gel fills or 42 for braids. When it's due the client
> gets one "Time for your next {service}?" message. It leads to "Book this look
> again" if you saved their look. It skips anyone who has already rebooked, it never
> messages your whole old client list when you switch it on, and clients can stop it
> in one click.
>
> **Sharing consent.** Each saved look now asks the client "Happy for {your
> business} to share this look?" You'll see "OK to share" with the date, or "Not
> cleared for sharing", on the booking and the customer record, plus a Consent
> history. It keeps a record of what the client agreed to.
>
> All three are in the service and booking screens you already use.
>
> [signoff]

---

## Open questions for Xoliswa

1. **Posting order and timing.** The code is live on main. Post inspiration photos
   first if it hasn't gone out, then quotes, rebook, consent. Is that the order you
   want?
2. **Are photos required for a quote service?** The confirm step calls photos
   "Optional" generally. A quote with no photos would be a guess. Check the confirm
   step for a quote service before the FAQ answer goes public.
3. **Multi-service visits and rebook timing.** The code uses the shortest
   `rebook_after_days` among the visit's services. OK to say so, or leave it out?
4. **Rebook channels.** The notice goes in-app, by push and by email, not WhatsApp
   or SMS. Confirm that's how you want it described.
5. **POPIA wording.** The draft never says "POPIA compliant". Should the public copy
   mention POPIA at all, or only say "a record of what the client agreed to"?
6. **Consent on older looks.** Confirm nothing is back-filled for looks saved before
   2026-10-04, so the "Not cleared for sharing" default is right for them.
7. **Quote expiry.** There's no expiry on a sent quote in the code I read. The slot
   is held until the client answers. Do you want a line about that, or a limit built
   first?
8. **Poster example figure.** The poster's "R1,450" is labelled "An example quote".
   Swap it for a figure that suits the niche you're targeting, such as a braids
   price, if you prefer.
9. **Demo content.** Any screen capture should use the Marigold demo tenant and
   stock or demo photos, never a real client's.

---

*All of the above is unposted draft for Xoliswa to review.*
