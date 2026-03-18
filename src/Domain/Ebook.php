<?php

declare(strict_types=1);

namespace Lea\Domain;

use DOMDocument;
use DOMXPath;
use Exception;
use Lea\Adore\Girlfriend;

/**
 * Ebook domain class
 */
final class Ebook
{
    private(set) string $xml {
        /**
         * @throws Exception
         */
        get => $this->xml ??= Girlfriend::comeToMe()->readFile(filePath: Girlfriend::$pathEbooks . $this->fileName);
    }
    private(set) DOMDocument $dom {
        get => $this->dom ??= XMLetsGoCrazy::createDOMFromFragments($this->xml);
    }
    private(set) DOMXPath $xpath {
        get => $this->xpath ??= XMLetsGoCrazy::createXPath($this->dom);
    }
    private(set) string $title {
        get => $this->title ??= XMLetsGoCrazy::extractTitle($this->xpath);
    }
    private(set) string $description {
        get => $this->description ??= XMLetsGoCrazy::extractDescription($this->xpath);
    }
    private(set) Publisher $publisher {
        get => $this->publisher ??= XMLetsGoCrazy::extractPublisher($this->xpath);
    }
    private(set) string $rights {
        get => $this->rights ??= XMLetsGoCrazy::extractRights($this->xpath);
    }
    private(set) string $language {
        get => $this->language ??= XMLetsGoCrazy::extractLanguage($this->xpath);
    }
    private(set) array $authors {
        get => $this->authors ??= XMLetsGoCrazy::extractAuthors($this->xpath);
    }
    private(set) Date $date {
        get => $this->date ??= XMLetsGoCrazy::extractDate($this->xpath);
    }
    private(set) array $contributors {
        get => $this->contributors ??= XMLetsGoCrazy::extractContributors($this->xpath);
    }
    private(set) ISBN $isbn {
        get => $this->isbn ??= XMLetsGoCrazy::extractISBN($this->xpath);
    }
    private(set) Collection $collection {
        get => $this->collection ??= XMLetsGoCrazy::extractCollection($this->xpath);
    }
    private(set) array $texts {
        get => $this->texts ??= XMLetsGoCrazy::extractTexts($this->xpath);
    }
    private(set) array $subjects {
        get => $this->subjects ??= XMLetsGoCrazy::extractSubjects($this->xpath);
    }
    private(set) string $cover {
        get => $this->cover ??= XMLetsGoCrazy::extractCover($this->xpath);
    }
    private(set) array $stylesheets {
        get => $this->stylesheets ??= XMLetsGoCrazy::extractStylesheets($this->xpath);
    }
    private(set) array $fonts {
        get => $this->fonts ??= XMLetsGoCrazy::extractFonts($this->xpath);
    }
    private(set) array $images {
        get => $this->images ??= XMLetsGoCrazy::extractImages($this);
    }
    private(set) array $targets {
        get => $this->targets ??= $this->extractTargetsFromTexts();
    }

    public function __construct(
        private(set) string $fileName {
            set => trim(string: $value);
        }
    )
    {
    }

    /**
     * Extract all link targets from all texts in the ebook.
     *
     * @return array
     */
    private function extractTargetsFromTexts(): array
    {
        $targets = [];
        foreach ($this->texts as $text)
            $targets = array_merge(
                $targets,
                XMLetsGoCrazy::extractTargets(
                    xpath: $text->xpath,
                    targetFileName: Girlfriend::comeToMe()->strToEpubTextFileName(
                        title: $text->title . " by " . $text->authors[0]->name
                    ),
                ));
        return $targets;
    }

    /**
     * Adds a Text object to the collection of texts.
     *
     * @param Text $text The Text object to be added.
     * @return void
     */
    public function addText(Text $text): void
    {
        $this->texts = array_merge($this->texts, [$text]);
    }

    /**
     * Adds an Author object to the list of authors.
     *
     * @param Author $author
     * @return void
     */
    public function addAuthor(Author $author): void
    {
        $this->authors = array_merge($this->authors, [$author]);
    }

    /**
     * Erases the list of authors in preparation for OPF metadata generation.
     * This allows for an easy removal of duplicates.
     *
     * @return void
     */
    public function eraseAuthors(): void
    {
        $this->authors = [];
    }

    /**
     * Adds Image objects to the collection of images.
     *
     * @param array $images
     * @return void
     */
    public function addImages(array $images): void
    {
        $this->images = array_merge($this->images, $images);
    }

    /**
     * Adds subjects to the collection of subjects.
     *
     * @param array $subjects
     * @return void
     */
    public function addSubjects(array $subjects): void
    {
        $this->subjects = array_unique(array_merge($this->subjects, $subjects));
    }
}
