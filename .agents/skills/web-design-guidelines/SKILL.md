---
name: web-design-guidelines
description: "Vercel Web Interface Guidelines compliance skill. Use when reviewing UI code, building interactive components, improving accessibility (a11y), designing forms, optimizing typography, or preventing layout shift (CLS)."
license: MIT
metadata:
  author: vercel-labs
  source: "https://github.com/vercel-labs/agent-skills"
---

# Web Interface Guidelines

Build and audit web interfaces against Vercel's production engineering and accessibility standards. Prioritize high clarity, zero layout shifts, resilient forms, and keyboard accessibility.

---

## 1. Accessibility (a11y) & Semantics

- **Icon Buttons:** Any button containing only an icon MUST have an `aria-label` or `title` describing its action (e.g., `<button aria-label="Close modal">`).
- **Semantic Tags:** Use native elements (`<button>`, `<a>`, `<label>`, `<table>`) before reaching for ARIA role overrides. Never use `<div onclick>` for clickable elements.
- **Form Controls:** Every input, select, and textarea needs an associated `<label>` (with `for`/`id`) or `aria-label`.
- **Keyboard Navigation:** All interactive elements must be focusable and operable via `Enter` and `Space`. Dialogs must trap focus and close on `Escape`.
- **Images:** Provide meaningful `alt` text for descriptive imagery, or `alt=""` with `aria-hidden="true"` for purely decorative artwork.
- **Dynamic Updates:** Announcements, live counters, or toast messages need `aria-live="polite"`.

---

## 2. Visible Focus States

- **Never Disable Outlines Without Replacement:** Avoid bare `outline: none` or `outline-none`. Always provide a clean focus ring:
  ```css
  :focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
  }
  ```
- **Use `:focus-visible` over `:focus`:** Prevents unwanted focus rings from showing up on mouse clicks while preserving keyboard indicator.
- **Group Focus:** Use `:focus-within` on compound search bars and input groups so the entire container highlights together.

---

## 3. Form Ergonomics

- **Meaningful Names & Autocomplete:** Use standard `autocomplete` tokens (`name`, `email`, `current-password`, `tel`, `off`).
- **Correct Input Types:** Set appropriate `type="email"`, `type="tel"`, `type="number"`, or `inputmode="numeric"`.
- **Never Block Paste:** Never intercept `onPaste` with `preventDefault()` on fields (allow password managers and code pastes).
- **Label Hit Targets:** Clicking the label must activate its input control.
- **Buttons in Flight:** Keep submit buttons enabled until the request begins, then show a spinner and disable repeat clicks.
- **Inline Errors:** Place validation error text directly next to the affected input, and move focus to the first error on failed submit.
- **Ellipses on Loading:** Use single Unicode ellipsis `…` instead of three periods (`"Saving…"`).

---

## 4. Typography & Layout Resilience

- **Headings & Widows:** Use `text-wrap: balance` or `text-wrap: pretty` on headlines to eliminate awkward single-word lines (orphans/widows).
- **Numeric Alignment:** Always use `font-variant-numeric: tabular-nums` for timestamps, countdowns, financial tables, and metric comparisons.
- **Content Truncation:**
  - Flex items with truncating text need `min-width: 0` (`min-w-0`) to prevent flex containers from expanding beyond their parent.
  - Apply `text-overflow: ellipsis`, `overflow: hidden`, `white-space: nowrap` or `-webkit-line-clamp`.
- **Empty States:** Always handle empty collections or missing props gracefully with intentional empty-state UI rather than blank voids or broken layouts.

---

## 5. Animation Hygiene & Stability

- **Prefers Reduced Motion:** Always provide an instant fallback:
  ```css
  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
      animation-duration: 0.01ms !important;
      transition-duration: 0.01ms !important;
    }
  }
  ```
- **Compositor-Friendly:** Animate only `transform` and `opacity`. Never animate `width`, `height`, `margin`, `padding`, or `top`/`left` (avoids browser repaints and jank).
- **Never `transition: all`:** Explicitly declare the animated CSS properties (`transition: transform 160ms ease, opacity 160ms ease`).

---

## 6. Image Stability & Performance

- **Zero Layout Shifts (CLS):** Always set explicit `width` and `height` (or CSS `aspect-ratio`) on images and videos so the browser reserves space before media loads.
- **Lazy Loading:** Apply `loading="lazy"` on below-the-fold media.
- **Critical Images:** For above-the-fold heroes, use `fetchpriority="high"`.
