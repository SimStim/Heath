# Lea Scripts (AlphabetSt)

`<lea:script>` is Lea's extensibility mechanism for edge cases and generated content.

## Execution model
Scripts are executed in multiple compiler phases (not a single-pass macro):

1. after loading ebook XML and reading subfolder/options
2. after XHTML validation is complete
3. after block resolution (because blocks may introduce additional scripts)

Effect semantics:
- script node is consumed
- script output is inserted in place
- newly introduced script nodes can be resolved in later passes

---

## Generic `<lea:script>` contract

- selector: `textContent` of `<lea:script>`
- selector matching: case-insensitive lookup in `AlphabetSt::$lut`
- attributes: free-form; each script decides what it reads and whether it is required

Unknown selector behavior:
- depends on compiler implementation; should be treated as invalid usage unless explicitly tolerated.

---

## Registry (current `AlphabetSt`)

Source: `src/Adore/AlphabetSt.php`

### 1) `tableOfContents`
Aliases:
- `toc`
- `table of contents`
- `list content`

Reads attributes:
- none

Behavior:
- inserts `<ol><li><lea:link>...</lea:link></li>...</ol>` from ebook text list.

### 2) `tableOfContentsPlain`
Aliases:
- `toc plain`
- `list content plain`
- `table of contents plain`

Reads attributes:
- `skip` (optional regex body; wrapped as `/<skip>/i`)

Behavior:
- inserts sorted plain-text title list, optionally filtered by `skip`.

### 3) `listRights`
Aliases:
- `colophon`
- `list rights`
- `text rights`
- `list text rights`

Reads attributes:
- none

Behavior:
- concatenates rights content from all texts.

### 4) `listBlurbs`
Aliases:
- `blurbs`
- `list blurbs`
- `text blurbs`

Reads attributes:
- `class` (optional; default `""`)
- `heading` (optional; default `h4`)

Behavior:
- builds heading + blurb blocks for texts with blurbs.

### 5) `listAuthors`
Aliases:
- `list authors`
- `authors`
- `text authors`

Reads attributes:
- `folder` (optional; prefix for block paths)
- `class` (optional; applied to separator div)
- `skip` (optional regex body; wrapped as `/<skip>/i`)

Behavior:
- deduplicates/sorts authors (uses `fileAs` when present), emits `<lea:block>` entries.

### 6) `linkedImage`
Aliases:
- `linked image`

Reads attributes:
- `to` (required)
- `image` (required)
- `caption` (optional; fallback `defaultcaption` memory key)
- `folder` (optional; fallback `subfolder-images` memory key)

Behavior:
- emits `<figure><lea:link to='...'><img .../></lea:link><figcaption>...</figcaption></figure>`
- registers image in ebook image set.

Failure behavior:
- missing `to` -> `linkedImageMissingTo`
- missing `image` -> `linkedImageMissingImage`

---

## Notes for grammar/docs

- `lea:script` attribute requirements are script-specific and should live here, not in core grammar.
- Keep this file synced with `AlphabetSt` whenever new handlers are added or signatures/attribute requirements change.
