# Inspiration photos on bookings: content pack

Source of truth for this copy: PR #160 (`feature/booking-inspiration-photos`) and
the views it touches: `booking/confirm.blade.php`,
`booking/partials/inspiration-picker.blade.php`, `booking/my-bookings.blade.php`,
`appointments/show.blade.php`, `services/create.blade.php` and `edit.blade.php`,
`emails/appointments/confirmation-booker.blade.php`, plus
`InspirationPhotoService`, `InspirationPhotoController` and the
`booking:prune-inspiration-photos` command. UI names below are quoted from those
views. Anything not established is in [brackets] for Xoliswa.

**Status:** PR #160 is open, not merged. Draft now, publish once it merges and is
deployed. Updated on branch `feature/inspiration-notify-saved-looks` for two
follow-up features (notify on later add, saved looks) and Xoliswa's decisions.
Included in every plan (confirmed by Xoliswa).

**What the feature is:** when a customer books on `/book/{slug}`, the confirm
step has an optional "Have a look in mind?" section. They can add up to 3 photos
(an Instagram or Pinterest screenshot, or something from the camera roll) and
type a short "Describe the look" note. They can add or remove photos later from
My Bookings, right up until the appointment starts. The business sees a "The look
they want" section on the appointment page: thumbnails you can tap for full size,
plus the note. The owner's new-booking email says how many photos are attached
(the photos themselves are deliberately not emailed). Each service has a "Let
clients add inspiration photos" switch, on by default.

**Added since the PR:**
- **Notify on later add.** When a client adds photos from My Bookings after
  booking, the business's staff get an in-app notification and a browser push:
  "New inspiration photos: {client} added 2 inspiration photos for {service} on
  {date}". It links to the booking.
- **Saved looks.** Staff can add up to 3 "after" photos under "How it turned out"
  (button "Add after photos"), then tap "Save as client's look". The appointment
  shows "Saved to {client}'s looks". Saved looks appear in a "Saved looks" section
  on the customer record. Clients see "Your saved looks" in My Bookings, with a
  "Book this look again" button and "Remove". See the saved looks section below.

**What it deliberately is not (so the copy doesn't overclaim):**
- Photos are not emailed. The email only says how many are attached.
- Saved looks are not kept forever: unsaved photos go after 90 days, saved looks
  after 2 years, and the client can remove a saved look any time.

---

## The feature in one line

Clients attach up to 3 photos of the look they want when they book, so you see it
before they walk in.

---

## Landing / section copy

*(Recommended placement: the Bookings feature area of the site, and a one-line
note in the Services screen for owners. Light theme, `#0078D4` blue, `#D4AF37`
gold, same as the rest of the marketing site.)*

### Hero

**See the look before the client walks in.**

Clients can add up to 3 photos and a short note when they book, so you know
whether it's a fade, a full set or a protective style before you start the
appointment. No more asking for a pic on WhatsApp. Included in every plan.

`[ Open Services ]`  ·  already on for every service

### What you get

- **The photos arrive with the booking.** On the confirm step, clients see "Have a
  look in mind?" and can add up to 3 photos, such as an Instagram or Pinterest
  screenshot, plus a note under "Describe the look".
- **Clients can change their mind.** From My Bookings they can add or remove
  photos any time before the appointment starts.
- **One place to look.** Open the appointment and you'll see "The look they want":
  thumbnails you can tap for full size, with their note underneath.
- **A heads-up when it matters.** Your new-booking email says how many photos are
  attached. If a client adds photos later, your staff get an in-app notification
  and a browser push that links to the booking. The photos stay in the app, not in
  your email.
- **Book the same look again.** Save how it turned out to the client's record.
  Next time they tap "Book this look again" and you see it before they arrive.
- **Off where it doesn't make sense.** Each service has a "Let clients add
  inspiration photos" switch. It's on by default. Switch it off for something like
  a consultation.
- **Private by design.** Photos are often pictures of the client, so only that
  client and your staff can see them. Location data is stripped when the photo is
  uploaded, and photos you don't save are deleted 90 days after the appointment.

### Who it's for

- Nail techs, lash and brow artists, braiders and hair stylists, barbers and
  makeup artists, anyone whose job starts with "what are we doing today?"
- Owners who lose time to "send me a pic" messages that land on a personal
  WhatsApp.
- Stylists who want to plan time and products before the client sits down, not
  after.

### How it works

1. **Client books** on your booking page and reaches the confirm step.
2. **They add the look.** Up to 3 photos and a short description. Optional.
3. **You see it** on the appointment page under "The look they want", and your
   new-booking email tells you how many photos came with it.
4. **They can still change it.** Photos can be added or removed from My Bookings
   until the appointment starts. If they add photos later, your staff are
   notified.

### Saved looks: book the same look again

After the appointment, open it and find **How it turned out**. Tap **Add after
photos** (up to 3), then **Save as client's look**. The appointment shows "Saved to
{client}'s looks" and the look appears in the **Saved looks** section on the
customer record.

The client is told straight away ("{business} saved your look from {date} so you
can book it again. You can remove it any time from My Bookings."), and sees it in
My Bookings under **Your saved looks**: "Looks {business} saved for you. Book one
again and they'll see it before you arrive." Two buttons: **Book this look again**
and **Remove**. After photos only appear once the appointment has happened, and a
look needs at least one after photo before it can be saved.

"Book this look again" starts a booking for the same services. On the confirm
step there's a ticked box, "Use your saved look from {date}". It copies the saved
photos and the description onto the new booking, so you see it again before they
arrive. Photos the client adds themselves come first, then the saved ones with the
after-photos leading.

- Saved looks are kept for 2 years after the appointment, and the client can
  remove one any time. Removing it deletes your after photos of them straight
  away, and the look can't be saved again.
- You can't delete a client's own uploads.

### Privacy, in plain words

- Only the client who booked and your own staff can open the photos. They are not
  on a public link.
- Photos are re-saved when uploaded, which removes location and camera data. The
  original is not kept.
- Photos are deleted 90 days after the appointment, or sooner if the appointment
  is deleted. The exception is a look you save to the client's record: saved looks
  are kept 2 years, and the client can remove them any time.

### FAQ

**Do clients have to add photos?**
No. The section is optional. A client can book without adding anything.

**How many photos can a client add?**
Up to 3 per booking, as JPG, PNG or WebP.

**Can they add photos after they've booked?**
Yes. From My Bookings they can add or remove photos until the appointment starts.

**Will I get a notification when a client adds a photo later?**
Yes. Your staff get an in-app notification and a browser push, for example "New
inspiration photos: Thandi added 2 inspiration photos for Gel Overlay on 14 Oct".
It links straight to the booking. Photos added at booking show up in your
new-booking email as a count.

**Are the photos emailed to me?**
No. Your email says how many are attached, and you open the appointment to see
them. That's deliberate, because the photos are often of the client.

**Who can see the photos?**
The client who booked and your own staff. Other clients, other businesses and
anyone with a copied link can't.

**What about location data on the photo?**
It's removed when the photo is uploaded. The original file isn't kept.

**How long do you keep them?**
90 days after the appointment, then they're deleted automatically. If you save a
look to the client's record, it's kept for 2 years, and the client can remove it
any time.

**What is a saved look?**
After an appointment you add up to 3 "after" photos and tap "Save as client's
look". The client sees it under "Your saved looks" in My Bookings and can tap "Book
this look again", which starts a booking for the same services and can copy the
photos and description across.

**What happens if a client removes a saved look?**
It disappears from their list and the customer record, and your after photos of
them are deleted straight away. It's their photo, so it's their call, and you
can't save that look again. You can't delete a client's own uploads either.

**Will it use up my clients' data?**
It's light. The phone shrinks the photo before it uploads, so a large camera photo
goes up as a small file.

**Can I turn it off for some services?**
Yes. Edit the service and untick "Let clients add inspiration photos". It's on by
default.

**Does this cost extra?**
No. It's included in every plan.

### Closing

Your clients already have the look saved on their phone. Let them show you before
they sit down, and next time let them book the same look again.

`[ Open Services ]`

---

## WhatsApp broadcast

> Tired of "send me a pic" messages before every appointment?
>
> Your clients can now add up to 3 photos of the look they want when they book,
> plus a short note. Instagram screenshots, Pinterest saves, camera roll, whatever
> they have.
>
> You see them on the appointment under "The look they want", and your staff get a
> notification if a client adds more later. The photos are private to you and that
> client.
>
> After the appointment, save how it turned out to the client's record. Next time
> they can tap "Book this look again" and you'll see it before they arrive.
>
> It's included in every plan and already on for your services. You can switch it
> off per service, like for consultations.
>
> [link]

---

## Instagram / Facebook caption

> "Can you send me a pic of what you want?"
>
> Every nail tech, braider and barber knows that message. It lands on your
> personal WhatsApp, the client forgets to reply, and you only see the look when
> they sit down.
>
> Now clients can add up to 3 photos and a short note when they book. You see it
> on the appointment under "The look they want", so you can plan your time and
> your products before they arrive.
>
> And once you've done the look, save the after photos to the client's record.
> Next time she taps "Book this look again" and it's already on the booking.
>
> Photos are private to that client and your staff, location data is stripped, and
> photos you don't save are deleted 90 days after the appointment. Included in
> every plan. Switch it off for any service where it doesn't fit.
>
> Link in bio.

**Story / short version:**

> Clients can now show you the look when they book, and book the same look again
> next time. Up to 3 photos and a note, on the appointment, before they walk in.
> Link in bio.

---

## Outreach DM (one to one)

> Hi [name], quick one. Xquisite now lets your clients add up to 3 inspiration
> photos and a short note when they book, so you see the look on the appointment
> before they arrive. No more "send me a pic" on WhatsApp. You can also save how
> the look turned out, so a returning client taps "Book this look again" and you see
> it before she walks in. Included in every plan. Want me to show you where it is?

---

## Email

**Subject line options**

- Your clients can now show you the look when they book (and book it again)
- See the photos before the appointment, not during it
- No more "send me a pic": inspiration photos on every booking

**Body**

> Hi [name],
>
> How many of your appointments start with "can you send me a pic?" or a client
> showing you a screenshot after they've already sat down?
>
> Clients booking on your page can now add up to 3 photos and a short note about
> the look they want. You'll see them on the appointment under "The look they
> want", and your new-booking email will tell you how many came with it.
>
> A few things worth knowing:
>
> - It's optional for the client, and they can add or remove photos from My
>   Bookings until the appointment starts.
> - After the appointment you can save how it turned out to the client's record.
>   They'll see it under "Your saved looks" in My Bookings and can tap "Book this
>   look again", which copies the photos and description onto the new booking.
> - If a client adds photos after booking, your staff get a notification and a
>   browser push.
> - Photos are private to that client and your staff, and location data is removed
>   on upload. Photos you don't save are deleted 90 days after the appointment.
>   Saved looks are kept 2 years, and the client can remove them any time.
> - It's included in every plan.
> - It's on for every service by default. Edit a service and untick "Let clients
>   add inspiration photos" if it doesn't suit, for example a consultation.
>
> [signoff]

---

## Open questions before this goes live

1. **Merge and deploy date.** PR #160 is open, and the notify and saved-looks work
   is on `feature/inspiration-notify-saved-looks`. Hold all posting until both are
   merged and live on production (and `php artisan optimize:clear` has been run,
   per the PR's deploy notes).
2. **Landing page placement.** Section on the Bookings features area, or a short
   mention only? The pack assumes a short section.
3. **Demo content.** Any screen capture or poster should use the Marigold demo
   tenant and stock or demo photos, never a real client's photos.
4. **Poster 90-day line.** The poster says "Photos are deleted 90 days after the
   appointment". That is now true only for photos that aren't saved as a look.
   Suggested replacement: "Photos you don't save are deleted after 90 days."

Resolved: pricing (included in every plan, confirmed by Xoliswa). Payment proofs on
the public disk are covered by PR #161.

---

*All of the above is unposted draft for Xoliswa to review.*
