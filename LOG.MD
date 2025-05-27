## Phase 1: Core Praise Functionality (1.5–2 hrs)

### 🔹 1. Praise Creation Flow (1 hr)
- [ ] Create `/praises/submit` route (GET + POST)
- [ ] Build praise form (fields: title, content, optional prayer ID)
- [ ] Handle submission and insert into database
- [ ] Redirect to either the Praise tab or the relevant prayer section
- [ ] Add CSRF protection (if used in your stack)

### 🔹 2. Praise Display Logic (30–45 min)
- [ ] Load praises in controller
- [ ] Display praises in `/praises` page or within each prayer (if inline)
- [ ] (Optional) Add praise count badge in the Praise tab

---

## Phase 2: Frontend Tabs + Inline Toggle (1.5 hrs)

### 🔹 3. Prayer/Praise Tab UI (1 hr)
- [ ] Add top navigation tabs: `Prayers | Praises`
- [ ] Use CSS to highlight active tab
- [ ] Switch content based on route: `/prayers` vs `/praises`

### 🔹 4. “Show Inline Praises” Toggle (30 min)
- [ ] Add toggle switch to Prayer tab (default: ON)
- [ ] Show associated praises beneath prayer cards
- [ ] Persist toggle state (via localStorage or query param)

---

## Phase 3: Stability + UX Polish (2–2.5 hrs)

### 🔹 5. Fix DI Errors / Crashes (30–60 min)
- [ ] Resolve missing dependencies (`$view`, `$db`) in `SettingsController`
- [ ] Scan app for other DI-related or route-level crashes

### 🔹 6. Pagination + Sorting for Praises (30 min)
- [ ] Reuse existing pagination controls
- [ ] Add `?page=` and `?limit=` query param handling for praises

### 🔹 7. Polish + Seed Data (1 hr)
- [ ] Insert seed prayers and praises
- [ ] Format dates and tidy card layout
- [ ] Smoke test: submit, display, navigate, unapprove, toggle views

---

## 🚀 Bonus (Optional if Time Allows)
- [ ] Link from each prayer to its praises (anchor or modal)
- [ ] Flash messages for praise submission success/failure
- [ ] Improve mobile layout and responsiveness


## Phase 4: 🌟 Nice to Haves

## 🛡️ Moderator Controls
- [ ] Allow moderators to:
  - [ ] Search prayers and praises
  - [ ] Sort by newest / oldest / length
- [ ] User management (excluding other moderators/admins):
  - [ ] Change user name
  - [ ] Change user email
  - [ ] Send password reset request
  - [ ] Shadow ban (quiet mute):
    - [ ] Block prayer submissions
    - [ ] Block praise submissions
  - [ ] Delete user accounts

---

## 🙏 Praise Requests
- [ ] Enable users to request praise on answered prayers
- [ ] (Optional) Convert a prayer to praise from the prayer card
- [ ] Moderator approval flow for praise requests

---

## 📱 Facebook Integration
- [ ] Facebook login integration (OAuth)
- [ ] “Share to Facebook” button on praise or prayer cards

---

## 🧩 Embed View
- [ ] Create embeddable view of prayers (iframe-safe)
- [ ] Allow filtered view by tags or user

---

## 👤 User Settings
- [ ] Allow users to update:
  - [ ] Display name
  - [ ] Email address

---

## 🧠 Admin Dashboard
- [ ] Overview of:
  - [ ] Total users
  - [ ] Pending prayer/praise approvals
  - [ ] Shadow-banned users
  - [ ] Feedback or abuse reports

---

## 🔐 Reset Password Requests
- [ ] User-initiated reset request
- [ ] Admin/moderator-triggered reset option

---

## 📄 Pagination
- [ ] Paginate all long lists:
  - [ ] Prayer requests
  - [ ] Praise posts
  - [ ] User accounts in admin panel
