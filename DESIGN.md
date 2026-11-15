---
name: Idraak Lab
description: Arabic-first AI model benchmarking, live telemetry, and research platform.
colors:
  cyan-signal: "#4cd7f6"
  on-cyan-signal: "#003640"
  cyan-container: "#06b6d4"
  on-cyan-container: "#00424f"
  emerald-telemetry: "#4edea3"
  on-emerald-telemetry: "#003824"
  emerald-container: "#00a572"
  on-emerald-container: "#00311f"
  indigo-accent: "#c0c1ff"
  on-indigo-accent: "#1000a9"
  coral-error: "#ffb4ab"
  on-coral-error: "#690005"
  midnight-slate: "#0b1326"
  abyssal-base: "#06090e"
  deep-surface: "#0b111a"
  panel-slate: "#111827"
  elevated-slate: "#1a2234"
  border-subtle: "#1e293b"
  border-highlight: "#334155"
  text-primary: "#f8fafc"
  text-secondary: "#94a3b8"
  text-tertiary: "#64748b"
  outline-muted: "#869397"
  outline-variant: "#3d494c"
  metric-speed: "#06b6d4"
  metric-efficiency: "#10b981"
  metric-cost: "#f59e0b"
  metric-latency: "#ec4899"
typography:
  display:
    fontFamily: "IBM Plex Sans Arabic, Instrument Sans, sans-serif"
    fontSize: "2.5rem"
    fontWeight: 700
    lineHeight: 1.2
    letterSpacing: "0em"
  headline:
    fontFamily: "IBM Plex Sans Arabic, Instrument Sans, sans-serif"
    fontSize: "1.75rem"
    fontWeight: 600
    lineHeight: 1.286
  title:
    fontFamily: "IBM Plex Sans Arabic, Instrument Sans, sans-serif"
    fontSize: "1.25rem"
    fontWeight: 600
    lineHeight: 1.4
  body:
    fontFamily: "IBM Plex Sans Arabic, Instrument Sans, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.571
  label:
    fontFamily: "JetBrains Mono, IBM Plex Sans Arabic, ui-monospace, monospace"
    fontSize: "0.75rem"
    fontWeight: 600
    lineHeight: 1.333
    letterSpacing: "0em"
rounded:
  sm: "4px"
  md: "8px"
  lg: "12px"
  full: "9999px"
spacing:
  xs: "0.25rem"
  sm: "0.5rem"
  md: "1rem"
  lg: "1.5rem"
  xl: "2.5rem"
components:
  button-primary:
    backgroundColor: "{colors.cyan-container}"
    textColor: "{colors.on-cyan-container}"
    rounded: "{rounded.md}"
    padding: "8px 16px"
  button-secondary:
    backgroundColor: "{colors.panel-slate}"
    textColor: "{colors.text-secondary}"
    rounded: "{rounded.md}"
    padding: "8px 16px"
  card-panel:
    backgroundColor: "{colors.panel-slate}"
    textColor: "{colors.text-primary}"
    rounded: "{rounded.lg}"
    padding: "16px"
  input-search:
    backgroundColor: "{colors.panel-slate}"
    textColor: "{colors.text-primary}"
    rounded: "{rounded.md}"
    padding: "8px 16px"
  badge-live:
    backgroundColor: "{colors.midnight-slate}"
    textColor: "{colors.cyan-signal}"
    rounded: "{rounded.full}"
    padding: "4px 16px"
---

# Design System: Idraak Lab

## Overview

**Creative North Star: "The Telemetry Observatory"**

Idraak Lab is built like a high-precision Arabic engineering instrument: deep midnight-navy surfaces, crisp cyan and emerald telemetry signals, and tabular numbers that let AI developers scan latency, tokenization efficiency, and cost without visual noise. Every surface is designed RTL-first (`dir="rtl"`, `lang="ar"`) in dark mode by default, with a calibrated opt-in light mode that preserves contrast and surface separation.

The visual language is crisp, technical, and restrained. It rejects generic SaaS purple-pink gradients, decorative card-in-card clutter, and low-contrast gray-on-colored text that slows down benchmark comparison.

**Key Characteristics:**
- Dark-first Midnight Slate canvas (`#0b1326` / `#06090e`) paired with a tinted Ice Slate light mode (`#e8eef7`) so cards always separate from the page.
- High-legibility Arabic sans-serif (`IBM Plex Sans Arabic`) paired with `JetBrains Mono` for benchmark metrics, prices, and telemetry strips.
- Tonal surface layering and 1px hairline borders (`#1e293b`) instead of heavy drop shadows.
- Distinct per-metric color coding so speed, Arabic efficiency, cost, and latency scan instantaneously across tables.

## Colors

The palette pairs cool Midnight Slate neutrals with a high-visibility Cyan Signal primary and Emerald Telemetry secondary, reserving distinct saturated hues strictly for benchmark metrics.

### Primary
- **Cyan Signal** (`#4cd7f6`, light mode `#0e7490`): Active navigation indicators, focus borders, primary links, and interactive highlights.
- **Cyan Container** (`#06b6d4`, light mode `#0e7490`): Primary CTA button fills (paired with `#00424f` text in dark mode, `#ffffff` in light mode).

### Secondary
- **Emerald Telemetry** (`#4edea3`, light mode `#047857`): Live status indicators, operational badges, and positive Arabic token-efficiency deltas.

### Tertiary
- **Indigo Accent** (`#c0c1ff`, light mode `#4f46e5`): Secondary analytical highlights and research category accents.

### Neutral
- **Midnight Slate** (`#0b1326`, light mode `#e8eef7`): Primary page canvas.
- **Abyssal Base** (`#06090e`, light mode `#dde6f1`): Top telemetry ticker, substrip, and deepest recessed backgrounds.
- **Deep Surface** (`#0b111a`, light mode `#ffffff`): Table containers, header backdrop, and calculator wrappers.
- **Panel Slate** (`#111827`, light mode `#ffffff`): Cards, inputs, filter pills, and secondary action buttons.
- **Elevated Slate** (`#1a2234`, light mode `#eef3f9`): Keyboard shortcut badges (`⌘K`), hover states, and nested chips.
- **Border Subtle & Highlight** (`#1e293b` / `#334155`, light mode `#cbd5e1` / `#94a3b8`): 1px structural dividers and interactive hover borders.
- **Text Primary / Secondary / Tertiary** (`#f8fafc` / `#94a3b8` / `#64748b`, light mode `#0b1220` / `#1e293b` / `#334155`): Strict three-tier contrast hierarchy.

### Named Rules
**The Metric Hue Rule.** Speed (`#06b6d4`), Arabic Efficiency (`#10b981`), Cost (`#f59e0b`), and Latency (`#ec4899`) each own one dedicated hue. Never reuse metric hues for decorative gradients.

**The Legible Canvas Rule.** Never place muted gray text on saturated or dark-tinted containers without verifying contrast; light mode darkens all accents to WCAG-safe shades.

## Typography

**Display Font:** `IBM Plex Sans Arabic`, with `Instrument Sans, sans-serif` fallback
**Body Font:** `IBM Plex Sans Arabic`, with `Instrument Sans, sans-serif` fallback
**Label/Mono Font:** `JetBrains Mono`, with `IBM Plex Sans Arabic, ui-monospace, monospace` fallback

**Character:** Clean geometric Arabic letterforms optimized for RTL UI density, paired with tabular monospace numerals for millisecond latencies, token ratios, and dollar pricing.

### Hierarchy
- **Display** (`700`, `40px` desktop / `28px` mobile, `48px` / `36px` line-height): Hero statements and primary page titles.
- **Headline** (`600`, `28px` lg / `20px` md / `16px` sm): Section headers, card group titles, and brand wordmark.
- **Body** (`400`, `16px` lg / `14px` md / `13px` sm): Explanations, research excerpts, table text, and form controls.
- **Metric Display & Cell** (`700` at `22px` / `600` at `14px`, `JetBrains Mono`): KPI callouts and leaderboard table cells.
- **Label / Code** (`600`, `12px`, `16px` line-height, `JetBrains Mono`): Telemetry ticker items, `⌘K` hints, and technical metadata tags.

### Named Rules
**The Monospace Telemetry Rule.** All numerical benchmark scores, latencies, token counts, and API prices render in `JetBrains Mono` so columns align cleanly across rows.

## Layout

Full-width fluid containers with `1rem` (`16px`) mobile gutters and `1.5rem` (`24px`) desktop gutters (`2rem` / `32px` desktop page margin). Public pages anchor beneath a fixed two-tier top chrome: a `40px` (`2.5rem`) live telemetry ticker and a `72px` (`4.5rem`) main navigation header (with an optional secondary telemetry strip on `lg+`). Content clears the fixed header via top padding (`calc(2.5rem + 4.5rem)` on mobile, adding `1.5rem` on `lg+`). Spacing follows a five-step scale: `xs` (`0.25rem`), `sm` (`0.5rem`), `md` (`1rem`), `lg` (`1.5rem`), and `xl` (`2.5rem`).

## Elevation & Depth

Depth is achieved primarily through **tonal surface layering** (stepping from `Abyssal Base` → `Midnight Slate` → `Deep Surface` → `Panel Slate` → `Elevated Slate`) reinforced by `1px` hairline borders (`#1e293b`). Glassmorphism (`.glass-card`, `.glass-header`, `.glass-sidebar`) uses `16px–24px` backdrop blur with translucent slate fills (`rgba(15, 23, 42, 0.75)`). Shadows are omitted at rest in dark mode and appear only as subtle ambient separation (`0 4px 6px -1px rgba(0, 0, 0, 0.03)`) in light mode.

### Named Rules
**The Flat-By-Default Rule.** Surfaces are flat at rest in dark mode, separated by tonal contrast and 1px hairline borders rather than drop shadows.

## Shapes

Controlled, technical geometry: `8px` (`rounded-lg`) for buttons, inputs, and action controls; `12px` (`rounded-xl`) for cards, leaderboard tables, and the brand mark; `4px` (`rounded`) for inline keyboard badges and scrollbar thumbs; and `9999px` (`rounded-full`) reserved strictly for status dots and live pill badges.

## Components

Components are crisp, technical, and restrained, relying on 1px border shifts and subtle opacity transitions (`transition-colors` / `transition-opacity`) with zero decorative bounce.

### Buttons
- **Shape:** Gently rounded (`8px`).
- **Primary:** Solid `Cyan Container` (`#06b6d4`) fill with `#00424f` semibold text, `8px 16px` (`py-space-sm px-space-md`) or `16px 24px` (`py-space-md px-space-lg`) for hero CTAs.
- **Hover / Focus:** `opacity: 0.9` on primary fills; border shifts to `Border Highlight` (`#334155`) or `Cyan Signal` at 50% opacity on secondary/outline buttons.
- **Secondary / Outline:** `Panel Slate` (`#111827`) background with `1px` `Border Subtle` (`#1e293b`) and `Text Secondary` (`#94a3b8`) text.

### Chips
- **Style:** Pill (`9999px`) for live status badges (`1px` `Cyan Signal` at 30% opacity, `Cyan Signal` at 5% fill) with a pulsing `6px` `Metric Efficiency` dot.

### Cards / Containers
- **Corner Style:** `12px` (`rounded-xl`).
- **Background:** `Panel Slate` (`#111827`) or `Deep Surface` (`#0b111a`) with `.glass-card` blur on featured/overlay panels.
- **Border:** `1px solid` `Border Subtle` (`#1e293b`).
- **Internal Padding:** `16px` (`p-space-md`) for compact metric cards, `40px` (`p-space-xl`) for major calculator/feature containers.

### Inputs / Fields
- **Style:** `Panel Slate` (`#111827`) fill, `1px solid` `Border Subtle`, `8px` radius, `8px 16px` padding.
- **Focus:** Border shifts to `Cyan Signal` at 50% opacity (`focus:border-primary/50`) with `outline: none`.

### Navigation
- **Style:** Fixed `40px` top telemetry ticker (`Abyssal Base`) above a `72px` translucent `Deep Surface` header (`backdrop-blur-xl`, `1px` bottom border). Active links carry `Cyan Signal` text and a `2px` bottom border; inactive links use `Text Secondary` transitioning to `Text Primary` on hover. Mobile uses a slide-out drawer trigger.

## Do's and Don'ts

### Do:
- **Do** design every layout RTL-first (`dir="rtl"`) and verify both dark (default) and opt-in light mode contrast.
- **Do** use `JetBrains Mono` for all numerical metrics, latencies, prices, and telemetry strips.
- **Do** separate adjacent surfaces with tonal steps and `1px` `Border Subtle` hairlines.
- **Do** preserve dedicated metric hues (`Speed`, `Efficiency`, `Cost`, `Latency`) consistently across tables and charts.

### Don't:
- **Don't** use generic SaaS purple-pink gradients or decorative card-in-card nesting.
- **Don't** place low-contrast gray text on colored backgrounds where it slows down tabular scanning.
- **Don't** add bouncy spring animations or heavy drop shadows to data cards.
- **Don't** hardcode dark-only hex colors in views when a semantic token (`bg-surface-panel`, `text-text-primary`, `border-border-subtle`) exists.

