<?php

declare(strict_types=1);

namespace Lea\Domain;

use DOMDocument;
use DOMXPath;
use Exception;
use Lea\Adore\Girlfriend;

/**
 * Text domain class
 */
final class Text
{
    private(set) DOMDocument $dom {
        get => $this->dom ??= XMLetsGoCrazy::createDOMFromFragments($this->xhtml);
    }
    private(set) DOMXPath $xpath {
        get => $this->xpath ??= XMLetsGoCrazy::createXPath($this->dom);
    }
    private(set) ?array $authors = null {
        get => $this->authors ??= XMLetsGoCrazy::extractAuthors($this->xpath);
    }
    private(set) string $blurb {
        get => $this->blurb ??= XMLetsGoCrazy::extractBlurb($this->xpath);
    }
    private(set) string $rights {
        get => $this->rights ??= XMLetsGoCrazy::extractRights($this->xpath);
    }

    public function __construct(
        private(set) string  $fileName {
            set => trim(string: $value);
        },
        private(set) ?string $xhtml = null {
            /**
             * @throws Exception
             */
            get => $this->xhtml ??= Girlfriend::comeToMe()->readFile(
                filePath: Girlfriend::$pathText
                . Girlfriend::comeToMe()->recall(name: "subfolder-text")
                . $this->fileName
            );
            set {
                if ($value !== null) {
                    $this->xhtml = $value;
                    $this->dom = XMLetsGoCrazy::createDOM($value);
                }
            }
        },
        private(set) ?string $title = null {
            get => $this->title ??= XMLetsGoCrazy::extractTitle($this->xpath);
            set {
                if ($value !== null)
                    $this->title = $value;
            }
        }
    )
    {
    }

    public function addAuthor(Author $author): void
    {
        $this->authors = array_merge($this->authors, [$author]);
    }
}
