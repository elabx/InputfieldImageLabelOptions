# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

ProcessWire Inputfield modules that render FieldtypeOptions fields as selectable images or styled labels instead of default radio buttons/checkboxes. Uses the hidden-input-styled-label technique (hide native input, style `<label>` as clickable selector). No custom Fieldtype needed — works as a drop-in replacement.

## Architecture

Two near-identical module pairs, differing only in input type (radio vs checkbox):

| Module | Extends | Input Type | Selection |
|--------|---------|------------|-----------|
| `InputfieldRadiosImageLabel` | `InputfieldRadios` | `radio` | Single-select |
| `InputfieldCheckboxesImageLabel` | `InputfieldCheckboxes` | `checkbox` | Multi-select |

Each module has a `.module.php` (PHP class) and a matching `.css` file.

**Key PHP methods in each module:**
- `init()` — Declares config properties: `optionImages`, `optionImageDesktopWidth`, `optionImageMobileWidth`, `optionImageAspectRatio`, `optionImageShowLabel`
- `___getConfigInputfields()` — Builds the admin configuration UI (hookable, triple-underscore convention)
- `render()` — Generates HTML output with CSS custom properties for responsive sizing

**CSS approach:** All configurable values use CSS custom properties defined on the wrapper element. Sizing properties (`--inputfield-image-label-options-desktop-width`, `-mobile-width`, `-aspect-ratio`) are set inline by PHP's `render()`. Theming properties (colors and border width) are defined in the stylesheet with defaults:
- `--inputfield-image-label-options-border-color` (`#d9e1ea`) — default border
- `--inputfield-image-label-options-checked-color` (`#3eb998`) — selected state
- `--inputfield-image-label-options-border-width` (`3px`)
- `--inputfield-image-label-options-label-color`, `-text-bg`, `-text-checked-bg`, `-text-checked-color`

Selection state uses `input:checked + .content .image-wrapper` selector. Responsive breakpoint at 768px.

## Install & Development

```bash
composer require elabx/inputfield-image-label-options
```

No build system, no JS, no tests. For manual install, copy files into ProcessWire's `site/modules/` and refresh modules in the admin.

## Conventions

- Namespace: `ProcessWire`
- ProcessWire hookable methods use triple-underscore prefix (`___methodName`)
- Option-to-image mapping format: `option_id=image_url` (one per line)
- CSS wrapper classes follow pattern: `.Inputfield{Radios|Checkboxes}ImageLabelWrapper`
- When changing one module, apply the equivalent change to the other (they are intentionally parallel)
