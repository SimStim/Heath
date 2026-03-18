# Lea Grammar

This document defines the user-facing grammar for Lea source files.

Scope:
- Ebook config XML (`arx/ebooks/.../*.xml`)
- Text/block XHTML files (`arx/text/...`, `arx/blocks/...`)

Conventions:
- **count** is cardinality in the relevant document scope.
- **string** means value resolved from `textContent` (inline markup stripped unless otherwise noted).
- **innerHTML** means inline markup is preserved as markup payload.

---

## `<lea:title>`
- count: `1`
- format: `string` (resolved via `textContent`)

## `<lea:author>`
- count: `> 0`
- format: `string` (resolved via `textContent`)
- attributes:
  - `file-as` — count `[0,1]`, format `string`

## `<lea:date>`
- count: `1`
- format: anything `DateTime` understands
- attributes:
  - `created` — count `[0,1]`, format anything `DateTime` understands

## `<lea:description>`
- count: `>= 0`
- format: `string` (resolved via `textContent`)

## `<lea:collection>`
- count: `[0,1]`
- format: `string` (resolved via `textContent`)
- attributes:
  - `type` — count `1`, allowed value: `series`
  - `position` — count `1`, format `integer`
  - `issn` — count `1`, format `string`

## `<lea:publisher>`
- count: `1`
- format: `string` (resolved via `textContent`)
- attributes:
  - `contact` — count `1`, format `string`

## `<lea:rights>`
- count: `1`
- format: `innerHTML`

## `<lea:language>`
- count: `1`
- format: `string` (resolved via `textContent`)

## `<lea:subfolder>`
- count: `>= 0`
- format: `string` (resolved via `textContent`)
- attributes:
  - `tag` — count `1`, allowed values: `epub`, `text`, `images`

## `<lea:option>`
- count: `>= 0`
- format: `string` (resolved via `textContent`)
- attributes:
  - `value` — count `1`, format `string`

## `<lea:text>`
- count: `> 0`
- format: `string` (resolved via `textContent`)

## `<lea:subject>`
- count: `>= 0`
- format: `string` (resolved via `textContent`)

## `<lea:isbn>`
- count: `1`
- format: valid ISBN (`/^\d{13}$/` and checksum verified)

## `<lea:contributor>`
- count: `>= 0`
- format: `string` (resolved via `textContent`)
- attributes:
  - `roles` — count `1`, format `string`

## `<lea:font>`
- count: `>= 0`
- format: `string` (resolved via `textContent`)

## `<lea:stylesheet>`
- count: `>= 0`
- format: `string` (resolved via `textContent`)

## `<lea:cover>`
- count: `1`
- format: `string` (resolved via `textContent`)

## `<lea:image>`
- count: `>= 0`
- format: `string` (resolved via `textContent`)
- attributes:
  - `source` — count `1`, format `string`
  - `folder` — count `[0,1]`, format `string`
- children:
  - `<lea:caption>` — count `[0,1]`, format `innerHTML`

## `<lea:blurb>`
- count: `[0,1]`
- format: `innerHTML`

## `<lea:link>`
- count: `>= 0`
- format: `string` (resolved via `textContent`)

## `<lea:target>`
- count: `>= 0`
- format: `string` (resolved via `textContent`)

## `<lea:section>`
- count: `>= 0` (optional convenience tag, both per-file and globally)
- format: `string` (resolved via `textContent`)
- attributes:
  - `title` — count `[0,1]`, format `string`
  - `class` — count `[0,1]`, format `string`

## `<lea:chapter>`
- count: `>= 0` (optional convenience tag, both per-file and globally)
- format: `string` (resolved via `textContent`)
- attributes:
  - `title` — count `[0,1]`, format `string`
  - `content-top` — count `[0,1]`, format `string`
  - `class-top` — count `[0,1]`, format `string`
  - `class-bottom` — count `[0,1]`, format `string`

## `<lea:block>`
- count: `>= 0`
- format: `string` (resolved via `textContent`)
- attributes:
  - `folder` — count `[0,1]`, format `string`

## `<lea:script>`
- count: `>= 0`
- format: `string` (script selector via `textContent`)
- attributes: free-form key/value map (`string` → `string`)

`lea:script` is an extensibility hook.
- `DOMElement->textContent` identifies the runtime script handler.
- Attributes are consumed by that handler.
- Formal per-script attribute requirements are defined in `SCRIPTS.md` / `AlphabetSt`, not in core grammar.
