# Zone-Wise Delivery — Feature Walkthrough & Test Plan

**For:** QA / Tester
**Date:** 2026-04-23
**Status:** Partially implemented — test all scenarios below; known gaps are flagged in §7.

---

## 1. What this feature does (in plain language)

Before this change, delivery availability was decided at the **city** level — if your saved city matched a city in our system, you could order. That was too coarse.

Now, an admin can draw one or more **Zones** inside a city on a map. A zone is a shape (either a free-drawn **polygon** or a **circle**). When a customer adds or edits a delivery address, the system checks whether their pinpoint (latitude/longitude) falls **inside any active zone**. The rules:

- **Inside a zone** → address saved, delivery allowed.
- **Outside every zone** → the system falls back to matching the customer's typed city name against the city list. If that matches, the address is still saved. If neither zone nor city-name matches → address rejected with "We do not deliver food at the selected location!"
- **Zones in the same city cannot overlap** with each other.
- **Zone names must be unique within a city** (e.g. you can't have two "North Zone" in Bhuj, but you can have "North Zone" in Bhuj and another "North Zone" in Mumbai).

---

## 2. What's new in the UI

### Admin panel

- **New menu item:** *Zones* — appears in the left sidebar, above "Deliverable Area".
- **New page:** *Manage Delivery Zones* (at Zones in sidebar) — paginated list of all zones with filters.
- **Updated page:** *Deliverable Area* → renamed to *Delivery Zones* / *Create Delivery Zone for City*. This is the map-drawing screen. It now loads Google Maps with drawing tools and shows **existing zones for the selected city as an overlay** so you can see what's already taken.

### Customer app

- No new screens. Address Add and Address Update screens behave the same, but the **acceptance rules change**:
  - Addresses may now be accepted or rejected depending on whether the pinpoint lands inside a configured zone.
  - The error message when it fails is: **"Sorry! We do not deliver food at the selected location!"**

---

## 3. Pre-test setup checklist

Before starting, confirm these are in place:

- [ ] Admin account with permission to read/update cities.
- [ ] At least **two cities** already exist in the system (so you can test cross-city behaviour).
- [ ] A valid **Google Maps API key** configured (with the Drawing library enabled, otherwise the drawing toolbar won't appear).
- [ ] At least one **branch** in each city you plan to use (needed for the branch nearest-search and order flow).
- [ ] A test customer account on the mobile app or a way to call the APIs directly (Postman/cURL).
- [ ] A second browser/tab for the customer side so you can switch quickly between admin changes and customer behaviour.

---

## 4. Admin flow — creating and managing zones

### 4.1 Navigating to the Zones list

1. Log in to Admin.
2. Click the **Zones** item in the left sidebar.
   - **Expected:** Manage Delivery Zones page opens. Breadcrumb shows **Home → Zones**.
3. Observe the page controls:
   - **Filter by Status** (Active / Inactive).
   - **Filter by City** (dropdown of all cities).
   - A **"Create New Zone"** button on the top right.
   - A table with columns: ID, City, Zone Name, Status, Created Date, Actions.

**Verify:** If no zones exist yet, the table is empty. If zones exist, they paginate correctly and filters reduce the list.

### 4.2 Creating a polygon zone (happy path)

1. From Manage Delivery Zones, click **Create New Zone**.
   - **Expected:** Map page opens. Breadcrumb: **Home → Zones → Delivery Zones**. Instruction block explains the rules.
2. In **Select City**, pick a city.
   - **Expected:** The map re-centers on that city. If there are already zones in this city, they render as coloured overlays. Each existing zone has a delete (×) button near its top-right corner.
3. Enter a **Zone Name** — e.g. `North Zone`.
4. Select **Geolocation Type → Polygon**.
5. Use the polygon tool on the map and click at least 3 points to close a shape, fully inside the city.
6. Click **Save**.
   - **Expected:** Success message. You are redirected back to the Manage Delivery Zones list. The new zone is visible with status Active.

### 4.3 Creating a circle zone

1. Same flow as §4.2 but choose **Geolocation Type → Circle**.
2. Click a centre point on the map, then drag outward to set the radius.
3. Save.
   - **Expected:** Zone created, appears in list, visible as a circle when you re-open that city on the map.

### 4.4 Editing a zone (status toggle)

1. In Manage Delivery Zones, find a zone row.
2. Toggle its **Status** (Active ↔ Inactive).
   - **Expected:** Status flips immediately. A success message is shown.
3. While it's **Inactive**, try adding a customer address whose pinpoint lies inside that zone.
   - **Expected:** The inactive zone should **not** match — the address should follow the "no zone matched" fallback.

### 4.5 Deleting a zone

Two ways:

**A. From the map page**
1. Open Create Delivery Zone, pick the city that contains the zone.
2. Click the × delete button on the existing zone overlay (or click the zone and use "Delete Zone" in the popup).
3. Confirm the prompt.
   - **Expected:** Zone disappears from the map and is removed from the list in Manage Zones.

**B. From the list page**
1. Use the row action in Manage Delivery Zones.
   - **Expected:** Same result.

### 4.6 Map helper buttons

On the Create Delivery Zone page:

- **Clear Map** — should clear both your current drawing AND the existing-zone overlays. Confirmation message: *"Map cleared. You can draw a new zone."*
- **Restore Previous Zone** — should bring back the last cleared shape with message *"Previous zone restored."* If nothing was drawn/cleared, message is *"No previous zone to restore."*

---

## 5. Admin — negative / validation tests

Every one of these should **be rejected** with a clear error message. Do not save to DB on failure.

| # | Test case | Expected error |
|---|-----------|----------------|
| 1 | Save without selecting a City | "City is required" (or equivalent validation message) |
| 2 | Save without a Zone Name | "Zone Name is required" |
| 3 | Save without drawing any shape | "Draw zone boundaries! Invalid latitude or longitude in boundary points." |
| 4 | Draw a polygon with fewer than 3 vertices | Rejected — invalid shape |
| 5 | Enter a Zone Name that already exists **in the same city** | "Zone name already exists in this city! Please provide a unique zone name." |
| 6 | Enter a Zone Name that exists **in a different city** | Should **succeed** — uniqueness is per-city. |
| 7 | Draw a polygon that overlaps an existing polygon in that city | "This zone overlaps with existing zone 'X'. Zones within the same city cannot overlap." |
| 8 | Draw a circle that overlaps an existing circle in that city | Same message, with the other zone's name. |
| 9 | Draw a polygon that overlaps an existing circle (or vice versa) | Same — cross-shape overlap must be detected in both directions. |
| 10 | Draw a polygon that **touches but does not overlap** the edge of an existing zone | Should be allowed (edges may touch; interiors must not intersect). |
| 11 | Draw a zone in City A whose shape geographically overlaps a zone in City B | Should be **allowed** — overlap rule is only within the same city. |
| 12 | Submit with special characters / very long zone name | Name should be accepted but sanitised; no XSS in the list view. |

---

## 6. Customer app — address flows to test

> If the app isn't ready, hit these endpoints directly:
> - `POST /app/v1/api/is_city_deliverable`
> - `POST /app/v1/api/add_address`
> - `POST /app/v1/api/update_address`

### 6.1 "Is this area deliverable?" check

**When it's called:** App uses this before showing the address form, and to pick the nearest branch.

**Input:** `latitude`, `longitude`.

**Scenarios to test:**

| # | Pinpoint lies… | Expected result |
|---|----------------|-----------------|
| 1 | Inside an **active polygon zone** | Success: returns the zone's city, zone name, and the nearest branch. Branch open/closed flag included. |
| 2 | Inside an **active circle zone** | Same as above. |
| 3 | Inside an **inactive** zone | Treated as "no match" → error *"Sorry! We do not deliver food at the selected location!"* |
| 4 | Outside every zone | Same error. |
| 5 | Exactly on a polygon edge | Either accepted or rejected is acceptable (document which happens). |
| 6 | Latitude/longitude missing or non-numeric | Validation error, no server crash. |
| 7 | City has zones configured, pin is outside them | Error (do **not** fall back to city name in this endpoint). |

### 6.2 Adding a new delivery address (customer)

**Inputs required:** name, mobile, address, area, city (typed), latitude, longitude. Others optional.

**Scenarios:**

| # | Situation | Expected result |
|---|-----------|-----------------|
| 1 | Coordinates fall inside an active polygon zone | Address saved. Response "Address updated Successfully". |
| 2 | Coordinates fall inside an active **circle** zone | ⚠ **Known gap** — currently the add_address flow only checks polygon zones. The circle zone is **not** matched. The address falls through to the city-name fallback. Report if this changes. |
| 3 | Coordinates outside every zone, **but** typed city name matches a city in the system | Address saved using that city (fallback). Message: "Address updated Successfully". |
| 4 | Coordinates outside every zone, typed city name does NOT match any city | Rejected: *"Sorry! We do not deliver food at the selected location!"* |
| 5 | Missing latitude or longitude | Validation error. |
| 6 | Logged-out / invalid token | Endpoint should reject before doing any zone work. |

### 6.3 Updating an existing delivery address (customer)

> This is the main code path edited **today**. Test extra carefully.

**Inputs:** `id` (required). All other fields optional.

| # | Situation | Expected result |
|---|-----------|-----------------|
| 1 | Updating **only non-location fields** (e.g. just the name or mobile), no lat/lng in request | Address must be saved. The system should re-use the **stored** lat/lng to re-check the zone. Previously this would fail — verify it now passes. |
| 2 | Sending new lat/lng that falls inside an active polygon zone | Saved, city_id updated to the zone's city. |
| 3 | Sending new lat/lng that falls inside an active **circle** zone | Saved (circle IS supported in update_address — this is one of today's additions). |
| 4 | Sending lat/lng outside every zone, but `city` field matches a system city | Saved via city-name fallback (this is also a today's addition — previously this would have failed). |
| 5 | Sending lat/lng outside every zone, and `city` not recognised | Rejected: *"Sorry! We do not deliver food at the selected location!"* |
| 6 | Invalid / non-existent `id` | "Address Not Found!" |
| 7 | Setting this address as default (`is_default: 1`) | Default flag applied; any previously-default address for this user is un-defaulted. |

### 6.4 Placing an order (end-to-end deliverability)

After an address is saved, when the customer attempts to place an order:

| # | Situation | Expected result |
|---|-----------|-----------------|
| 1 | Saved address pinpoint inside an active **polygon** zone, branch within city's max deliverable distance | Order is deliverable, proceeds normally. |
| 2 | Saved address pinpoint inside an active **circle** zone | ⚠ **Known gap** — order placement currently only matches polygon zones. May incorrectly say "not deliverable" for circle-only zones. Please log if you hit this. |
| 3 | Saved address inside zone, but branch is further than the city's max deliverable distance | Order refused as "not deliverable". |
| 4 | Address saved via city-name fallback (no zone match) | Document the behaviour you observe — depending on branch distance this may still be allowed or refused. |
| 5 | All zones for that city are inactive/deleted after address was saved | Order should be refused. |

---

## 7. Known gaps / things we expect to fail

The commit message says *"partially done"* — please log these, but they are already known:

1. **Circle zones are not honoured in every flow.** Specifically:
   - `add_address` — only matches polygons. A circle-inside pin will fall through to city-name fallback.
   - Order placement (`is_order_deliverable`) — same: circle zones are ignored.
   - `is_city_deliverable` and `update_address` — these **do** handle circles correctly.
   - If a test fails only in a circle-related scenario, note it but don't treat as blocker unless product confirms.
2. **Edit existing zone shape** — there is no admin UI to *re-draw* a zone after it's created. You can only delete and recreate. If a tester finds an Edit button that doesn't work, report it.
3. **Zone info isn't saved on the address record.** Even when an address matches a zone, only the zone's *city* is persisted. The zone identity itself isn't stored. This is intentional for now.
4. **Overlap check performance** — with many zones in a single city, the create-zone save may feel slow. Note response times if > 3 seconds.
5. **Purchase-code gate is currently disabled** in the admin (unrelated to zones but may be visible during your testing — don't log it as a bug, it's intentional for this build).

---

## 8. Regression tests — things that shouldn't have broken

Run these quickly to confirm nothing else slipped:

- [ ] Creating / editing / deleting a **city** (not zone) still works through the same admin page.
- [ ] City list filters, search, pagination still work.
- [ ] A customer who saves an address **in a city that has no zones at all** — behaviour should fall back to the old city-name matching, not error out.
- [ ] Existing customer addresses (created before zones existed) still load and are still usable for ordering.
- [ ] Delivery charge calculation (fixed / per_km / range) still applies normally once an order is deliverable.
- [ ] Nearest-branch selection still works when multiple branches exist in the city.
- [ ] Admin login, sidebar, other menus render normally — no console errors from the Google Maps script.

---

## 9. Bug-report template (please use for every finding)

```
Title:
Area: Admin ▸ Zones   |   Customer ▸ Add Address   |   Customer ▸ Update Address   |   Order Placement
Build / commit: <date + short sha>
Severity: Blocker | Major | Minor | Cosmetic

Steps to reproduce:
1.
2.
3.

Pinpoint used (if applicable): lat=  , lng=
City / Zone context:

Expected:
Actual:

Screenshot / API response: <attach>
Is this already listed in §7 Known Gaps? Yes / No
```

---

## 10. Quick sanity checklist (run at the end of each test session)

- [ ] Creating a polygon zone works
- [ ] Creating a circle zone works
- [ ] Duplicate zone name in same city is blocked
- [ ] Duplicate zone name in different city is allowed
- [ ] Overlapping zones in same city are blocked (polygon-polygon, circle-circle, polygon-circle both directions)
- [ ] Deleting a zone removes it from list and map
- [ ] Toggling status to inactive stops that zone from matching addresses
- [ ] Adding an address inside an active polygon zone succeeds
- [ ] Adding an address outside all zones but with a known city name succeeds (fallback)
- [ ] Adding an address outside all zones with unknown city fails with the correct message
- [ ] Updating only non-location fields of an existing address succeeds (today's fix)
- [ ] Updating an address into a circle zone succeeds (today's fix)
- [ ] Placing an order on a zone-valid address succeeds
- [ ] Placing an order on an address outside max-deliverable distance fails
- [ ] No console errors on the admin map page
