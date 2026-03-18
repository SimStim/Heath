# High-Level Improvement Suggestions for Lea

This report summarizes architecture and maintainability opportunities identified by reviewing core runtime/orchestration, parsing, metadata compilation, and project setup.

## 1) Decouple orchestration from validation/reporting

- `PaisleyPark::segue()` currently mixes control flow, validation rules, and user-facing error accumulation into one very large method.
- Recommendation: split into stage-oriented services (`EbookValidator`, `TextValidator`, `AssetValidator`, `BuildPipeline`) and return structured result objects.
- Benefit: easier feature additions (new rules), more isolated tests, fewer regressions from editing one giant method.

## 2) Introduce typed configuration objects for CLI/runtime options

- Runtime options are currently stored in `Girlfriend::$memory` as mutable string flags (`yes/no`, string paths).
- Recommendation: replace with immutable typed options (`BuildOptions`) parsed once from CLI and passed through dependency graph.
- Benefit: compile-time guarantees, fewer hidden global side effects, simpler testing.

## 3) Reduce global singleton/static coupling

- `Girlfriend` acts as logger, config store, path registry, utility collection, and singleton lifecycle manager.
- Recommendation: extract interfaces/services (`PathResolver`, `MessageBus`, `StringNormalizer`, `FileSystem`) and inject dependencies into orchestrators.
- Benefit: clearer responsibilities and easier unit testing without global state resets.

## 4) Add an explicit error taxonomy and machine-readable diagnostics

- Errors are currently message-template driven in `Affirmation`, useful for humans but hard for automation.
- Recommendation: add stable error codes and structured payloads (JSON output mode, severity enum, source location).
- Benefit: CI integration, IDE tooling, scriptable quality gates.

## 5) Improve XML safety and parser abstraction boundaries

- XML parsing and extraction responsibilities are centralized in `XMLetsGoCrazy` with broad static methods.
- Recommendation: separate parser (`LeaXmlParser`) from domain extractors, define small method contracts per entity type.
- Benefit: easier validation evolution (schema checks, stricter attribute handling, better namespace extensibility).

## 6) Add comprehensive automated tests and fixtures

- Project currently appears to have no `tests/` suite despite `autoload-dev` mapping and rich domain behavior.
- Recommendation: add PHPUnit/Pest tests across:
  - identifier normalization/transliteration,
  - XPath extraction and malformed input handling,
  - OPF/manifest/spine generation snapshots,
  - URL checker behavior (mocked HTTP),
  - end-to-end compile fixtures from `arx/ebooks` samples.
- Benefit: safer refactoring velocity and release confidence.

## 7) Formalize build pipeline and CI quality gates

- Recommendation: add CI workflow with minimum gates: lint, unit tests, static analysis, and a fixture build.
- Suggested tools:
  - `php -l` for syntax,
  - PHPStan/Psalm for static analysis,
  - PHP-CS-Fixer or Pint for formatting.
- Benefit: consistency and early defect detection.

## 8) Improve packaging and runtime compatibility strategy

- `composer.json` pins `php` to `^8.5`, which is very forward-leaning and may limit adoption.
- Recommendation: validate minimum required language features and consider broadening compatibility if possible.
- Benefit: easier installation and contributor onboarding.

## 9) Isolate template generation from string concatenation

- OPF/manifest/spine metadata assembly uses large string concatenation logic.
- Recommendation: switch to XML DOM builders or dedicated template renderers with escaping guarantees.
- Benefit: lower risk of malformed XML and better readability.

## 10) Add extension/runtime preflight checks

- README already notes intent to check extension availability.
- Recommendation: central `EnvironmentPreflight` check (curl, zip, dom, filesystem permissions, EPUBCheck binaries) with actionable diagnostics.
- Benefit: faster troubleshooting and better UX for new users.

---

## Suggested implementation order

1. Testing scaffold + baseline fixtures
2. Error codes + structured diagnostics
3. Options/config refactor (remove mutable global memory)
4. Pipeline decomposition (validator/build stages)
5. XML/domain extraction service split
6. CI/static analysis hardening

This sequence maximizes safety before deeper refactors.
