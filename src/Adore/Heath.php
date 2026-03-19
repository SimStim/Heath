<?php

declare(strict_types=1);

namespace Lea\Adore;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Heath
{
    private(set) string $heathName
        = PHP_EOL . Fancy::BG_GREEN . Fancy::WHITE . Fancy::BOLD . " [ HEATH ] " . Fancy::RESET;
    private string $hugo = "website";
    private array $imprintCodes = [
        "Logophilia" => "",
        "Logophilia Essentials" => " (Logophilia Essentials)",
    ];
    public array $ebookFiles {
        get => $this->gatherFiles();
    }
    private(set) array $index = [];

    public function __construct()
    {
        $this->checkDir(dir: REPO);
        $this->checkDir(dir: $this->hugo);
    }

    private function checkDir(string $dir): void
    {
        if (!is_dir(filename: $dir)) {
            echo $this->heathName . Fancy::BG_RED . Fancy::BLACK . Fancy::BLINK . " [ ERROR ] " . Fancy::UNBLINK
                . Fancy::BG_WHITE . Fancy::BLACK . " Repository expected at "
                . Fancy::RED . $dir . Fancy::BLACK . " was not found. " . Fancy::RESET . PHP_EOL;
            exit;
        }
    }

    private function gatherFiles(): array
    {
        $xmlFiles = [];
        $dir = new RecursiveDirectoryIterator(directory: REPO, flags: FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dir);
        foreach ($iterator as $file)
            if ($file->isFile() && $iterator->getDepth() > 0 && strtolower($file->getExtension()) === 'xml')
                $xmlFiles[] = substr($file->getPathname(), strlen(Girlfriend::$pathEbooks));
        return $xmlFiles;
    }

    private function addPublicationToSeries(string $title, string $publication): void
    {
        $this->index["series"][$title]["publications"][] = $publication;
    }

    private function addSeriesToPublication(string $title, string $series, string $position): void
    {
        $this->index["publications"][$title]["series"] = ["title" => $series, "position" => $position];
    }

    private function addStoryToPublication(string $title, string $story): void
    {
        if ((!in_array($story, ["Cover", "EPUB Navigation", "The Journey Continues"]))
            && (!str_starts_with($story, needle: "About"))
            && (!in_array(needle: $story, haystack: $this->index["publications"][$title]["stories"] ?? [])))
            $this->index["publications"][$title]["stories"][] = $story;
    }

    private function addAuthorToPublication(string $title, string $author): void
    {
        if (($author !== Girlfriend::comeToMe()->leaNamePlain)
            && (!in_array(needle: $author, haystack: $this->index["publications"][$title]["authors"] ?? [])))
            $this->index["publications"][$title]["authors"][] = $author;
    }

    private function addBioToAuthor(string $title, $bio): void
    {
        if ($title !== Girlfriend::comeToMe()->leaNamePlain)
            $this->index["authors"][$title]["bio"] = $bio;
    }

    private function addStoryToAuthor(string $title, $story): void
    {
        if ((!in_array($story, ["Cover", "EPUB Navigation", "The Journey Continues"]))
            && (!str_starts_with($story, needle: "About"))
            && (!in_array(needle: $story, haystack: $this->index["authors"][$title]["stories"] ?? [])))
            $this->index["authors"][$title]["stories"][] = $story;
    }

    private function addPublicationToAuthor(string $title, string $publication): void
    {
        if (!in_array($publication, haystack: $this->index["authors"][$title]["publications"] ?? []))
            $this->index["authors"][$title]["publications"][] = $publication;
    }

    private function addAuthorToStory(string $title, string $author): void
    {
        if (($author !== Girlfriend::comeToMe()->leaNamePlain)
            && (!in_array($title, ["Cover", "EPUB Navigation", "The Journey Continues"]))
            && (!str_starts_with($title, needle: "About"))
            && (!in_array(needle: $author, haystack: $this->index["stories"][$title]["authors"] ?? [])))
            $this->index["stories"][$title]["authors"][] = $author;
    }

    private function addBlurbToStory(string $title, string $blurb): void
    {
        if ((!in_array($title, ["Cover", "EPUB Navigation", "The Journey Continues"]))
            && (!str_starts_with($title, needle: "About")))
            $this->index["stories"][$title]["blurb"] = $blurb;
    }

    private function addPublicationToStory(string $title, string $publication): void
    {
        if ((!in_array($title, ["Cover", "EPUB Navigation", "The Journey Continues"]))
            && (!str_starts_with($title, needle: "About")))
            $this->index["stories"][$title]["publications"][] = $publication;
    }

    public function index(PaisleyPark $work): void
    {
        if ($work->ebook->collection->title !== "") {
            $this->addPublicationToSeries(
                title: $work->ebook->collection->title,
                publication: $work->ebook->title . $this->imprintCodes[$work->ebook->publisher->imprint]
            );
            $this->addSeriesToPublication(
                title: $work->ebook->title . $this->imprintCodes[$work->ebook->publisher->imprint],
                series: $work->ebook->collection->title,
                position: $work->ebook->collection->position
            );
        }
        foreach ($work->ebook->texts as $text) {
            $this->addStoryToPublication(
                title: $work->ebook->title . $this->imprintCodes[$work->ebook->publisher->imprint],
                story: $text->title
            );
            $this->addPublicationToStory(
                title: $text->title,
                publication: $work->ebook->title . $this->imprintCodes[$work->ebook->publisher->imprint]
            );
            $this->addBlurbToStory(
                title: $text->title,
                blurb: $text->blurb
            );
            foreach ($text->authors as $author) {
                $this->addBioToAuthor(
                    title: $author->name,
                    bio: @file_get_contents(filename: REPO . "heath/authors/" . $author->name . ".xhtml") ?? ""
                );
                $this->addAuthorToStory(
                    title: $text->title,
                    author: $author->name
                );
                $this->addStoryToAuthor(
                    title: $author->name,
                    story: $text->title
                );
                $this->addAuthorToPublication(
                    title: $work->ebook->title . $this->imprintCodes[$work->ebook->publisher->imprint],
                    author: $author->name
                );
                $this->addPublicationToAuthor(
                    title: $author->name,
                    publication: $work->ebook->title . $this->imprintCodes[$work->ebook->publisher->imprint]
                );
            }
        }
        print_r($this->index["authors"]);
    }
}
