---
name: frontend-design
description: "Guidance for distinctive, intentional visual design when building new UI or reshaping an existing one. Use when styling web pages, dashboards, landing pages, or components to prevent generic AI-generated aesthetics ('AI slop') through bold typography, harmonious color palettes, intentional spacing, and purposeful visual hierarchy."
license: Apache-2.0
metadata:
  author: anthropic
  source: "https://github.com/anthropics/skills"
---

# Frontend Design

Approach this as the design lead at a premier design studio known for giving every product a distinct visual identity that is never mistaken for anyone else's. Avoid cliché, templated designs or generic "AI defaults." Make deliberate, opinionated choices about palette, typography, layout, and visual rhythm that are tailored to the specific brief.

---

## 1. Ground Your Designs in the Subject Matter

- **Context First:** If the brief does not specify the product or subject matter, identify it before designing. Consider the audience, their mental model, and the primary job of the interface. A financial analytics workstation demands dense data clarity; an agency workflow demands high-velocity calm; a luxury brand demands spacious elegance.
- **Real Content Over Placeholders:** Always design with realistic domain content rather than generic lorem ipsum. The vernacular, abbreviations, and real data formats of the industry dictate the right layout and density.

---

## 2. Typographic Craft

Typography carries the entire personality of an interface:
- **Family Selection:** Use one strong family (or two clearly distinct families, e.g., an editorial display serif with a clean sans-serif body). Never mix multiple similar sans-serifs.
- **Typographic Scale:** Follow a disciplined type scale (e.g., 11px, 12.5px, 14px, 16px, 20px, 26px, 32px) with intentional line-heights and letter-spacing.
- **Line Lengths:** Cap reading prose at 65–80 characters maximum (`max-w-prose` or `max-width: 65ch`). Give serif body text slightly more line-height than sans-serif.
- **Tabular Numbers:** Always apply `font-variant-numeric: tabular-nums` or `font-mono` on counters, monetary values, timestamps, and column data to prevent layout jitter.
- **Avoid Typographic Tells of AI Slop:**
  - ❌ Do NOT highlight a single random word in a headline with a bright italic or different color.
  - ❌ Do NOT make every label and badge UPPERCASE with wide letter-spacing (`tracking-widest`).
  - ❌ Do NOT put unnecessary decorative eyebrow labels above every single heading.

---

## 3. Color Harmony & Palette Discipline

- **The 60-30-10 Rule:**
  - **60% Dominant Background/Surface:** Clean, cohesive canvas (light mode soft neutral or dark mode deep slate/zinc).
  - **30% Structural Secondary:** Card borders, subtle fills, table headers, sidebars, muted text.
  - **10% Intentional Accent:** A single focused brand accent color reserved for primary calls-to-action, key statuses, and active highlights.
- **Avoid Generic Color Clichés:**
  - ❌ Avoid the overused "AI purple-to-pink" gradient washes.
  - ❌ Avoid pure raw saturated primaries (`#FF0000`, `#0000FF`, `#00FF00`). Use tailored HSL/OKLCH tones with controlled saturation and depth.
  - ❌ Avoid flat pure black (`#000000`) on harsh white (`#FFFFFF`). Use refined neutral scales (`#0F172A`, `#1E293B`, `#F8FAFC`).

---

## 4. Visual Structure as Information

- **Functional Containers:** Visual devices like borders, dividers, badges, and counters should encode real status or hierarchy, not act as decorative noise.
- **Card Hierarchy:** Don't chop every piece of content into identical rounded cards with the same box-shadow. Vary density, grouping, and elevation to reflect importance.
- **Sequential Lists:** Only use numbered markers (`01`, `02`, `03`) if the items represent a strict chronological or sequential process. Otherwise, use meaningful visual tokens or clean row structures.

---

## 5. Deliberate Motion & Interactions

- **One Orchestrated Moment:** A single intentional page-load entrance or reveal lands better than scattered hover animations on every element.
- **Action-Responsive:** Motion should answer a person's action (expanding a drawer, toggling a filter, completing a modal) rather than looping continuously without purpose.
- **Respect Preferences:** Always honor `@media (prefers-reduced-motion: reduce)` by providing instant transitions.

---

## 6. UX Writing & Microcopy

- **Words Are Design:** Microcopy is functional guidance, not marketing filler.
- **Active Verbs:** CTAs must describe the exact outcome: `"Mark Published"`, `"Approve Task"`, `"Download Brief"`, not vague words like `"Submit"` or `"Go"`.
- **Cohesive Vocabulary:** Keep action names consistent throughout the entire flow (the button that says `"Approve"` produces a notification that says `"Task Approved"`).
- **Constructive Empty States:** Treat empty states as invitations to act with a clear button and friendly explanation, not an apologetic dead end.

---

## 7. The Two-Pass Workflow

1. **Pass 1 — Define the Token System:**
   - Establish base palette (surface, border, text primary/secondary, single accent).
   - Establish typographic roles and line scale.
   - Outline layout concept with spatial hierarchy.
2. **Pass 2 — Critique & Build:**
   - Review against the brief: Does any part look like a generic template?
   - Refine until the interface feels distinctly crafted for its real domain.
