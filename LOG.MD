## Phase 1: Core Praise Functionality (1.5–2 hrs)

### 🔹 1. Praise Creation Flow (1 hr)
- [x] Create `/praises/submit` route (GET + POST)
- [x] Build praise form (fields: title, content, optional prayer ID)
- [x] Handle submission and insert into database
- [x] Redirect to either the Praise tab or the relevant prayer section

### 🔹 2. Praise Display Logic (30–45 min)
- [x] Load praises in controller
- [x] Display praises in `/praises` page 
  - [x] or within each prayer (if inline)

---

## Phase 2: Frontend Tabs + Inline Toggle (1.5 hrs)

### 🔹 Praise Reporting (1 hr) 
- [x] Add "Cancel" or "Back" to report forms and prayer request form
- [x] Get prayers for current user (or all if admin / mod)
- [x] Add parent prayer.
- [x] Submit test
- [x] Test approve and deny

### 🔹 3. Prayer/Praise Tab UI (1 hr)
- [x] Add top navigation tabs: `Prayers | Praises`
- [x] Use CSS to highlight active tab
- [x] Switch content based on route: `/prayers` vs `/praises`

### 🔹 4. “Show Inline Praises” Toggle (30 min)
- [x] Add toggle switch to Prayer tab (default: ON)
- [x] Show associated praises beneath prayer cards
- [x] Persist toggle state (via localStorage or query param)

---

## Phase 3: Stability + UX Polish (2–2.5 hrs)

### 🔹 7. Polish + Seed Data (1 hr)
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
  - [x] Prayer requests
    - [ ] Moderator View
  - [x] Praise posts
    - [ ] Moderator View
  - [ ] User accounts in admin panel
