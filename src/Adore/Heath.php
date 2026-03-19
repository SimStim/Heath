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
    private string $hugo = "website/";
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
        $dir = new RecursiveDirectoryIterator(directory: Girlfriend::$pathEbooks, flags: FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dir);
        $xmlFiles = [];
        foreach ($iterator as $file)
            if ($file->isFile() && $iterator->getDepth() >= 0 && strtolower($file->getExtension()) === 'xml')
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

    private function addPortraitToAuthor(string $title, $portrait): void
    {
        if ($title !== Girlfriend::comeToMe()->leaNamePlain)
            $this->index["authors"][$title]["portrait"] = $portrait;
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
        if (($title !== Girlfriend::comeToMe()->leaNamePlain)
            && (!in_array($publication, haystack: $this->index["authors"][$title]["publications"] ?? [])))
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

    public function makeIndex(PaisleyPark $work): Heath
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
                $this->addPortraitToAuthor(
                    title: $author->name,
                    portrait: strtr($author->name . ".jpg", Girlfriend::$characterTransliterationMap)
                );
                $this->addBioToAuthor(
                    title: $author->name,
                    bio: @file_get_contents(filename: REPO . "heath/authors/"
                    . strtr($author->name . ".xhtml", Girlfriend::$characterTransliterationMap)) ?? ""
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
        return $this;
    }

    private function rmDir($dir): void
    {
        $it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        $it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $file) {
            if ($file->isDir()) rmdir($file->getPathname());
            else unlink($file->getPathname());
        }
        rmdir($dir);
    }

    private function writeAuthors(): void
    {
        $hugoAuthorsDir = $this->hugo . "content/authors/";
        if (is_dir(filename: $hugoAuthorsDir)) $this->rmDir(dir: $hugoAuthorsDir);
        foreach ($this->index["authors"] as $author => $properties) {
            $contents = @file_get_contents(filename: REPO . "heath/authors/"
                . strtr($author, Girlfriend::$characterTransliterationMap) . ".xhtml") ?? "";
            if ($contents === "") continue;
            $civilName = "";
            if (preg_match(pattern: "/#.*#/", subject: $contents, matches: $matches)) {
                $civilName = trim($matches[0], characters: " #");
                $contents = str_replace(search: "#$civilName#", replace: "", subject: $contents);
            }
            $output = "+++" . PHP_EOL . "draft = false" . PHP_EOL
                . "title = '" . ($civilName !== "" ? $civilName : $author) . "'" . PHP_EOL
                . "cross-reference-stories = [";
            $stories = "";
            if (isset($properties["stories"]))
                foreach ($properties["stories"] as $story)
                    $stories .= "'" . $story . "'" . ", ";
            $output .= substr($stories, offset: 0, length: strlen($stories) - 2) . "]" . PHP_EOL
                . "cross-reference-publications = [";
            $publications = "";
            foreach ($properties["publications"] as $publication)
                $publications .= "'" . $publication . "'" . ", ";
            $output .= substr($publications, offset: 0, length: strlen($publications) - 2) . "]" . PHP_EOL
                . "+++" . PHP_EOL
                . (is_file(filename: $this->hugo . "assets/img/authors/" . $properties["portrait"])
                    ? "{{< author-portrait" . PHP_EOL
                    . "    src=\"img/authors/" . $properties["portrait"] . "\"" . PHP_EOL
                    . "    alt=\"Illustration by Eduard Pech\"" . PHP_EOL
                    . ">}}" . PHP_EOL . PHP_EOL
                    : "")
                . $contents . PHP_EOL;
            if (!is_dir(filename: $hugoAuthorsDir))
                mkdir(directory: $hugoAuthorsDir, permissions: 0755, context: null);
            file_put_contents(
                filename: $hugoAuthorsDir
                . Girlfriend::comeToMe()->strToEpubIdentifier($author) . ".html",
                data: $output
            );
        }
    }

    private function writeStories(): void
    {
        $hugoStoriesDir = $this->hugo . "content/stories/";
        if (is_dir(filename: $hugoStoriesDir)) $this->rmDir(dir: $hugoStoriesDir);
        foreach ($this->index["stories"] as $story => $properties) {
            $output = "+++" . PHP_EOL
                . "draft = false" . PHP_EOL
                . "title = '" . $story . "'" . PHP_EOL
                . "cross-reference-authors = [";
            $authors = "";
            foreach ($properties["authors"] as $author)
                $authors .= "'" . $author . "'" . ", ";
            $output .= substr($authors, offset: 0, length: strlen($authors) - 2) . "]" . PHP_EOL
                . "cross-reference-publications = [";
            $publications = "";
            foreach ($properties["publications"] as $publication)
                $publications .= "'" . $publication . "'" . ", ";
            $output .= substr($publications, 0, strlen($publications) - 2) . "]" . PHP_EOL
                . "+++" . PHP_EOL
                . "<p>" . $properties["blurb"] . "</p>" . PHP_EOL;
            if (!is_dir(filename: $hugoStoriesDir))
                mkdir(directory: $hugoStoriesDir, permissions: 0755, context: null);
            file_put_contents(
                filename: $hugoStoriesDir
                . Girlfriend::comeToMe()->strToEpubIdentifier($story) . ".html",
                data: $output
            );
        }
    }

    private function writePublications(): void
    {
        $hugoPublicationsDir = $this->hugo . "content/publications/";
        if (is_dir(filename: $hugoPublicationsDir)) $this->rmDir(dir: $hugoPublicationsDir);
        foreach ($this->index["publications"] as $publication => $properties) {
            $output = "+++" . PHP_EOL
                . "draft = false" . PHP_EOL
                . "title = '" . $publication . "'" . PHP_EOL
                . "cross-reference-authors = [";
            $authors = "";
            foreach ($properties["authors"] as $author)
                $authors .= "'" . $author . "'" . ", ";
            $output .= substr($authors, offset: 0, length: strlen($authors) - 2) . "]" . PHP_EOL
                . "cross-reference-stories = [";
            $stories = "";
            foreach ($properties["stories"] as $story)
                $stories .= "'" . $story . "'" . ", ";
            $output .= substr($stories, offset: 0, length: strlen($stories) - 2) . "]" . PHP_EOL;
            if (isset($properties["series"]))
                $output .= "cross-reference-series = [\"" . $properties["series"]["title"] . "\"]" . PHP_EOL
                    . "series_order = " . $properties["series"]["position"] . PHP_EOL;
            $output .= "+++" . PHP_EOL;
            foreach ($properties["stories"] as $story) {
                $authors = "";
                foreach ($this->index["stories"][$story]["authors"] as $author) {
                    $authors .= "<a href=\"/authors/" . Girlfriend::comeToMe()->strToEpubIdentifier($author) . "\">"
                        . $author . "</a>, ";
                }
                $output .= "<h2><a href=\"/stories/" . Girlfriend::comeToMe()->strToEpubIdentifier($story) . "\">"
                    . $story . "</a> by "
                    . substr($authors, offset: 0, length: strlen($authors) - 2) . "</h2>" . PHP_EOL . PHP_EOL
                    . "<p>" . $this->index["stories"][$story]["blurb"] . "</p>" . PHP_EOL . PHP_EOL;
            }
            if (!is_dir(filename: $hugoPublicationsDir))
                mkdir(directory: $hugoPublicationsDir, permissions: 0755, context: null);
            file_put_contents(
                filename: $hugoPublicationsDir
                . Girlfriend::comeToMe()->strToEpubIdentifier($publication) . ".html",
                data: $output
            );
        }
    }

    private function writeSeries(): void
    {
        $hugoSeriesDir = $this->hugo . "content/series/";
        if (is_dir(filename: $hugoSeriesDir)) $this->rmDir(dir: $hugoSeriesDir);
        foreach ($this->index["series"] as $series => $properties) {
            $output = "+++" . PHP_EOL
                . "draft = false" . PHP_EOL
                . "title = '" . $series . "'" . PHP_EOL
                . "cross-reference-publications = [";
            $publications = "";
            foreach ($properties["publications"] as $publication)
                $publications .= "\"" . $publication . "\"" . ", ";
            $output .= substr($publications, offset: 0, length: strlen($publications) - 2) . "]" . PHP_EOL
                . "+++" . PHP_EOL;
            if ($series === "The Pitch Science Fiction")
                $output .= "<p>The Pitch Science Fiction is a quarterly publication of science fiction stories"
                    . " by various authors, covering diverse themes. It aims to explore the intersection"
                    . " of speculative fiction with contemporary issues, offering readers a glimpse"
                    . " into the future through the lens of imagination and innovation.</p>" . PHP_EOL;
            if (!is_dir(filename: $hugoSeriesDir))
                mkdir(directory: $hugoSeriesDir, permissions: 0755, context: null);
            file_put_contents(
                filename: $hugoSeriesDir
                . Girlfriend::comeToMe()->strToEpubIdentifier($series) . ".html",
                data: $output
            );
        }
    }

    public function writeIndex(): Heath
    {
        $this->writeAuthors();
        $this->writeStories();
        $this->writePublications();
        $this->writeSeries();
        return $this;
    }
}
