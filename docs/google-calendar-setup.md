# Connecting the virtual consultation page to Google Calendar

The virtual consultation page shows the times that are free on a Google Calendar and books
into it. This is the one-off setup that lets the website read and write that calendar.
It is done once, by someone who can administer the igniteorthodontics.com Google Workspace.

Roughly 15 minutes. Nothing here changes the website; it only creates the credentials.

## 1. Create the calendar

In Google Calendar, on the account that will own it:

1. **Other calendars → + → Create new calendar.**
2. Name it **Virtual Consultations**, set the time zone to **(GMT-05:00) Eastern Time — New York**,
   and create it.
3. Open **Settings for my calendars → Virtual Consultations → Integrate calendar** and copy the
   **Calendar ID** (it looks like `c_9f3...@group.calendar.google.com`). Send me that.

The team then marks availability by putting free time on that calendar — the site only offers
times that are free.

## 2. Create the Google Cloud project and the service account

At https://console.cloud.google.com, signed in as a Workspace admin:

1. **Create a project** and call it, for example, `ignite-website`.
2. **APIs & Services → Library →** search for **Google Calendar API → Enable**.
3. **APIs & Services → Credentials → Create credentials → Service account.**
   - Name: `ignite-website-calendar`. Create, then **Done** (no roles are needed).
4. Open the new service account, go to **Keys → Add key → Create new key → JSON**.
   A `.json` file downloads. **Send me that file privately** — it is the password to the calendar,
   so do not email it around or put it in a shared drive.
5. On the service account's **Details** tab, copy the **Unique ID** (a long number) — this is its
   **Client ID**. Send me that too, or keep it for step 3.

## 3. Let the service account act as a person (for Meet links)

A Meet link can only be created by a real Workspace user, so the service account has to be
allowed to act as one.

1. At https://admin.google.com go to **Security → Access and data control → API controls →
   Manage domain-wide delegation → Add new**.
2. **Client ID:** the Unique ID from step 2.5.
3. **OAuth scopes:** paste exactly this line:

   ```
   https://www.googleapis.com/auth/calendar
   ```

4. **Authorise.**
5. Decide which Workspace user the bookings should appear to be made by — for example
   `booking@igniteorthodontics.com`. Send me that address. Patients will see appointments
   organised by that person.

## 4. Share the calendar with the service account

Back in Google Calendar:

1. **Settings for my calendars → Virtual Consultations → Share with specific people → Add people.**
2. Paste the service account's email address (it is in the JSON file as `client_email`, and looks
   like `ignite-website-calendar@ignite-website.iam.gserviceaccount.com`).
3. Permission: **Make changes to events.** Send.

## What to send me

- The **Calendar ID** from step 1.3
- The **JSON key file** from step 2.4
- The **Workspace user** to book as, from step 3.5

I install the JSON file on the server outside the public folder (`ignite-data/`, the same place
the database lives, which is never served to the web and never committed to git) and set the
calendar ID and the user in the dashboard under **Settings → Virtual consultations**.

## How it behaves afterwards

- The page offers **15-minute slots, up to 14 days ahead**, only where the calendar is free.
- Booking a slot creates the event with a **Google Meet** link and invites the patient by email.
- The appointment also appears in the dashboard under **Bookings**.
- If the office moves, shortens or deletes an event in Google Calendar, the website follows: that
  time becomes bookable again on the next page load.
- If the credentials ever stop working, the page falls back to asking for preferred times instead
  of showing slots, so it keeps taking enquiries rather than showing an error.
