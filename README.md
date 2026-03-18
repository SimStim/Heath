# 🝄 Lea EPUB Anvil

Lea produces EPUBs conforming to the latest standards, i.e., 3.3 at the time of writing.
Lea is not trying to be a full EPUB authoring environment, a semantic curator,
or a replacement for Sigil's editorial layer. Lea is a compiler, validator, and normalizer.
The output is intended to be opened in Sigil. Lea guarantees correctness,
human editors can add meaning later.

## Features

- filename and identifier consistency
- dynamic local cross-file link targets
- external link target validity check
- generation of EPUB manifest, including metadata and spine
- error messages with focused suggestions for fixes
- easy to learn and use if you know some XML or HTML
- Lea is a bitch, the female dog variant; you can always rely on her

### Existing

* filename and identifier consistency
* dynamic local cross-file link targets
* generation of EPUB OPF manifest
* support for book series collections with ISSN
* support for anthologies with per-text authors and rights
* support for text blocks
* support for the execution of pre-made scripts enabling handling of any edge cases like linked images
* <lea:> tags anywhere in the source files, for example, for images with captions
* validation of external links
* support for advanced typesetting: embedded fonts and stylesheets
* EPUBCheck validation
* command-line arguments for run-time configuration
* repo includes the world's very first, fully Lea-produced actually published title,
  compiling ~8 MB into a 3.3 MB EPUB file in 0.273 seconds on my Zen-2 based notebook

### Planned

* automatic detection and content-based compression of image files
* import of word processor documents into Lea definition files
* simple but useful reports
* important: throw error on unknown attributes instead of silently ignoring them.
* refactor for a new LeaTags class enabling case-insensitive cached XPath queries.
* new helper class NewPowerGeneration.
* pipe every file access through Girlfriend for centralized error handling.
* check for the existence of extensions before using them, e.g., curl.
* might as well add the ncx, generated EPUB will be backwards-compatible with the EPUB2 standard.
* time-frame: import of word processor files and image manipulations before 2Q2026.

## 1.0.0 and ⊙

🝄 stands for Lea as a holistic entity. The glyph ⊙ was chosen as a marker for specific aspects
at defined points in time. If 🝄 is the flux for soldering, then ⊙ is the moment when
the soldering iron heats the flux and a new creation emerges.

⊙ expresses a structured combination where the structure matters more than the operands.
It is not about symmetry but coherence. ⊙ marks the point around which a meaning is calibrated.
Lea's process is information-preserving normalization, or a lossless canonicalization.

With the release of "⊙ 1.0.0," Lea will be able to dynamically create an EPUB
from config and content files only.

Releases marked with ⊙ are recommended for most users. These releases will be stable,
have long-term support, and will come with major new features.

## License

Copyright Eduard Pech (https://logophilia.eu/the-director-without-a-service-weapon/). Licensed under
PolyForm-Noncommercial-1.0.0; see separate file LICENSE.md for details.
