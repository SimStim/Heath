<?php

declare(strict_types=1);

namespace Lea\Domain;

use DOMDocument;
use DOMElement;
use DOMException;
use DOMImplementation;
use DOMXPath;
use Exception;
use NoDiscard;
use ReflectionException;
use Lea\Adore\AlphabetSt;
use Lea\Adore\Girlfriend;

/**
 * XMLetsGoCrazy class for static domain helpers
 *
 * We're all excited, but we don't know why.
 */
final class XMLetsGoCrazy
{
    private(set) static string $leaNamespace = "https://logophilia.eu/lea/2026/xhtml";
    private(set) static string $rootElement = "xmletsgocrazy";

    public static function wrapInLeaNamespace($fragments): string
    {
        return "<" . self::$rootElement
            . " xmlns:lea='" . self::$leaNamespace
            . "'>$fragments</" . self::$rootElement . ">";
    }

    /**
     * Takes a DOMDocument wrapped in the lea namespace
     * and returns a DOMXPath object to inquire against
     *
     * @param DOMDocument $dom
     * @return DOMXPath
     */
    #[NoDiscard]
    public static function createXPath(DOMDocument $dom): DOMXPath
    {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace(prefix: 'lea', namespace: self::$leaNamespace);
        return $xpath;
    }

    /**
     * Takes an xhtml string and returns a DOMDocument object
     *
     * @param string $xhtml
     * @return DOMDocument
     */
    #[NoDiscard]
    public static function createDOM(string $xhtml): DOMDocument
    {
        $dom = new DOMDocument(version: '1.0', encoding: 'UTF-8');
        $dom->loadXML($xhtml, options: LIBXML_NONET);
        return $dom;
    }

    /**
     * Takes a file of XML fragments, including lea namespace directives,
     * and returns a DOMDocument object
     *
     * @param string $fragments
     * @return DOMDocument
     */
    #[NoDiscard]
    public static function createDOMFromFragments(string $fragments): DOMDocument
    {
        return self::createDOM(self::wrapInLeaNamespace($fragments));
    }

    /**
     * Checks a DOMXPath object and returns FALSE when the DOMDocument is empty
     * This would indicate:
     * - DOMDocument was not well-formed, or
     * - There has been a read error on the file (or file not found), or
     * - The XML fragments file did exist but was actually devoid of any useful content
     *
     * @param DOMXPath $xpath
     * @return bool
     */
    #[NoDiscard]
    public static function isWellFormed(DOMXPath $xpath): bool
    {
        return $xpath->document->documentElement !== null;
    }

    /**
     * Extract the title from an XPath object
     * - <lea:title>The Gold Experience</lea:title>
     *
     * @param DOMXPath $xpath
     * @return string
     */
    #[NoDiscard]
    public static function extractTitle(DOMXPath $xpath): string
    {
        return trim($xpath->evaluate(expression: "string(/" . self::$rootElement . "/lea:title)"));
    }

    /**
     * Extract the description from an XPath object
     * - <lea:description>The Pitch Science Fiction is a quarterly publication of science-fiction stories
     *                    by various authors, covering diverse themes.</lea:description>
     *
     * @param DOMXPath $xpath
     * @return string
     */
    #[NoDiscard]
    public static function extractDescription(DOMXPath $xpath): string
    {
        return trim($xpath->evaluate(expression: "string(/" . self::$rootElement . "/lea:description)"));
    }

    /**
     * Validates all existing <author> tags in a passed XPath object
     *
     * @param DOMXPath $xpath
     * @return bool
     */
    public static function validateAuthors(DOMXPath $xpath): bool
    {
        $nodes = $xpath->query(expression: "/" . self::$rootElement . "/lea:author");
        return (count($nodes) >= 1);
    }

    /**
     * Extract the author(s) from an XPath object
     * - <lea:author>Robert Sheckley</lea:author>
     * - <lea:author file-as="Nelson, Prince Rogers [The Unpronounceable Symbol]">The Unpronounceable Symbol</lea:author>
     *
     * @param DOMXPath $xpath
     * @return array
     */
    #[NoDiscard]
    public static function extractAuthors(DOMXPath $xpath): array
    {
        $nodes = $xpath->query(expression: "/" . self::$rootElement . "/lea:author");
        $authors = [];
        foreach ($nodes as $node) {
            $fileAs = $node->hasAttribute('file-as')
                ? $node->getAttribute('file-as')
                : "";
            $name = trim($node->textContent);
            $authors[] = new Author($name, $fileAs);
        }
        return $authors;
    }

    /**
     * Extract the rights from an XPath object
     * - <lea:rights>English translation of M2 (C) 2025 by Eduard Pech.
     *               Published by permission of Daria Skrinitsa.
     *               All other stories are in the public domain in the USA.
     *               All content not marked as public domain (C) 2025 by Eduard Pech.
     *               Complete edition (C) 2025 by Logophilia.
     *               Logophilia is an imprint of Logophilia OÜ.</lea:rights>
     *
     * @param DOMXPath $xpath
     * @return string
     */
    #[NoDiscard]
    public static function extractRights(DOMXPath $xpath): string
    {
        $nodes = $xpath->query(expression: "/" . self::$rootElement . "/lea:rights");
        if ($nodes->length === 0)
            return "";
        $rightsNode = $nodes->item(index: 0);
        $dom = $rightsNode->ownerDocument;
        $innerXml = '';
        foreach ($rightsNode->childNodes as $child)
            $innerXml .= $dom->saveXML($child);
        return trim($innerXml);
    }

    /**
     * Extract the language from an XPath object
     * - <lea:language>en</lea:language>
     *
     * @param DOMXPath $xpath
     * @return string
     */
    #[NoDiscard]
    public static function extractLanguage(DOMXPath $xpath): string
    {
        return trim($xpath->evaluate(expression: "string(/" . self::$rootElement . "/lea:language)"));
    }

    /**
     * Extract the dates from an XPath object
     * - <lea:date>
     *     <lea:created>The Unpronounceable Symbol</lea:created>
     *     <lea:modified>Nelson, Prince Rogers [The Unpronounceable Symbol]</lea:modified>
     *     <lea:issued>Nelson, Prince Rogers [The Unpronounceable Symbol]</lea:issued>
     *   </lea:date>
     *
     * @param DOMXPath $xpath
     * @return Date
     */
    #[NoDiscard]
    public static function extractDate(DOMXPath $xpath): Date
    {
        $nodes = $xpath->query(expression: "/" . self::$rootElement . "/lea:date");
        if ($nodes === false || $nodes->length !== 1) return new Date ();
        $node = $nodes->item(index: 0);
        $issued = trim($node->textContent);
        return new Date (
            created: $node->hasAttribute(qualifiedName: 'created')
                ? trim($node->getAttribute(qualifiedName: 'created'))
                : $issued,
            modified: "now",
            issued: $issued
        );
    }

    /**
     * Extract the publisher from an XPath object
     * - <lea:publisher>
     *     <lea:imprint>Logophilia</lea:imprint>
     *     <lea:contact>origins@logophilia.eu</lea:contact>
     *   </lea:publisher>
     * This information is not mandatory, according to specifications,
     * but I choose to print annoying messages until the tags are present.
     * All data is free-form text and not checked for formatting.
     * For example, contact could be a telephone number,
     * or just the string, "go fuck yourself, Lea."
     *
     * @param DOMXPath $xpath
     * @return Publisher
     */
    #[NoDiscard]
    public static function extractPublisher(DOMXPath $xpath): Publisher
    {
        $nodes = $xpath->query(expression: "/" . self::$rootElement . "/lea:publisher");
        if ($nodes === false || $nodes->length !== 1)
            return new Publisher();
        $node = $nodes->item(index: 0);
        if (!$node->hasAttribute(qualifiedName: "contact"))
            return new Publisher();
        return new Publisher(
            imprint: trim($node->textContent),
            contact: trim($node->getAttribute(qualifiedName: "contact"))
        );
    }

    /**
     * Validates all existing <contributor> tags in a passed XPath object
     *
     * @param Ebook $ebook
     * @return bool
     */
    public static function validateContributors(Ebook $ebook): bool
    {
        return !empty($ebook->contributors);
    }

    /**
     * Extract the contributor(s) from an XPath object
     * - <lea:contributor roles="edt trl bkp bkd tyg mrk pfr cov ill art blw">Eduard Pech</lea:contributor>
     *
     * @param DOMXPath $xpath
     * @return array
     */
    #[NoDiscard]
    public static function extractContributors(DOMXPath $xpath): array
    {
        $nodes = $xpath->query(expression: "/" . self::$rootElement . "/lea:contributor");
        if ($nodes === false || $nodes->length !== 1) return [];
        $contributors = [];
        foreach ($nodes as $node) {
            $roles = array_intersect(
                preg_split(
                    pattern: "/\s+/",
                    subject: $node->hasAttribute('roles') ? strtolower(trim($node->getAttribute('roles'))) : ""
                ), Contributor::$permittedRoles // use only permitted roles
            );
            if (empty($roles)) continue;        // at least one role is required
            $contributors[] = new Contributor(name: trim($node->textContent), roles: $roles);
        }
        return $contributors;
    }

    /**
     * Extract the optional blurb from an XPath object
     * - <lea:blurb>
     *     According to the NPG operator, the most beautiful girl in the world said, "I hate u."
     *     Thus commenced operation P. Control.
     *   </lea:blurb>
     *
     * @param DOMXPath $xpath
     * @return string
     */
    #[NoDiscard]
    public static function extractBlurb(DOMXPath $xpath): string
    {
        $nodes = $xpath->query(expression: "/" . self::$rootElement . "/lea:blurb");
        if ($nodes->length === 0)
            return "";
        $blurbNode = $nodes->item(index: 0);
        $dom = $blurbNode->ownerDocument;
        $innerXml = '';
        foreach ($blurbNode->childNodes as $child)
            $innerXml .= $dom->saveXML($child);
        return trim($innerXml);
    }

    /**
     * Extract the cover from an XPath object
     * - <lea:cover>2025Q4-cover-inside.jpg</lea:cover>
     *
     * @param DOMXPath $xpath
     * @return string
     */
    #[NoDiscard]
    public static function extractCover(DOMXPath $xpath): string
    {
        return trim($xpath->evaluate(expression: "string(/" . self::$rootElement . "/lea:cover)"));
    }

    /**
     * Extract the image file names from an XPath object
     * - <lea:image>Harry-Harrison-FM-Logophilia-512.jpg</lea:image>
     * - <lea:image caption="Cover with QR code">Hubris-cover-512-qr.jpg</lea:image>
     *
     * @param Ebook|Text $domObject
     * @return array
     * @throws Exception
     */
    #[NoDiscard]
    public static function extractImages(Ebook|Text $domObject): array
    {
        $nodes = $domObject->xpath->query(expression: "//lea:image");
        $images = [];
        foreach ($nodes as $node) {
            if (!$node->hasAttribute("source")) {
                Girlfriend::comeToMe()->makeDoveCry($domObject, "imageSourceUndefined", $domObject->fileName);
                continue;
            }
            $fileName = trim(string: $node->getAttribute(qualifiedName: "source"));
            $folder = $node->hasAttribute("folder")
                ? trim(string: $node->getAttribute(qualifiedName: "folder"), characters: "/ ") . "/"
                : Girlfriend::comeToMe()->recall(name: "subfolder-images");
            $captionNodes = $domObject->xpath->query("lea:caption", $node);
            if ($captionNodes->length === 0)
                $caption = Girlfriend::comeToMe()->recall(name: "defaultcaption");
            else {
                $captionNode = $captionNodes->item(0);
                if ($captionNodes->length > 1) {
                    Girlfriend::comeToMe()->makeDoveCry(
                        $domObject, "multipleCaptions",
                        $domObject->fileName, trim($node->textContent)
                    );
                }
                $dom = $captionNode->ownerDocument;
                $caption = '';
                foreach ($captionNode->childNodes as $child)
                    $caption .= $dom->saveXML($child);
            }
            $images[] = new Image(fileName: $fileName, folder: $folder, caption: $caption);
        }
        return $images;
    }

    /**
     * Extract the optional subjects from an XPath object
     * - <lea:subject>Diplomats -- Fiction</lea:subject>
     * - <lea:subject>Extraterrestrial beings -- Fiction</lea:subject>
     *
     * @param DOMXPath $xpath
     * @return array
     */
    #[NoDiscard]
    public static function extractSubjects(DOMXPath $xpath): array
    {
        $nodes = $xpath->query(expression: "/" . self::$rootElement . "/lea:subject");
        $subjects = [];
        foreach ($nodes as $node)
            $subjects[] = trim($node->textContent);
        return $subjects;
    }

    /**
     * Extract the stylesheet file names from an XPath object
     * - <lea:stylesheet>lphi-originals/stylesheet.css</lea:stylesheet>
     * - <lea:stylesheet>lphi-originals/fonts.css</lea:stylesheet>
     *
     * @param DOMXPath $xpath
     * @return array
     */
    #[NoDiscard]
    public static function extractStylesheets(DOMXPath $xpath): array
    {
        $nodes = $xpath->query(expression: "/" . self::$rootElement . "/lea:stylesheet");
        $stylesheets = [];
        foreach ($nodes as $node)
            $stylesheets[] = trim($node->textContent);
        return $stylesheets;
    }

    /**
     * Extract the font file names from an XPath object
     * - <lea:font>Comfortaa-Bold.ttf</lea:font>
     * - <lea:font>TeXGyreHeros-Regular.otf</lea:font>
     *
     * @param DOMXPath $xpath
     * @return array
     */
    #[NoDiscard]
    public static function extractFonts(DOMXPath $xpath): array
    {
        $nodes = $xpath->query(expression: "/" . self::$rootElement . "/lea:font");
        $fonts = [];
        foreach ($nodes as $node)
            $fonts[] = trim($node->textContent);
        return $fonts;
    }

    /**
     * Extract the ISBN from an XPath object
     * - ISBN-13 only. Not aware of any other formats going around for ebooks.
     * - <lea:isbn>987-1234567890</lea:isbn>
     *
     * @param DOMXPath $xpath
     * @return ISBN
     */
    #[NoDiscard]
    public static function extractISBN(DOMXPath $xpath): ISBN
    {
        $nodes = $xpath->query(expression: "/" . self::$rootElement . "/lea:isbn");
        $isbns = [];
        foreach ($nodes as $node) {
            $isbn = new ISBN($node->textContent);
            if ($isbn->isValid) $isbns[] = $isbn;
        }
        return (!empty($isbns) ? $isbns[0] : new ISBN(isbn: "***INVALID***"));
    }

    /**
     * Extract the optional collection data from an XPath object
     * - <lea:collection>
     *     <lea:title>Logophilia</lea:title>
     *     <lea:type>origins@logophilia.eu</lea:type>
     *     <lea:position>origins@logophilia.eu</lea:position>
     *     <lea:issn>origins@logophilia.eu</lea:issn>
     *   </lea:collection>
     * This information is not mandatory, as not every book is part of a collection.
     * At the time of writing, only the collection type "series" is supported.
     * A "series" requires an ISSN.
     *
     * @param DOMXPath $xpath
     * @return Collection
     */
    #[NoDiscard]
    public static function extractCollection(DOMXPath $xpath): Collection
    {
        $nodes = $xpath->query(expression: "/" . self::$rootElement . "/lea:collection");
        if ($nodes === false || $nodes->length !== 1)
            return new Collection();
        $node = $nodes->item(index: 0);
        if (!$node->hasAttribute(qualifiedName: "type")
            || !$node->hasAttribute(qualifiedName: "position")
            || !$node->hasAttribute(qualifiedName: "issn"))
            return new Collection();
        if ($node->getAttribute(qualifiedName: "type") !== "series")
            return new Collection();
        return new Collection(
            title: trim($node->textContent),
            type: $node->getAttribute("type"),
            position: $node->getAttribute("position"),
            issn: $node->getAttribute("issn")
        );
    }

    /**
     * Extract the texts from an XPath object
     * - File names are always relative to REPO
     * - Subfolder should be specified with the subfolder tag
     * - <lea:text>AboutTheAuthors.xhtml</lea:text>
     *
     * @param DOMXPath $xpath
     * @return array
     */
    #[NoDiscard]
    public static function extractTexts(DOMXPath $xpath): array
    {
        $nodes = $xpath->query(expression: "/" . self::$rootElement . "/lea:text");
        $texts = [];
        foreach ($nodes as $node)
            $texts[] = new Text(trim($node->textContent));
        return $texts;
    }

    /**
     * Extract all target tags from a passed XPath object.
     * - <lea:target>Clifford D. Simak</lea:target>
     * - <lea:target>Table of Contents</lea:target>
     * Targets are not case-sensitive.
     *
     * @param DOMXPath $xpath
     * @param string $targetFileName
     * @return array
     */
    #[NoDiscard]
    public static function extractTargets(DOMXPath $xpath, string $targetFileName): array
    {
        $nodes = $xpath->query(expression: "//lea:target");
        $targets = [];
        foreach ($nodes as $node)
            $targets[] = new Target(
                name: $node->textContent,
                identifier: Girlfriend::$leaPrefixes["target"]
                . Girlfriend::comeToMe()->strToEpubIdentifier($node->textContent),
                targetFileName: $targetFileName
            );
        return $targets;
    }

    /**
     * Extract all link tags from a passed XPath object.
     * - <lea:link>Clifford D. Simak</lea:link>
     * - <lea:link>Table of Contents</lea:link>
     * You might have guessed it: links are not case-sensitive.
     *
     * @param DOMXPath $xpath
     * @return array
     */
    #[NoDiscard]
    public static function extractLinks(DOMXPath $xpath): array
    {
        $nodes = $xpath->query(expression: "//lea:link");
        $links = [];
        foreach ($nodes as $node)
            $links[] = strtolower(string: $node->hasAttribute("to")
                ? trim($node->getAttribute("to"))
                : trim($node->textContent));
        return $links;
    }

    /**
     * Replace <lea:link> tags with html.
     * - <lea:link>Clifford D. Simak</lea:link>
     * - This will find the file the target was declared in and produce an html link to it.
     *
     * @param Text $text
     * @param array $targetData
     * @return void
     * @throws DOMException
     * @throws Exception
     */
    public static function replaceLeaLinkTags(Text $text, array $targetData): void
    {
        $nodes = $text->xpath->query(expression: "//lea:link");
        foreach ($nodes as $node) {
            $linkTarget = trim(string: $node->hasAttribute('to')
                ? $node->getAttribute('to')
                : $node->textContent
            );
            $linkTargetIdentifier = Girlfriend::$leaPrefixes["target"]
                . Girlfriend::comeToMe()->strToEpubIdentifier($linkTarget);
            if (filter_var($linkTarget, filter: FILTER_VALIDATE_URL) === false) {
                if (!isset($targetData[$linkTargetIdentifier])) {
                    Girlfriend::comeToMe()->makeDoveCry($text, "linkTargetUndefined",
                        Girlfriend::$pathEbooks . $text->fileName, $linkTarget);
                    continue;
                }
            }
            $linkHref = (filter_var($linkTarget, filter: FILTER_VALIDATE_URL) !== false)
                ? $linkTarget
                : $targetData[$linkTargetIdentifier]["targetFileName"]
                . "#" . $targetData[$linkTargetIdentifier]["identifier"];
            $fragment = $text->dom->createDocumentFragment();
            foreach ($node->childNodes as $child)
                $fragment->appendChild($child->cloneNode(true));
            $aTag = $text->dom->createElement(localName: "a");
            $aTag->setAttribute(qualifiedName: "href", value: $linkHref);
            $aTag->appendChild($fragment);
            $node->parentNode->replaceChild($aTag, $node);
        }
    }

    /**
     * Replace <lea:target> tags with html.
     * - <lea:target>Clifford D. Simak</lea:target> => <a id="lea-tgt-clifford-d--simak"/>
     * - <lea:target>John Campbell, Jr.</lea:target> => <a id="lea-tgt-john-w--campbell--jr-"/>
     *
     * @param Text $text
     * @return void
     */
    public static function replaceLeaTargetTags(Text $text): void
    {
        $nodes = $text->xpath->query(expression: "//lea:target");
        foreach ($nodes as $node) {
            $replacement = "<a id='" . Girlfriend::$leaPrefixes["target"]
                . Girlfriend::comeToMe()->strToEpubIdentifier($node->textContent) . "'></a>";
            $fragment = $text->dom->createDocumentFragment();
            $fragment->appendXML($replacement);
            $node->parentNode->replaceChild($fragment, $node);
        }
    }

    /**
     * Replace <lea:chapter> tags with html.
     * - <lea:chapter content-top="CHAPTER ONE" class-top="heading break-before"
     *                class-bottom="hr heading">The Pyramid of Bottles</lea:chapter>
     * =>
     * <h2 title="CHAPTER ONE-The Pyramid of Bottles"
     *     class="heading break-before">CHAPTER ONE</h2>
     * <h3 class="hr heading">The Pyramid of Bottles</h3>
     *
     * @param Text $text
     * @return void
     */
    public static function replaceLeaChapterTags(Text $text): void
    {
        $nodes = $text->xpath->query(expression: "//lea:chapter");
        foreach ($nodes as $node) {
            $chapterTitle = trim($node->textContent);
            $title = $node->hasAttribute("title")
                ? $node->getAttribute("title")
                : $text->title . "—" . $chapterTitle;
            $contentTop = $node->hasAttribute("content-top")
                ? $node->getAttribute("content-top")
                : "";
            $classTop = $node->hasAttribute("class-top")
                ? $node->getAttribute("class-top")
                : "";
            $classBottom = $node->hasAttribute("class-bottom")
                ? $node->getAttribute("class-bottom")
                : "";
            $replacement = "<h2 title='$title' class='$classTop'>$contentTop</h2>"
                . "<h3 class='$classBottom'>$chapterTitle</h3>";
            self::replaceNodeWithStringContent($node, $replacement);
        }
    }

    /**
     * Replace <lea:section> tags with html.
     * - <lea:section class="heading">The Pyramid of Bottles</lea:section>
     * =>
     * <h2 title="The Call from Beyond - The Pyramid of Bottles"
     *     class="heading">The Pyramid of Bottles</h2>
     *
     * @param Text $text
     * @return void
     */
    public static function replaceLeaSectionTags(Text $text): void
    {
        $nodes = $text->xpath->query(expression: "//lea:section");
        foreach ($nodes as $node) {
            $sectionTitle = trim($node->textContent);
            $title = $node->hasAttribute("title")
                ? $node->getAttribute("title")
                : $text->title . "—" . $sectionTitle;
            $class = $node->hasAttribute("class")
                ? $node->getAttribute("class")
                : "";
            $replacement = "<h2 title='$title' class='$class'>$sectionTitle</h2>";
            self::replaceNodeWithStringContent($node, $replacement);
        }
    }

    /**
     * Replace <lea:script> tags with xhtml content by executing the names script.
     * - <lea:script>tableOfContents</lea:script>
     * Check AlphabetSt or Lea documentation for the list of available scripts.
     *
     * @throws ReflectionException|Exception
     */
    public static function executeLeaScriptTags(Ebook|Text $text, AlphabetSt $scripts, Ebook $ebook): void
    {
        $nodes = $text->xpath->query(expression: "//lea:script");
        foreach ($nodes as $node) {
            $scriptName = strtolower(trim($node->textContent));
            if (!array_key_exists($scriptName, $scripts->lut)) {
                Girlfriend::comeToMe()->makeDoveCry($text, "scriptUndefined", $scriptName,
                    Girlfriend::$pathText . Girlfriend::comeToMe()->recall(name: "subfolder-text") . $text->fileName);
                continue;
            }
            $scripts->{$scripts->lut[$scriptName]}($node, $ebook);
        }
    }

    /**
     * Extract the subfolder names from an XPath object.
     * - <lea:subfolder>tpsf-8</lea:subfolder>
     *
     * @param Ebook $ebook
     * @return void
     * @throws Exception
     */
    public static function extractSubFolder(Ebook $ebook): void
    {
        $nodes = $ebook->xpath->query(expression: "/" . self::$rootElement . "/lea:subfolder");
        foreach ($nodes as $node) {
            $subfolder = trim(string: $node->textContent, characters: "/ ") . "/";
            if (!$node->hasAttribute('tag')) {
                Girlfriend::comeToMe()->remember(name: "subfolder-epub", data: $subfolder);
                Girlfriend::comeToMe()->remember(name: "subfolder-text", data: $subfolder);
                Girlfriend::comeToMe()->remember(name: "subfolder-images", data: $subfolder);
                continue;
            }
            $attr = $node->getAttribute('tag');
            if (!in_array($attr, ["epub", "text", "images"])) {
                Girlfriend::comeToMe()->makeDoveCry($ebook, "subfolderTagUndefined", $attr,
                    Girlfriend::$pathEbooks . $ebook->fileName);
                continue;
            }
            Girlfriend::comeToMe()->remember(name: "subfolder-$attr", data: $subfolder);
        }
    }

    /**
     * Extract the default caption from an XPath object.
     * - <lea:defaultcaption>illustration is in the public domain</lea:defaultcaption>
     *
     * @param DOMXPath $xpath
     * @return void
     */
    public static function extractOptions(DOMXPath $xpath): void
    {
        $nodes = $xpath->query(expression: "//lea:option");
        foreach ($nodes as $node) {
            $option = trim($node->textContent);
            $value = $node->hasAttribute("value")
                ? $node->getAttribute("value")
                : "";
            if ($value !== "")
                Girlfriend::comeToMe()->remember(name: $option, data: $value);
        }
    }

    /**
     * Replace <lea:image> tags with xhtml template.
     *
     * @param Text $text
     * @param array $images
     * @return void
     */
    public static function replaceLeaImageTags(Text $text, array $images): void
    {
        $indexedImages = [];
        foreach ($images as $image)
            $indexedImages[$image->fileName] = $image;
        $nodes = $text->xpath->query(expression: "//lea:image");
        foreach ($nodes as $node) {
            $fileName = trim(string: $node->getAttribute(qualifiedName: "source"));
            if (!isset($indexedImages[$fileName]))  // in case of "include-images" = "no"
                continue;
            $caption = $indexedImages[$fileName]->caption;
            $alt = preg_replace(
                pattern: '/\s+/',
                replacement: ' ',
                subject: strip_tags(preg_replace(
                    pattern: '/<br\s*\/?>/i',
                    replacement: ' ',
                    subject: $caption))
            );
            $replacement = Girlfriend::comeToMe()->recall(name: "include-images") === "no"
                ? ""
                : "<figure>" . "<img src='../Images/"
                . Girlfriend::comeToMe()->strToEpubImageFileName($fileName) . "'"
                . " alt='$alt'/>" . "<figcaption>$caption</figcaption></figure>";
            $fragment = $text->dom->createDocumentFragment();
            $fragment->appendXML($replacement);
            $node->parentNode->replaceChild($fragment, $node);
        }
    }

    /**
     * Replace <lea:block> tags with xhtml content.
     *
     * @param Text $text
     * @return void
     * @throws Exception
     */
    public static function replaceLeaBlockTags(Text $text): void
    {
        $nodes = $text->xpath->query(expression: "//lea:block");
        foreach ($nodes as $node) {
            $blockFileName = Girlfriend::$pathBlocks
                . ($node->hasAttribute("folder")
                    ? trim(string: $node->getAttribute(qualifiedName: "folder"), characters: "/ ") . "/"
                    : "")
                . trim($node->textContent);
            $replacement = Girlfriend::comeToMe()->readFile($blockFileName);
            if ($replacement === "") {
                Girlfriend::comeToMe()->makeDoveCry($text, "blockReadError", $blockFileName, $text->fileName);
                continue;
            }
            $replacementDom = new DOMDocument(version: '1.0', encoding: 'UTF-8');
            $replacementDom->loadXML(self::wrapInLeaNamespace($replacement), options: LIBXML_NONET);
            foreach ($replacementDom->documentElement->childNodes as $child) {
                $imported = $text->dom->importNode($child, deep: true);
                $node->parentNode->insertBefore($imported, $node);
            }
            $node->parentNode->removeChild($node);
        }
    }

    /**
     * Make-up
     * Make-up
     * Pink, blue
     * Purple, I wanna make it good for you
     *
     * Rewraps a passed DOMDocument into a standard ePub DOM document.
     *
     * @param DOMDocument $domDocument
     * @param string $title
     * @param array $stylesheets
     * @return DOMDocument
     * @throws DOMException
     */
    public static function reWrapDom(
        DOMDocument $domDocument,
        string      $title = "TITLE",
        array       $stylesheets = []): DOMDocument
    {
        if (!str_contains($domDocument->saveXML(), XMLetsGoCrazy::$leaNamespace))
            return $domDocument;
        $impl = new DOMImplementation();
        $doctype = $impl->createDocumentType(qualifiedName: 'html'); // <!DOCTYPE html>
        $dom = $impl->createDocument(namespace: 'http://www.w3.org/1999/xhtml', qualifiedName: 'html', doctype: $doctype);
        $dom->encoding = 'UTF-8';
        $dom->formatOutput = false;
        $html = $dom->documentElement;
        $html->setAttributeNS(namespace: "http://www.w3.org/2000/xmlns/", qualifiedName: "xmlns:epub", value: "http://www.idpf.org/2007/ops");
        $html->setAttribute(qualifiedName: "lang", value: "en-GB");
        $html->setAttributeNS(namespace: "http://www.w3.org/XML/1998/namespace", qualifiedName: "xml:lang", value: "en-GB");
        $head = $dom->createElement(localName: "head");
        $html->appendChild($head);
        $generator = $dom->createElement(localName: "meta");
        $generator->setAttribute(qualifiedName: "name", value: "generator");
        $generator->setAttribute(qualifiedName: "content", value: Girlfriend::comeToMe()->leaNameShort);
        $head->appendChild($generator);
        $titleTag = $dom->createElement(localName: "title", value: $title);
        $head->appendChild($titleTag);
        foreach ($stylesheets as $stylesheet) {
            $stylesheetTag = $dom->createElement('link');
            $stylesheetTag->setAttribute(qualifiedName: 'rel', value: "stylesheet");
            $stylesheetTag->setAttribute(qualifiedName: 'type', value: "text/css");
            $stylesheetTag->setAttribute(qualifiedName: 'href', value: "../Styles/$stylesheet");
            $head->appendChild($stylesheetTag);
        }
        $html->appendChild($head);
        $body = $dom->createElement('body');
        $body->setAttributeNS(namespace: 'http://www.idpf.org/2007/ops', qualifiedName: 'epub:type', value: 'bodymatter');
        $html->appendChild($body);
        $fragmentsRoot = $domDocument->documentElement;
        foreach ($fragmentsRoot->childNodes as $node)
            $body->appendChild($dom->importNode($node, deep: true));
        return $dom;
    }

    /**
     * Strip down, strip down
     * Elephants and flowers
     * Is everybody ready? Here we go
     *
     * Strips all Lea tags from a passed DOMDocument.
     *
     * @param DOMDocument $leaDom
     * @return DOMDocument
     * @throws DOMException
     */
    public static function stripLeaDom(DOMDocument $leaDom): DOMDocument
    {
        $xpath = self::createXPath($leaDom);
        $nodes = $xpath->query(expression: "//lea:*");
        foreach ($nodes as $node)
            $node->parentNode->removeChild($node);
        return $leaDom;
    }

    /**
     * Replaces a DOMNode with the nodes generated from the provided string content.
     *
     * @param DOMElement $node The DOM node to be replaced.
     * @param string $string The string content to generate replacement nodes.
     * @return void
     */
    public static function replaceNodeWithStringContent(DOMElement $node, string $string): void
    {
        $newDom = new DOMDocument(version: '1.0', encoding: 'UTF-8');
        $newDom->loadXML(XMLetsGoCrazy::wrapInLeaNamespace($string), options: LIBXML_NONET);
        foreach ($newDom->documentElement->childNodes as $child) {
            $nodeDom = $node->ownerDocument;
            $imported = $nodeDom->importNode($child, deep: true);
            $node->parentNode->insertBefore($imported, $node);
        }
        $node->parentNode->removeChild($node);
    }
}
