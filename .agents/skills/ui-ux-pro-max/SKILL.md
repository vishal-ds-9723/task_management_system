---
name: ui-ux-pro-max
description: "Comprehensive UI/UX design intelligence skill. Provides systemized guidance for selecting UI styles (Glassmorphism, Minimalist, Bento Grid, Modern SaaS, Dark Mode), color harmonies, 8pt spacing grids, typography pairings, and layout composition patterns for dashboards, applications, and landing pages."
license: MIT
metadata:
  author: nextlevelbuilder
  source: "https://github.com/nextlevelbuilder/ui-ux-pro-max-skill"
---

# UI/UX Pro Max Design Intelligence

A design intelligence framework providing reasoning rules, style catalogs, component patterns, and quality checklists for building production-grade web interfaces.

---

## 1. Core Visual Archetypes & Styles

Choose a visual archetype that authentically matches the product context:

| Style | Key Visual Characteristics | Best Used For |
| :--- | :--- | :--- |
| **Clean Modern SaaS** | Subtle borders (`1px solid var(--border)`), soft cards, 12–14px radius, crisp typography, generous whitespace | Agency portals, productivity tools, task management systems |
| **Bento Grid** | Modular grid cards with varied spans (e.g. 2x1, 1x1, 2x2), high visual density, self-contained interactive micro-widgets | Dashboards, feature overviews, metric hubs, command centers |
| **Glassmorphism / Frosted** | `backdrop-filter: blur(12px)`, translucent background (`rgba(255,255,255,0.7)` or `rgba(15,23,42,0.7)`), 1px gradient borders | Floating navigation, modal backdrops, elevated command bars |
| **Deep Slate Dark Mode** | Slate/zinc surfaces (`#0F172A`, `#1E293B`), never pure `#000000` background; crisp neon or emerald accents; 1px inner glow borders | Developer tools, creative suites, media monitors, analytics |
| **Editorial Minimalist** | High-contrast serif headlines + crisp sans-serif body, generous padding, hairline separators, zero box-shadows | Knowledge bases, documentation, publishing platforms |

---

## 2. Spatial System & The 8-Point Grid

All layout dimensions, paddings, and margins must align with the 8-point grid (with a 4px sub-step for compact components):

- **4px (`--spacing-1`):** Micro-gaps between icons and text, pill badge padding.
- **8px (`--spacing-2`):** Tight padding, button icon gaps, status dot offsets.
- **12px (`--spacing-3`):** Compact table cell padding, form input vertical padding.
- **16px (`--spacing-4`):** Standard card padding, list gap, table cell horizontal padding.
- **20px (`--spacing-5`):** Standard card header padding, medium panel gaps.
- **24px (`--spacing-6`):** Section margins, modal internal padding, grid column gaps.
- **32px (`--spacing-8`):** Major page header offsets, dashboard widget spacing.
- **48px+ (`--spacing-12+`):** Major layout section dividers.

---

## 3. High-Conversion Component Anatomy

### A. Metric & Stat Cards
- Single primary number in bold tabular font (`font-size: 24–28px`, `font-weight: 800`).
- Compact icon badge (`width: 40px`, `height: 40px`, soft tinted background).
- Contextual trend indicator (e.g., green `+12%` or amber `Due soon`).
- Subtle hover elevation (`transform: translateY(-2px)`, refined box-shadow).

### B. Interactive Tables & Lists
- Sticky headers with contrasting background (`background: var(--card2)`).
- Visual thumbnail preview with hover zoom or quick lightbox.
- Status badges with distinct pill colors (e.g. teal for published, amber for pending, crimson for overdue).
- Right-aligned quick action buttons with clear icon + text.

### C. Command Drawers & Modals
- Backdrop blur overlay (`backdrop-filter: blur(4px); background: rgba(15, 23, 42, 0.4)`).
- Clean top header with prominent close button (`aria-label="Close"`).
- Fixed bottom action bar with primary action on the right and secondary dismiss on the left.

---

## 4. Curated Typography Pairings

1. **Modern SaaS & Dashboard:**
   - Display/Headings: `Plus Jakarta Sans` or `Inter` (Font weights: 700 / 800)
   - Body & Inputs: `Inter` or `System-UI` (Font weights: 400 / 500 / 600)
   - Counters & Data: `JetBrains Mono` or `tabular-nums`
2. **Creative Agency & Portfolio:**
   - Display/Headings: `Outfit` or `Cabinet Grotesk`
   - Body & Copy: `DM Sans` or `Inter`
3. **Editorial & High Craft:**
   - Display/Headings: `Playfair Display` or `Instrument Serif`
   - Body & Copy: `Newsreader` or `Inter`

---

## 5. Pre-Delivery Quality Checklist

Before finalizing any UI implementation, verify:
- [ ] **No Emojis as System Icons:** Use FontAwesome, Lucide, or Heroicons SVG.
- [ ] **Cursor Indicators:** All clickable cards, rows, and buttons have `cursor: pointer`.
- [ ] **Contrast Verification:** Text contrast meets 4.5:1 minimum on all surfaces.
- [ ] **Responsive Breakpoints:** Tested across 375px (mobile), 768px (tablet), 1024px (laptop), and 1440px (desktop).
- [ ] **Reflow & Overflow:** Long text titles truncate cleanly without breaking container boundaries (`truncate` + `min-w-0`).
- [ ] **Empty States:** Gracefully handles 0 items with an actionable empty-state illustration or prompt.
