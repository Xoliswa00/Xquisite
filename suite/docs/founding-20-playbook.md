# Founding 20 playbook

How the programme is run, what success means, and the decisions still open. The numbers here are placeholders until Xoliswa confirms them. The ones marked (config) are read by the app from `config/founding_twenty.php`.

## 1. What success looks like

| Measure | Target (config) | Where to see it |
|---|---|---|
| Finished applications | 40 | Admin > Founding 20 > Funnel |
| Selected | 20 | Funnel |
| Logged a first win within 14 days of onboarding | 16 | Funnel, Action queue |
| Still active and paying after the free period | 12 | Funnel |

Two numbers matter most: how many logged a first win in the first two weeks, and how many pay after month 3. Everything else is a way to explain those.

Review the Funnel page weekly. The three questions it answers:
1. Where do people stop? (drop-off by questionnaire section, and unfinished applicants)
2. Which source brings people who finish and stay? (by source)
3. Are we keeping our promise to answer within 7 days? (how fast we answer)

## 2. Nobody is left in silence

| Moment | What happens | Who |
|---|---|---|
| They finish the questionnaire | Acknowledgement email is sent automatically if they gave an email | System |
| No email on file | Appears under "Applications not yet acknowledged" in the Action queue, send the WhatsApp | You |
| You set selected, waitlisted or rejected | Appears under "Decided, but not yet told", one click to WhatsApp or email | You |
| Selected, no deposit after 5 days | Appears under "Selected but no deposit yet" | You |
| Onboarded, no first win after 14 days | Appears under "Onboarded but no first win" | You |
| 76 days into the free period | Appears under "Free period ending soon" with the conversion message | You |

The wording of every message lives in one file, `app/Services/FoundingTwentyMessages.php`, so it can be reviewed in one sitting. WhatsApp messages open pre-written and are sent by you; nothing is sent to anyone's WhatsApp automatically.

## 3. Selecting the 20

The application form favours organised people. The people who need Xquisite most are often the ones who never fill in a form. Aim for:

- 12 to 14 from the form, chosen by score and the "why" answer.
- 6 to 8 approached directly (Admin > Founding 20 > Add a business). Look for a paper diary, a crowded WhatsApp, a salon that misses calls. Write down what you saw in the notes.
- Mix business types unless one type clearly dominates the waiting list.
- Keep 2 places back for referrals from selected businesses.

Only a small share of people who click will finish 8 sections. If the Funnel shows most people stopping at the same section, shorten or reorder that section before spending more on reach.

## 4. Onboarding capacity

Twenty businesses is a lot for one person. Set the pace before launch, not after.

- Onboard in waves of 5, a week apart. Do not accept all 20 deposits and set up everyone at once.
- Budget per business: about 90 minutes of setup and a 20 minute check-in call in week 1, then a short message weekly.
- The first win is the goal of week 1. Agree it with them at setup ("send your first reminder", "take your first online booking") and log it on their application. If it is not logged in 14 days they show up in the Action queue.
- Decide who does what if a business is stuck on a Friday afternoon. A single named person, and a same-day reply promise, is enough.

## 5. Conversion at day 90

The free period ends, and the risk is that they drift rather than decide.

- Day 76: the conversion message goes out (Action queue), stating the price (R200 a month, locked for 24 months) and that the R100 deposit comes back, paid back or credited at their choice.
- Day 90: the final check-in asks what would make them continue or cancel.
- Do not let anyone lapse silently. If they do not answer the day-76 message, phone them.

Decided (Xoliswa, 2026-09-26):
- **Price:** R200 a month, locked for 24 months from the day the free period ends. The app shows the lock date on each application (onboarding date + 3 free months + 24 months). The conversion message states the lock.
- **Deposit:** it always comes back. A business that continues chooses either to be **paid back** to their bank account or to have it **credited** to their account (taken off their first invoice). A business that stops is paid back. We do the accounting for both.

How the deposit is settled (Admin > Founding 20 > an application > Reservation deposit):
1. Record which they chose (they reply "refund" or "credit" to the day-76 message).
2. Paid back: mark it paid back with the EFT reference.
3. Credit: pick one of their unpaid invoices larger than R100. The invoice drops by R100 and gets a note with the deposit reference, so the invoice itself explains the difference.
4. Admin > Founding 20 > Deposits shows received, paid back, credited and still held, and downloads as CSV for the accountant.

Still open: whether to offer an annual option at day 76.

## 6. What competitors have that we do not

From the competitor audit (Fresha, Booksy, SimplyBook.me, Goldie): payment at booking is the biggest gap. It is not part of this programme build. Tell selected businesses plainly that it is coming and ask which payment method their clients actually use, so it is built for them. Proof points (a real salon's numbers, a quote) come from the first wins logged during the programme, so ask each business for permission to use theirs at the day-30 check-in.

## 7. Data and consent

- The privacy policy now covers programme applications and says unselected and unfinished applications are kept for up to 12 months.
- Enforce that with `php artisan founding-twenty:purge-stale` (dry run by default, add `--force` to delete). It never touches selected or onboarded businesses. It is not scheduled. Decide whether to schedule it monthly.
- Businesses added directly need a yes from the owner to being kept on file, and the Add form asks you to confirm it.

## 8. Decisions and placeholders to confirm

1. The success targets in section 1.
2. The decision promise of 7 days (also shown on the thank-you page).
3. The wording of the received, selected, waitlisted, rejected, check-in and conversion messages.
4. The 12 month retention period, and whether to schedule the purge.
5. Whether to tell rejected applicants you will keep them posted (the message currently offers it).

Settled: the R200 price is locked for 24 months, and the deposit is paid back or credited, at the business's choice.
