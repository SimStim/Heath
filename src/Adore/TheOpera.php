<?php

declare(strict_types=1);

namespace Lea\Adore;

use DOMException;
use Exception;
use NoDiscard;
use RuntimeException;
use ZipArchive;
use Lea\Domain\Date;
use Lea\Domain\Ebook;
use Lea\Domain\XMLetsGoCrazy;

/**
 * Ah, the opera.
 */
final class TheOpera
{
    private array $identifiers {
        get => $this->identifiers ??= $this->compileIdentifiers();
    }
    private array $idMarkers = [
        "text" => "lea:text:",
        "author" => "lea:creator:",
        "contributor" => "lea:contributor:",
        "font" => "lea:font:",
        "image" => "lea:image:",
        "stylesheet" => "lea:stylesheet:",
    ];

    public function __construct(private(set) readonly Ebook $ebook)
    {
    }

    /**
     * The Overture:
     * - Happens before the opera
     * - Sets the themes
     * - Establishes consequences
     * - Tells the audience how to listen
     *
     * @return void (also known as Caesura)
     * @throws Exception
     */
    public function theOverture(): void
    {
        $hashes = [
            "PurpleRain.txt" => "247e5c56d2619ee9d29c4c56d69cacf917b49a572696ea60ba742d365b983112",
            "mimetype" => "e468e350d1143eb648f60c7b0bd6031101ec0544a361ca74ecef256ac901f48b",
            "container.xml" => "c54cb884813a53ce2fc9b3102ca8ee5c03b0397a2cb984500830e86c65ec092f",
            "covertemplate.xhtml" => "2d3d15c1277cc6a1f429afb8ef8dcc8e04949ccc4f743c4039333245ca7f76ce",
            "navtemplate.xhtml" => "00b2b08eab2ce2b7069dd7b942f9333b5dd643e6d21a19753cc4ff33b3c64647",
            "tocncxtemplate.xhtml" => "ee8ed1d413f0dfe19be08f3ee6e95a033dbd4e955f20204457f566d8c2a4711c",
            "lea-logo-ascii.txt" => "fa89b6f5ec8ba63ccc5b1f83dff81208e0cb7a272824caaeea464cc16ae67a0b",
        ];
        foreach ($hashes as $fileName => $hash) {
            $content = Girlfriend::comeToMe()->readFile(filePath: Girlfriend::$pathPurpleRain . $fileName);
            if (hash(algo: "sha256", data: $content) !== $hash) {
                echo($fileName === array_key_first($hashes)
                    ? "Die weißen Tauben sind müde." . PHP_EOL
                    : Girlfriend::comeToMe()->readFile(filePath: Girlfriend::$pathPurpleRain . array_key_first($hashes)));
                exit;
            }
        }
    }

    /**
     * Build normalized identifiers for all relevant objects.
     *
     * @return array
     */
    private function compileIdentifiers(): array
    {
        $identifiers = [];
        foreach ($this->ebook->authors as $author) {
            $identifiers[$this->idMarkers["author"] . $author->name] = [
                "epubIdentifier" => Girlfriend::$leaPrefixes["creator"]
                    . Girlfriend::comeToMe()->strToEpubIdentifier($author->name),
                "epubFileName" => "",
            ];
        }
        foreach ($this->ebook->contributors as $contributor) {
            $identifiers[$this->idMarkers["contributor"] . $contributor->name] = [
                "epubIdentifier" => Girlfriend::$leaPrefixes["contributor"]
                    . Girlfriend::comeToMe()->strToEpubIdentifier($contributor->name),
                "epubFileName" => "",
            ];
        }
        foreach ($this->ebook->texts as $text) {
            $title = $text->title . " by " . $text->authors[0]->name;
            $identifiers[$this->idMarkers["text"] . $text->fileName] = [
                "epubIdentifier" => Girlfriend::$leaPrefixes["text"]
                    . Girlfriend::comeToMe()->strToEpubIdentifier($title),
                "epubFileName" => Girlfriend::comeToMe()->strToEpubTextFileName($title),
            ];
            foreach ($text->authors as $author) {
                $identifiers[$this->idMarkers["author"] . $author->name] = [
                    "epubIdentifier" => Girlfriend::$leaPrefixes["creator"]
                        . Girlfriend::comeToMe()->strToEpubIdentifier($author->name),
                    "epubFileName" => "",
                ];
            }
        }
        $identifiers[$this->idMarkers["text"] . "nav.xhtml"] = [
            "epubIdentifier" => Girlfriend::$leaPrefixes["text"] . "nav-xhtml",
            "epubFileName" => "nav.xhtml",
        ];
        foreach ($this->ebook->fonts as $font) {
            $identifiers[$this->idMarkers["font"] . $font] = [
                "epubIdentifier" => Girlfriend::$leaPrefixes["font"] . Girlfriend::comeToMe()->strToEpubIdentifier($font),
                "epubFileName" => basename($font),
            ];
        }
        foreach ($this->ebook->stylesheets as $stylesheet) {
            $identifiers[$this->idMarkers["stylesheet"] . $stylesheet] = [
                "epubIdentifier" => Girlfriend::$leaPrefixes["stylesheet"]
                    . Girlfriend::comeToMe()->strToEpubIdentifier($stylesheet),
                "epubFileName" => basename($stylesheet),
            ];
        }
        foreach ($this->ebook->images as $image) {
            $identifiers[$this->idMarkers["image"] . $image->fileName] = [
                "epubIdentifier" => Girlfriend::$leaPrefixes["image"] . Girlfriend::comeToMe()->strToEpubIdentifier($image->fileName),
                "epubFileName" => Girlfriend::comeToMe()->strToEpubImageFileName($image->fileName),
            ];
        }
        $identifiers[$this->idMarkers["image"] . $this->ebook->cover] = [
            "epubIdentifier" => Girlfriend::$leaPrefixes["image"] . Girlfriend::comeToMe()->strToEpubIdentifier($this->ebook->cover),
            "epubFileName" => Girlfriend::comeToMe()->strToEpubImageFileName($this->ebook->cover),
        ];
        return $identifiers;
    }

    /**
     * Generates the cover xhtml from a template file, injecting the extracted cover file name.
     *
     * @return string
     * @throws Exception
     */
    public function generateCoverFile(): string
    {
        return str_replace(
            search: "###",
            replace: Girlfriend::comeToMe()->strToEpubImageFileName($this->ebook->cover),
            subject: Girlfriend::comeToMe()->readFile(filePath: Girlfriend::$pathPurpleRain . "covertemplate.xhtml")
        );
    }

    /**
     * Generates the nav.xhtml from a template file, injecting the table of contents.
     *
     * @return string
     * @throws Exception
     */
    public function generateNavFile(): string
    {
        $format = "<li><a href='%s'>%s</a></li>\n";
        $toc = sprintf(
            $format,
            $this->identifiers[$this->idMarkers["text"] . "cover.xhtml"]["epubFileName"],
            "Cover",
        );
        foreach ($this->ebook->texts as $text) {
            if (($text->title !== "Cover") && ($text->title !== "EPUB Navigation"))
                $toc .= sprintf(
                    $format,
                    $this->identifiers[$this->idMarkers["text"] . $text->fileName]["epubFileName"],
                    $text->title,
                );
        }
        $toc .= sprintf(
            $format,
            $this->identifiers[$this->idMarkers["text"] . "nav.xhtml"]["epubFileName"],
            "Table of Contents",
        );
        return str_replace(
            search: "###",
            replace: $toc,
            subject: Girlfriend::comeToMe()->readFile(filePath: Girlfriend::$pathPurpleRain . "navtemplate.xhtml")
        );
    }

    /**
     * Builds metadata block of content.opf.
     *
     * @return string
     */
    private function compileMetadata(): string
    {
        $collectionId = "";
        $metadata = "<dc:format>application/epub+zip</dc:format>" . PHP_EOL
            . "<dc:type>Text</dc:type>" . PHP_EOL
            . "<meta property='dcterms:created'>" . $this->ebook->date->created . "</meta>" . PHP_EOL
            . "<meta property='dcterms:modified'>" . $this->ebook->date->modified . "</meta>" . PHP_EOL
            . "<meta property='dcterms:issued'>" . $this->ebook->date->issued . "</meta>" . PHP_EOL
            . "<dc:date>" . $this->ebook->date->issued . "</dc:date>" . PHP_EOL
            . "<dc:title>" . $this->ebook->title . "</dc:title>" . PHP_EOL
            . "<dc:description>" . $this->ebook->description . "</dc:description>" . PHP_EOL;
        if ($this->ebook->collection->title !== "") {
            $collectionId = Girlfriend::$leaPrefixes["collection"] . Girlfriend::comeToMe()->strToEpubIdentifier($this->ebook->collection->title);
            $metadata .= "<meta property='dcterms:isPartOf'>urn:issn:" . $this->ebook->collection->issn . "</meta>" . PHP_EOL
                . "<meta property='belongs-to-collection' id='$collectionId'>" . $this->ebook->collection->title . "</meta>" . PHP_EOL
                . "<meta refines='#$collectionId' property='collection-type'>series</meta>" . PHP_EOL
                . "<meta refines='#$collectionId' property='group-position'>" . $this->ebook->collection->position . "</meta>" . PHP_EOL;
        }
        $metadata .= "<dc:identifier id='isbn'>" . $this->ebook->isbn->isbn . "</dc:identifier>" . PHP_EOL
            . "<meta refines='#isbn' property='identifier-type'>ISBN</meta>" . PHP_EOL;
        if ($collectionId !== "")
            $metadata .= "<dc:identifier id='issn'>urn:issn:" . $this->ebook->collection->issn . "</dc:identifier>" . PHP_EOL
                . "<meta refines='#issn' property='identifier-type'>ISSN</meta>" . PHP_EOL;
        $metadata .= "<dc:publisher>" . $this->ebook->publisher->imprint . "</dc:publisher>" . PHP_EOL
            . "<meta property='dcterms:contact'>" . $this->ebook->publisher->contact . "</meta>" . PHP_EOL
            . "<meta property='dcterms:identifier' id='imprint'>" . $this->ebook->publisher->imprint . "</meta>" . PHP_EOL
            . "<dc:rights>" . $this->ebook->rights . "</dc:rights>" . PHP_EOL
            . "<dc:language>" . $this->ebook->language . "</dc:language>" . PHP_EOL;
        foreach ($this->ebook->authors as $author) {
            $metadata .= "<dc:creator id='" . Girlfriend::$leaPrefixes["creator"]
                . Girlfriend::comeToMe()->strToEpubIdentifier($author->name)
                . "'>" . $author->name . "</dc:creator>" . PHP_EOL;
            if ($author->fileAs !== "")
                $metadata .= "<meta refines='#" . Girlfriend::$leaPrefixes["creator"]
                    . Girlfriend::comeToMe()->strToEpubIdentifier($author->name)
                    . "' property='file-as'>" . $author->fileAs . "</meta>" . PHP_EOL;
        }
        foreach ($this->ebook->authors as $author)
            $metadata .= "<meta refines='#" . Girlfriend::$leaPrefixes["creator"]
                . Girlfriend::comeToMe()->strToEpubIdentifier($author->name)
                . "' property='role' scheme='marc:relators'>aut</meta>" . PHP_EOL;
        $seq = 1;
        foreach ($this->ebook->authors as $author)
            $metadata .= "<meta refines='#" . Girlfriend::$leaPrefixes["creator"]
                . Girlfriend::comeToMe()->strToEpubIdentifier($author->name)
                . "' property='display-seq'>" . $seq++ . "</meta>" . PHP_EOL;
        foreach ($this->ebook->contributors as $contributor) {
            $metadata .= "<dc:contributor id='" . Girlfriend::$leaPrefixes["contributor"]
                . Girlfriend::comeToMe()->strToEpubIdentifier($contributor->name)
                . "'>" . $contributor->name . "</dc:contributor>" . PHP_EOL;
            foreach ($contributor->roles as $role)
                $metadata .= "<meta refines='#" . Girlfriend::$leaPrefixes["contributor"]
                    . Girlfriend::comeToMe()->strToEpubIdentifier($contributor->name)
                    . "' property='role' scheme='marc:relators'>$role</meta>" . PHP_EOL;
        }
        foreach ($this->ebook->texts as $text)
            foreach ($text->authors as $author)
                if ($author->name !== Girlfriend::comeToMe()->leaNamePlain)
                    $metadata .= "<meta refines='#" . Girlfriend::$leaPrefixes["text"]
                        . Girlfriend::comeToMe()->strToEpubIdentifier($text->title . " by " . $text->authors[0]->name)
                        . "' property='dcterms:creator'>" . $author->name . "</meta>" . PHP_EOL;
        $subjects = $this->ebook->subjects;
        sort($subjects);
        foreach ($subjects as $subject)
            $metadata .= "<dc:subject>$subject</dc:subject>" . PHP_EOL;
        if ($this->ebook->cover !== "")
            $metadata .= "<meta name='cover' content='" . Girlfriend::$leaPrefixes["image"]
                . Girlfriend::comeToMe()->strToEpubIdentifier($this->ebook->cover) . "'/>" . PHP_EOL;
        $metadata .= "<meta name='generator' content='" . Girlfriend::comeToMe()->leaNamePlain . "'/>" . PHP_EOL;
        return $metadata;
    }

    /**
     * Builds manifest from identifiers.
     *
     * @return string
     */
    private function compileManifest(): string
    {
        $manifest = "";
        foreach (Girlfriend::comeToMe()->arrayPregKeys(
            pattern: "/^" . $this->idMarkers["text"] . "/",
            array: $this->identifiers
        ) as $key => $text)
            $manifest .= sprintf(
                "<item id='%s' href='Text/%s' media-type='application/xhtml+xml'%s/>%s",
                $text["epubIdentifier"], $text["epubFileName"],
                $key === $this->idMarkers["text"] . "cover.xhtml" ? " properties='svg'"
                    : ($key === $this->idMarkers["text"] . "nav.xhtml" ? " properties='nav'" : ""), PHP_EOL
            );
        foreach (Girlfriend::comeToMe()->arrayPregKeys(
            pattern: "/^" . $this->idMarkers["font"] . "/",
            array: $this->identifiers
        ) as $font)
            $manifest .= sprintf("<item id='%s' href='Fonts/%s' media-type='font/%s'/>%s",
                $font["epubIdentifier"], $font["epubFileName"],
                pathinfo($font["epubFileName"])['extension'], PHP_EOL);
        foreach (Girlfriend::comeToMe()->arrayPregKeys(
            pattern: "/^" . $this->idMarkers["stylesheet"] . "/",
            array: $this->identifiers
        ) as $stylesheet)
            $manifest .= sprintf("<item id='%s' href='Styles/%s' media-type='text/css'/>%s",
                $stylesheet["epubIdentifier"], $stylesheet["epubFileName"], PHP_EOL);
        foreach (Girlfriend::comeToMe()->arrayPregKeys(
            pattern: "/^" . $this->idMarkers["image"] . "/",
            array: $this->identifiers
        ) as $image)
            $manifest .= sprintf("<item id='%s' href='Images/%s' media-type='image/%s'/>%s",
                $image["epubIdentifier"], $image["epubFileName"],
                pathinfo($image["epubFileName"])['extension'] === "jpg" ? "jpeg"
                    : pathinfo($image["epubFileName"])['extension'], PHP_EOL);
        $manifest .= "<item id='ncx' href='toc.ncx' media-type='application/x-dtbncx+xml'/>" . PHP_EOL;
        return $manifest;
    }

    /**
     * Builds spine from identifiers.
     *
     * @return string
     */
    private function compileSpine(): string
    {
        $format = "<itemref idref='" . "%s" . "'/>%s";
        $identifiers = Girlfriend::comeToMe()->arrayPregKeys(
            pattern: "/^" . $this->idMarkers["text"] . "/",
            array: $this->identifiers
        );
        $spine = sprintf($format, $identifiers[$this->idMarkers["text"] . "cover.xhtml"]["epubIdentifier"], PHP_EOL);
        unset($identifiers[$this->idMarkers["text"] . "cover.xhtml"]);
        foreach ($identifiers as $key => $val)
            if ($key !== $this->idMarkers["text"] . "nav.xhtml")
                $spine .= sprintf($format, $val["epubIdentifier"], PHP_EOL);
        $spine .= sprintf($format, $identifiers[$this->idMarkers["text"] . "nav.xhtml"]["epubIdentifier"], PHP_EOL);
        return $spine;
    }

    private function compileGuide(): string
    {
        return "<reference type='toc' title='Table of Contents' href='Text/nav.xhtml#toc'/>" . PHP_EOL;
    }

    /**
     * Compiles the OPF structure for the ePub content.opf file.
     *
     * @return string
     */
    private function compileOpf(): string
    {
        return "<?xml version=\"1.0\" encoding=\"utf-8\"?>"
            . "<package version=\"3.0\" unique-identifier=\"isbn\" xmlns=\"http://www.idpf.org/2007/opf\">"
            . "<metadata xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:opf=\"http://www.idpf.org/2007/opf\">"
            . $this->compileMetadata() . "</metadata>"
            . "<manifest>" . $this->compileManifest() . "</manifest>"
            . "<spine toc='ncx'>" . $this->compileSpine() . "</spine>"
            . "<guide>" . $this->compileGuide() . "</guide>"
            . "</package>" . PHP_EOL;
    }

    /**
     * Generates the toc.ncx file from a template file, injecting the nav points.
     *
     * @return string
     * @throws Exception
     */
    private function generateTocNcx(): string
    {
        $format = "\n\t\t<navPoint id='%s'>\n\t\t\t<navLabel>\n\t\t\t\t<text>%s</text>\n\t\t\t</navLabel>"
            . "\n\t\t\t<content src='Text/%s' />\n\t\t</navPoint>";
        $toc = sprintf(
            $format,
            Girlfriend::$leaPrefixes["ncx"]
            . Girlfriend::comeToMe()->strToEpubIdentifier(string: "Cover"),
            "Cover",
            $this->identifiers[$this->idMarkers["text"] . "cover.xhtml"]["epubFileName"],
        );
        foreach ($this->ebook->texts as $text) {
            if (($text->title !== "Cover") && ($text->title !== "EPUB Navigation"))
                $toc .= sprintf(
                    $format,
                    Girlfriend::$leaPrefixes["ncx"]
                    . Girlfriend::comeToMe()->strToEpubIdentifier($text->title),
                    $text->title,
                    $this->identifiers[$this->idMarkers["text"] . $text->fileName]["epubFileName"],
                );
        }
        $toc .= sprintf(
                $format,
                Girlfriend::$leaPrefixes["ncx"]
                . Girlfriend::comeToMe()->strToEpubIdentifier(string: "Table of Contents"),
                "Table of Contents",
                $this->identifiers[$this->idMarkers["text"] . "nav.xhtml"]["epubFileName"],
            )
            . "\n\t";
        return str_replace(
            "#3#",
            $toc,
            str_replace(
                "#2#",
                $this->ebook->title,
                str_replace(
                    search: "#1#",
                    replace: $this->ebook->isbn->isbn,
                    subject: Girlfriend::comeToMe()->readFile(
                        filePath: Girlfriend::$pathPurpleRain . "tocncxtemplate.xhtml"
                    ),
                ),
            ),
        );
    }

    /**
     * Generates the production log for META-INF.
     *
     * @param array $errorLog
     * @param string $timeStamp
     * @param array $epubCheckCapture
     * @return string
     * @throws Exception
     */
    private function generateProductionLog(array $errorLog, string $timeStamp, array $epubCheckCapture): string
    {
        $productionLog = Girlfriend::comeToMe()->readFile(Girlfriend::$pathPurpleRain . "lea-logo-ascii.txt")
            . Girlfriend::comeToMe()->leaNamePlain . " Production Log." . PHP_EOL
            . "----------------------------------------------------------------" . PHP_EOL . PHP_EOL
            . "Production Date: $timeStamp" . PHP_EOL . PHP_EOL . PHP_EOL;
        foreach ($errorLog as $error)
            $productionLog .= "Severity:        " . strtoupper($error->flaw->name) . PHP_EOL
                . "Message:         "
                . preg_replace(pattern: Fancy::STRIP_ANSI_REGEX, replacement: '', subject: $error->message) . PHP_EOL
                . "Suggestion:      "
                . preg_replace(pattern: Fancy::STRIP_ANSI_REGEX, replacement: '', subject: $error->suggestion)
                . PHP_EOL . PHP_EOL . PHP_EOL;
        $productionLog .= "EPUBCheck Log." . PHP_EOL
            . "----------------------------------------------------------------" . PHP_EOL . PHP_EOL
            . "[ STDOUT ]" . PHP_EOL . PHP_EOL
            . ($epubCheckCapture["stdout"] ?? "NULL") . PHP_EOL . PHP_EOL
            . "[ STDERR ]" . PHP_EOL . PHP_EOL
            . ($epubCheckCapture["stderr"] ?? "NULL") . PHP_EOL . PHP_EOL
            . "[ RETURN ]" . PHP_EOL . PHP_EOL
            . ($epubCheckCapture["return"] ?? "NULL") . PHP_EOL . PHP_EOL . PHP_EOL;
        $productionLog .= "----------------------------------------------------------------" . PHP_EOL
            . "Excuse me, but is this really goodbye?" . PHP_EOL;
        return $productionLog;
    }

    /**
     * Now I can touch
     * Now I can feel
     *
     * @param array $errorLog
     * @return bool
     * @throws DOMException
     * @throws Exception
     */
    #[NoDiscard]
    public function conductor(array $errorLog): bool
    {
        $zip = new ZipArchive();
        $epubFilePath = Girlfriend::$pathEpubs
            . Girlfriend::comeToMe()->recall(name: "subfolder-epub");
        $epubFileName = $epubFilePath . $this->ebook->title . " - " . $this->ebook->publisher->imprint . ".epub";
        if (!is_dir($epubFilePath))
            mkdir($epubFilePath);
        $zip->open(filename: $epubFileName, flags: ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFile(filepath: Girlfriend::$pathPurpleRain . "mimetype", entryname: "mimetype");
        $zip->setCompressionName(name: 'mimetype', method: ZipArchive::CM_STORE);
        $zip->addFile(filepath: Girlfriend::$pathPurpleRain . "container.xml", entryname: "META-INF/container.xml");
        $zip->addFromString(name: "OEBPS/content.opf", content: $this->compileOpf());
        $zip->addFromString(name: "OEBPS/toc.ncx", content: $this->generateTocNcx());
        /**
         * Add any user-defined font files.
         */
        foreach ($this->ebook->fonts as $font)
            $zip->addFile(
                filepath: Girlfriend::$pathFonts . $font,
                entryname: "OEBPS/Fonts/" . $this->identifiers[$this->idMarkers["font"] . $font]["epubFileName"],
            );
        /**
         * Add any user-defined stylesheets.
         * We sanitze them to add included iamge files to the ePub,
         * and use normalized file names instead of the original ones.
         */
        foreach (Girlfriend::comeToMe()->sanitizeStylesheets($this->ebook) as $fileName => $content)
            $zip->addFromString(
                name: "OEBPS/Styles/" . $this->identifiers[$this->idMarkers["stylesheet"] . $fileName]["epubFileName"],
                content: $content
            );
        /**
         * Add all files defined through <lea:image> in both text and ebook config files.
         */
        set_error_handler(function ($errno, $errstr) {
            throw new RuntimeException($errstr, $errno);
        });
        foreach ($this->ebook->images as $image)
            $zip->addFromString(
                "OEBPS/Images/" . $this->identifiers[$this->idMarkers["image"] . $image->fileName]["epubFileName"],
                Girlfriend::comeToMe()->readFile(
                    str_starts_with($image->fileName, "/")
                        ? $image->fileName
                        : Girlfriend::$pathImages . $image->folder . $image->fileName
                ),
            );
        restore_error_handler();
        /**
         * Also add the cover image file.
         */
        $zip->addFile(
            filepath: Girlfriend::$pathImages . Girlfriend::comeToMe()->recall(name: "subfolder-images") . $this->ebook->cover,
            entryname: "OEBPS/Images/" . $this->identifiers[$this->idMarkers["image"] . $this->ebook->cover]["epubFileName"]
        );
        /**
         * Add all text files, including the cover xhtml file and the mandatory nav.xhtml EPUB Navigation.
         */
        foreach ($this->ebook->texts as $text) {
            $zip->addFromString(
                name: "OEBPS/Text/" . $this->identifiers[$this->idMarkers["text"] . $text->fileName]["epubFileName"],
                content: XMLetsGoCrazy::stripLeaDom(XMLetsGoCrazy::reWrapDom($text->dom, $text->title, $this->ebook->stylesheets))->saveXML() ?: ""
            );
        }
        $zip->close();
        echo "Successfully produced the ePub file: $epubFileName" . PHP_EOL;
        $timeStamp = new Date(modified: "now")->modified;
        $zip->open($epubFileName);
        $zip->addFromString(name: "META-INF/lea-log.txt", content: $this->generateProductionLog(
            errorLog: $errorLog,
            timeStamp: $timeStamp,
            epubCheckCapture: $result ?? []
        ));
        return true;
    }
}
