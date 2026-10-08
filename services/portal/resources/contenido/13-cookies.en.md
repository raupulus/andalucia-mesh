# 13 · Cookies Policy

> `/legal/cookies` · Explaining why public pages use zero cookies, what is stored in client local storage, and technical cookies used in the private administration panel.

## SEO

- **Título:** `Cookies Policy · {PROJECT_NAME}`
- **Descripción:** `Public pages of {PROJECT_NAME} use zero cookies. Only the private operator panel uses technical session cookies.`

## Borrador del texto

**H1:** Cookies Policy

Last updated: October 8, 2026

The public web pages of this portal use **zero cookies and zero trackers**. That is why you will never encounter an annoying cookie consent banner.

### What is stored in your browser

Only your visual theme and language preferences, if you explicitly change them using the top navigation bar controls:
- **Theme preference:** Stored in your browser's local storage (`localStorage` key `snm_theme`). It is not a cookie, is never transmitted to our server, and cannot identify you. If untouched, the website automatically adopts your operating system's color scheme.
- **Language selection:** Stored locally in `localStorage` key `portal_locale` so your preferred language persists across pages without emitting cookies (`RN-06` / `RN-48`).

### Administration Panel

The `/admin` area is private and reserved exclusively for authorized project maintainers. Only when logging into or navigating within this private area are technical session cookies utilized:

| Cookie | Purpose | Duration |
|---|---|---|
| `andalucia_mesh_session` | Maintains the operator's authenticated technical session | 120 minutes of inactivity or browser close |
| `XSRF-TOKEN` | Protection against Cross-Site Request Forgery (anti-CSRF) | Active session (max 120 minutes) |

*For public visitors browsing community pages, these cookies are never issued or stored.*

### PotatoMesh and MeshView

These services run open-source third-party software on dedicated subdomains. Neither service installs advertising or commercial tracking cookies. PotatoMesh uses client storage (`IndexedDB` and `localStorage`) to cache node packets locally for faster rendering on mobile devices.

### Web Analytics

We do not use Google Analytics, Meta Pixel, or any third-party behavioral tracking services.

### Clearing Local Data

You can wipe local storage and cookies at any time through your browser settings (under Privacy & Security or Site Data).
