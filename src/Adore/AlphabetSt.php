<?php

declare(strict_types=1);

namespace Lea\Adore;

use DOMElement;
use Exception;
use Lea\Domain\Ebook;
use Lea\Domain\Image;
use Lea\Domain\XMLetsGoCrazy;

/**
 * We're going down, down, down
 * If that's the only way
 * To make this cruel, cruel world
 * Hear what we've got to say
 * Put the right letters together
 * And make a better day
 */
class AlphabetSt
{
    public array $lut = [
        "toc" => "tableOfContents",
        "table of contents" => "tableOfContents",
        "list content" => "tableOfContents",
        "toc plain" => "tableOfContentsPlain",
        "list content plain" => "tableOfContentsPlain",
        "table of contents plain" => "tableOfContentsPlain",
        "colophon" => "listRights",
        "list rights" => "listRights",
        "text rights" => "listRights",
        "list text rights" => "listRights",
        "blurbs" => "listBlurbs",
        "list blurbs" => "listBlurbs",
        "text blurbs" => "listBlurbs",
        "linked image" => "linkedImage",
        "image" => "lockedImage",
        "locked image" => "lockedImage",
        "list authors" => "listAuthors",
        "authors" => "listAuthors",
        "text authors" => "listAuthors",
    ];

    /**
     * Generates a table of contents and inserts it into the DOM structure.
     *
     * @param DOMElement $node The DOM node where the table of contents will be inserted. This node will be replaced.
     * @param Ebook $ebook The ebook object containing the texts from which the table of contents will be generated.
     * @return void
     */
    public function tableOfContents(DOMElement $node, Ebook $ebook): void
    {
        $toc = "<ol>" . PHP_EOL;
        foreach ($ebook->texts as $text)
            $toc .= "<li><lea:link>" . $text->title . "</lea:link></li>" . PHP_EOL;
        $toc .= "</ol>" . PHP_EOL;
        XMLetsGoCrazy::replaceNodeWithStringContent(node: $node, string: $toc);
    }

    /**
     * Generates a plain-text table of contents and inserts it into the DOM structure.
     *
     * @param DOMElement $node
     * @param Ebook $ebook
     * @return void
     */
    public function tableOfContentsPlain(DOMElement $node, Ebook $ebook): void
    {
        $pattern = $node->hasAttribute(qualifiedName: "skip")
            ? "/" . $node->getAttribute(qualifiedName: "skip") . "/i"
            : "";
        $titles = [];
        foreach ($ebook->texts as $text)
            if ($pattern === "" || preg_match($pattern, $text->title) === 0)
                $titles[$text->title] = $text->title;
        ksort(array: $titles);
        $toc = PHP_EOL;
        foreach ($titles as $title)
            $toc .= $title . ", " . PHP_EOL;
        $toc = trim($toc, ", \n") . ".";
        XMLetsGoCrazy::replaceNodeWithStringContent(node: $node, string: $toc);
    }

    /**
     * Iterates over all text files and inserts their rights into the DOM structure.
     *
     * @param DOMElement $node The DOM node where the colophon will be inserted. This node will be replaced.
     * @param Ebook $ebook The ebook object containing the texts with rights information for generating the colophon.
     * @return void
     */
    public function listRights(DOMElement $node, Ebook $ebook): void
    {
        $rights = "";
        foreach ($ebook->texts as $text)
            if (!empty($text->rights))
                $rights .= $text->rights . PHP_EOL;
        XMLetsGoCrazy::replaceNodeWithStringContent(node: $node, string: $rights);
    }

    /**
     * Iterates over all text files and inserts their blurbs into the DOM structure.
     *
     * @param DOMElement $node
     * @param Ebook $ebook
     * @return void
     */
    public function listBlurbs(DOMElement $node, Ebook $ebook): void
    {
        $class = $node->hasAttribute(qualifiedName: "class")
            ? $node->getAttribute(qualifiedName: "class")
            : "";
        $heading = $node->hasAttribute(qualifiedName: "heading")
            ? $node->getAttribute(qualifiedName: "heading")
            : "h4";
        $blurbs = "";
        foreach ($ebook->texts as $text)
            if (!empty($text->blurb))
                $blurbs .= "<$heading class='$class'><lea:link>" . $text->title . " by " . $text->authors[0]->name
                    . "</lea:link></$heading>" . $text->blurb . PHP_EOL;
        XMLetsGoCrazy::replaceNodeWithStringContent(node: $node, string: $blurbs);
    }

    /**
     * Iterates over all authors and inserts text blocks into the DOM structure
     * based on the author's name.
     * Useful for creating a back matter chapter of author biographies,
     * or just a list of authors, depending on what you put into the text blocks.
     *
     * @param DOMElement $node
     * @param Ebook $ebook
     * @return void
     */
    public function listAuthors(DOMElement $node, Ebook $ebook): void
    {
        $folder = $node->hasAttribute(qualifiedName: 'folder')
            ? $node->getAttribute(qualifiedName: 'folder')
            : "";
        $class = $node->hasAttribute(qualifiedName: 'class')
            ? $node->getAttribute(qualifiedName: 'class')
            : "";
        $pattern = $node->hasAttribute(qualifiedName: "skip")
            ? "/" . $node->getAttribute(qualifiedName: "skip") . "/i"
            : "";
        $authors = [];
        foreach ($ebook->texts as $text)
            foreach ($text->authors as $author)
                if ($pattern === "" || preg_match($pattern, $author->name) === 0)
                    $authors[$author->fileAs !== "" ? $author->fileAs : $author->name] = $author;
        ksort(array: $authors);
        $output = "";
        $ctr = 0;
        foreach ($authors as $author) {
            if ($ctr++ > 0)
                $output .= "<div class='$class'/>" . PHP_EOL;
            $output .= "<lea:block>$folder/$author->name.xhtml</lea:block>" . PHP_EOL;
        }
        XMLetsGoCrazy::replaceNodeWithStringContent(node: $node, string: $output);
    }

    /**
     * Replaces a DOM node with a structured HTML representation of a linked image.
     *
     * @param DOMElement $node The DOM node to be replaced. The node must include attributes "to" and "image".
     * @param Ebook $ebook The ebook object used for error handling and fetching default values if necessary.
     * @return void
     * @throws Exception
     */
    public function linkedImage(DOMElement $node, Ebook $ebook): void
    {
        if (!$node->hasAttribute(qualifiedName: "to")) {
            Girlfriend::comeToMe()->makeDoveCry($ebook, "linkedImageMissingTo");
            return;
        }
        $to = $node->getAttribute(qualifiedName: "to");
        if (!$node->hasAttribute(qualifiedName: "image")) {
            Girlfriend::comeToMe()->makeDoveCry($ebook, "linkedImageMissingImage");
            return;
        }
        $image = Girlfriend::comeToMe()->strToEpubImageFileName($node->getAttribute(qualifiedName: "image"));
        $caption = $node->hasAttribute(qualifiedName: "caption")
            ? $node->getAttribute(qualifiedName: "caption")
            : Girlfriend::comeToMe()->recall(name: "defaultcaption");
        $folder = $node->hasAttribute(qualifiedName: "folder")
            ? trim(string: $node->getAttribute(qualifiedName: "folder"), characters: "/ ") . "/"
            : Girlfriend::comeToMe()->recall(name: "subfolder-images");
        $replacement = "<figure>"
            . "<lea:link to='$to'>"
            . "<img src='../Images/$image' alt='$caption'/>"
            . "</lea:link>"
            . "<figcaption>$caption</figcaption>"
            . "</figure>";
        $ebook->addImages([new Image(
            fileName: trim($node->getAttribute(qualifiedName: "image")),
            folder: $folder,
            caption: $caption)]);
        XMLetsGoCrazy::replaceNodeWithStringContent(node: $node, string: $replacement);
    }

    /**
     * Processes an image node, generates a corresponding figure element with an image and caption,
     * and replaces the original node in the DOM structure. Also adds the image to the ebook's image collection.
     *
     * @param DOMElement $node The DOM node that represents the image. This node should have attributes for the image file, caption, and folder.
     * @param Ebook $ebook The ebook object to which the processed image and its metadata will be added.
     * @return void
     * @throws Exception
     */
    public function lockedImage(DOMElement $node, Ebook $ebook): void
    {
        if (!$node->hasAttribute(qualifiedName: "image")) {
            Girlfriend::comeToMe()->makeDoveCry($ebook, "lockedImageMissingImage");
            return;
        }
        $image = Girlfriend::comeToMe()->strToEpubImageFileName($node->getAttribute(qualifiedName: "image"));
        $caption = $node->hasAttribute(qualifiedName: "caption")
            ? $node->getAttribute(qualifiedName: "caption")
            : Girlfriend::comeToMe()->recall(name: "defaultcaption");
        $folder = $node->hasAttribute(qualifiedName: "folder")
            ? trim(string: $node->getAttribute(qualifiedName: "folder"), characters: "/ ") . "/"
            : Girlfriend::comeToMe()->recall(name: "subfolder-images");
        $replacement = "<figure>"
            . "<img src='../Images/$image' alt='$caption'/>"
            . "<figcaption>$caption</figcaption>"
            . "</figure>";
        $ebook->addImages([new Image(
            fileName: trim($node->getAttribute(qualifiedName: "image")),
            folder: $folder,
            caption: $caption)]);
        XMLetsGoCrazy::replaceNodeWithStringContent(node: $node, string: $replacement);
    }
}
