<?php

declare(strict_types=1);

namespace Lea\Adore;

use DOMException;
use Exception;
use NoDiscard;
use Throwable;
use Lea\Domain\Author;
use Lea\Domain\Ebook;
use Lea\Domain\Text;
use Lea\Domain\XMLetsGoCrazy;

/**
 * There is a park that is known 4 the face it attracts. Admission is easy, just say U believe.
 */
final class PaisleyPark
{
    private(set) Ebook $ebook {
        get => $this->ebook ??= $this->cream($this->fileName);
    }
    private(set) TheOpera $theOpera {
        get => $this->theOpera ??= new TheOpera($this->ebook);
    }
    private(set) AlphabetSt $alphabetSt {
        get => $this->alphabetSt ??= new AlphabetSt();
    }

    public function __construct(
        private string $fileName {
            set => trim(string: $value);
        }
    )
    {
    }

    /**
     * You got the horn so why don't you blow it?
     *
     * The Cream gets the hard data structures for the ebook all lubed up.
     *
     * @param string $fileName
     * @return Ebook
     */
    #[NoDiscard]
    private function cream(string $fileName): Ebook
    {
        return new Ebook(fileName: $fileName);
    }

    /**
     * I hope you don't mind I'm recording our conversati–
     *
     * The Segue checks whether it is safe to proceed.
     *
     * @return void
     * @throws Exception
     */
    public function segue(): void
    {
        echo "SEARCHING" . PHP_EOL;
        /**
         * Checks if the ebook config file has been read successfully.
         * - No = fatal
         */
        if ($this->ebook->xml === "") {
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookReadError",
                Girlfriend::$pathEbooks . $this->ebook->fileName);
            return;
        }
        echo "FOUND" . PHP_EOL;
        /**
         * Checks if the ebook config file is well-formed.
         * - No = fatal
         */
        if (!XMLetsGoCrazy::isWellFormed($this->ebook->xpath)) {
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookNotWellFormed",
                Girlfriend::$pathEbooks . $this->ebook->fileName);
            return;
        }
        echo "LOADING" . PHP_EOL;
        XMLetsGoCrazy::extractSubFolder($this->ebook);
        XMLetsGoCrazy::extractOptions($this->ebook->xpath);
        XMLetsGoCrazy::executeLeaScriptTags($this->ebook, $this->alphabetSt, ebook: $this->ebook);
        /**
         * Checks if there is at least one title.
         * - No = fatal
         */
        if ($this->ebook->title === "")
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookTitleRequired",
                Girlfriend::$pathEbooks . $this->ebook->fileName);
        /**
         * Checks if there is more than one <lea:title> definition.
         * - Yes = use first, continue with a warning message
         */
        if ($this->ebook->xpath->query(expression: "/" . XMLetsGoCrazy::$rootElement . "/lea:title")->length > 1)
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookMultipleTitles",
                $this->ebook->title, Girlfriend::$pathEbooks . $this->ebook->fileName);
        /**
         * Checks if there is at least one <lea:description>.
         * - No = severe, continue
         */
        if ($this->ebook->description === "")
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookDescriptionRecommended",
                Girlfriend::$pathEbooks . $this->ebook->fileName);
        /**
         * Checks if there is more than one <lea:description> definition.
         * - yes = use first, continue with a warning message
         */
        if ($this->ebook->xpath->query(expression: "/" . XMLetsGoCrazy::$rootElement . "/lea:description")->length > 1)
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookMultipleDescriptions",
                $this->ebook->description, Girlfriend::$pathEbooks . $this->ebook->fileName);
        /**
         * Checks if there is at least one <lea:publisher>.
         * - No = severe, continue
         */
        if (!$this->ebook->publisher->isValid())
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookPublisherMandatory",
                Girlfriend::$pathEbooks . $this->ebook->fileName);
        /**
         * Checks if there is at least one <lea:rights> tag.
         * - no = severe, continue
         */
        if ($this->ebook->rights === "")
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookRightsRecommended",
                Girlfriend::$pathEbooks . $this->ebook->fileName);
        /**
         * Checks if there is more than one <lea:rights> definitions.
         * - yes = use first, continue with warning message
         */
        if ($this->ebook->xpath->query(expression: "/" . XMLetsGoCrazy::$rootElement . "/lea:rights")->length > 1)
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookMultipleRights",
                $this->ebook->rights, Girlfriend::$pathEbooks . $this->ebook->fileName);
        /**
         * Checks if there is at least one <lea:language> tag.
         * - no = severe, continue
         */
        if ($this->ebook->language === "")
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookLanguageRecommended",
                Girlfriend::$pathEbooks . $this->ebook->fileName);
        /**
         * Checks if there is at least one author.
         * - no = fatal
         */
        if (count($this->ebook->authors) === 0)
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookAuthorRequired",
                Girlfriend::$pathEbooks . $this->ebook->fileName);
        /**
         * Checks if there are invalid <author> definitions present in the ebook config.
         * - yes = continue with all valid <author> definitions
         */
        if (!XMLetsGoCrazy::validateAuthors($this->ebook->xpath))
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookInvalidAuthor",
                (string)count($this->ebook->authors), Girlfriend::$pathEbooks . $this->ebook->fileName);
        /**
         * Checks if the date has been extracted successfully
         * - No = fatal
         */
        if (!$this->ebook->date->isValid())
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookInvalidDate",
                Girlfriend::$pathEbooks . $this->ebook->fileName);
        /**
         * Checks if there are invalid <contributor> definitions present in the ebook config.
         * - Yes = continue with all valid <contributor> definitions
         */
        if (!XMLetsGoCrazy::validateContributors($this->ebook))
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookInvalidContributor",
                (string)count($this->ebook->contributors), Girlfriend::$pathEbooks . $this->ebook->fileName);
        /**
         * Checks if the ISBN is valid.
         * - No = fatal
         */
        if (!$this->ebook->isbn->isValid)
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookInvalidISBN",
                $this->ebook->isbn->isbn, Girlfriend::$pathEbooks . $this->ebook->fileName);
        /**
         * Checks if there are more than one <isbn> definitions.
         * - Yes = use first, continue with a warning message
         */
        if ($this->ebook->xpath->query(expression: "/" . XMLetsGoCrazy::$rootElement . "/lea:isbn")->length > 1)
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookMultipleISBNs",
                $this->ebook->isbn->isbn, Girlfriend::$pathEbooks . $this->ebook->fileName);
        /**
         * If there is a <lea:cover> declaration, check if the file exists in the file system.
         * - No = fatal
         */
        $coverFileName = Girlfriend::$pathImages . Girlfriend::comeToMe()->recall(name: "subfolder-images") . $this->ebook->cover;
        if (($this->ebook->cover !== "") && !is_file(filename: $coverFileName))
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookInvalidCover",
                $coverFileName, Girlfriend::$pathEbooks . $this->ebook->fileName);
        /**
         * Check if there is more than one <lea:cover> definition.
         * - Yes = use first, continue with a warning message
         */
        if ($this->ebook->xpath->query(expression: "/" . XMLetsGoCrazy::$rootElement . "/lea:cover")->length > 1)
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookMultipleCovers",
                $this->ebook->cover, Girlfriend::$pathEbooks . $this->ebook->fileName);
        /**
         * Time to direct our gaze at the text files
         */
        foreach ($this->ebook->texts as $text) {
            /**
             * Checks if the text file has been read successfully.
             * - No = fatal
             */
            if ($text->xhtml === "") {
                Girlfriend::comeToMe()->makeDoveCry($text, "textReadError",
                    Girlfriend::$pathText . Girlfriend::comeToMe()->recall(name: "subfolder-text") . "$text->fileName");
                continue;
            }
            /**
             * Checks if the text file is well-formed.
             * - No = fatal
             */
            if (!XMLetsGoCrazy::isWellFormed($text->xpath)) {
                Girlfriend::comeToMe()->makeDoveCry($text, "textNotWellFormed",
                    Girlfriend::$pathText . Girlfriend::comeToMe()->recall(name: "subfolder-text") . "$text->fileName");
                continue;
            }
            /**
             * Checks if there is at least one title.
             * - No = fatal
             */
            if ($text->title === "")
                Girlfriend::comeToMe()->makeDoveCry($text, "textTitleRequired",
                    Girlfriend::$pathText . Girlfriend::comeToMe()->recall(name: "subfolder-text") . "$text->fileName");
            /**
             * Checks if there are more than one <title> definitions.
             * - Yes = use first, continue with a warning message
             */
            if ($text->xpath->query(expression: "/" . XMLetsGoCrazy::$rootElement . "/lea:title")->length > 1)
                Girlfriend::comeToMe()->makeDoveCry($text, "textMultipleTitles", $text->title,
                    Girlfriend::$pathText . Girlfriend::comeToMe()->recall(name: "subfolder-text") . "$text->fileName");
            /**
             * Checks if there is at least one author.
             * - no = fatal
             */
            if (count($text->authors) === 0)
                Girlfriend::comeToMe()->makeDoveCry($text, "textAuthorRequired",
                    Girlfriend::$pathText . Girlfriend::comeToMe()->recall(name: "subfolder-text") . "$text->fileName");
            /**
             * Checks if there are invalid <author> definitions present in the text file.
             * - Yes = continue with all valid <author> definitions
             */
            if (XMLetsGoCrazy::validateAuthors($text->xpath) === false)
                Girlfriend::comeToMe()->makeDoveCry($text, "textInvalidAuthor", (string)count($text->authors),
                    Girlfriend::$pathText . Girlfriend::comeToMe()->recall(name: "subfolder-text") . "$text->fileName");
            /**
             * Checks if there are more than one <lea:blurb> definitions.
             * - Yes = use the first blurb found, continue with a warning message
             */
            if ($text->xpath->query(expression: "/" . XMLetsGoCrazy::$rootElement . "/lea:blurb")->length > 1)
                Girlfriend::comeToMe()->makeDoveCry($text, "textMultipleBlurbs", $text->blurb,
                    Girlfriend::$pathText . Girlfriend::comeToMe()->recall(name: "subfolder-text") . "$text->fileName");
        }
    }

    /**
     * Tell me, how're we gonna put this back together?
     * How're we gonna think with the same mind?
     *
     * If messages exist, display them, with additional info.
     *
     * Returns values:
     * - true if no fatal errors detected
     *
     * @return bool
     */
    #[NoDiscard]
    public function inThisBedEyeScream(): bool
    {
        $fatal = false;
        $infoOnly = true;
        foreach (Girlfriend::comeToMe()->doveCries as $msg) {
            echo match ($msg->flaw) {
                Flaw::Info => Fancy::info(msg: "[ INFO ]") . PHP_EOL . $msg->message . PHP_EOL,
                Flaw::Warning => Fancy::warning(msg: "[ WARNING ]" . PHP_EOL) . $msg->message . PHP_EOL,
                Flaw::Severe => Fancy::severe(msg: "[ SEVERE ]") . PHP_EOL . $msg->message . PHP_EOL,
                Flaw::Fatal => Fancy::fatal(msg: "[ FATAL ]") . PHP_EOL . $msg->message . PHP_EOL
            };
            echo Fancy::suggestion(msg: "[ Suggestion ] ") . PHP_EOL . ($msg->suggestion ?: "[ none ]") . PHP_EOL . PHP_EOL;
            $fatal = $fatal || ($msg->flaw === Flaw::Fatal);
            $infoOnly = $infoOnly && ($msg->flaw === Flaw::Info);
        }
        if (!$infoOnly) {
            echo Fancy::info(msg: "[ INFO ]") . "    shows potential for improvement. The produced EPUB may be less than ideal." . PHP_EOL;
            echo Fancy::warning(msg: "[ WARNING ]") . " denotes missing optional data. The EPUB should not be published." . PHP_EOL;
            echo Fancy::severe(msg: "[ SEVERE ]") . "  requires guessing from Lea. The EPUB must not be published." . PHP_EOL;
            echo Fancy::fatal(msg: "[ FATAL ]") . "   cannot be resolved. No EPUB will be produced." . PHP_EOL;
        }
        echo PHP_EOL;
        return !$fatal;
    }

    /**
     * Validate external links in the text files.
     *
     * @return void
     * @throws Exception
     */
    private function validateURLs(): void
    {
        $urls = [];
        foreach ($this->ebook->texts as $text)
            foreach (XMLetsGoCrazy::extractLinks($text->xpath) as $link)
                if (filter_var($link, filter: FILTER_VALIDATE_URL) !== false)
                    $urls[$link] = $link;
        $animCtr = 0;
        $handles = [];
        $mh = curl_multi_init();
        foreach ($urls as $url) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_NOBODY => false,            // GET request, because Elon Musk sucks (sometimes)
                CURLOPT_RANGE => '0-0',
                CURLOPT_TIMEOUT => 5,               // Total timeout: 5 seconds max for the whole request
                CURLOPT_CONNECTTIMEOUT => 3,        // Max 3 seconds to establish connection
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_USERAGENT => Girlfriend::comeToMe()->leaNamePlain,
            ]);
            curl_multi_add_handle($mh, $ch);
            $handles[$url] = $ch;                   // keep reference to the curl handle
        }
        echo Fancy::HIDE_CURSOR;
        do {
            curl_multi_exec($mh, still_running: $active);
            curl_multi_select($mh);
            echo "\r[ " . Fancy::PURPLE_RAIN_BOLD_INVERSE_WHITE
                . Fancy::ANIMATION[$animCtr++ % strlen(string: Fancy::ANIMATION)] . Fancy::RESET
                . " ] [ " . Fancy::INVERSE . $active . "/" . count($handles) . " active" . Fancy::RESET . " ]"
                . " Resolving external link '" . array_rand($urls) . "'" . Fancy::CLR_EOL;
            flush();
            usleep(microseconds: 200000);
        } while ($active > 0);
        echo Fancy::UNHIDE_CURSOR;
        foreach ($handles as $key => $ch) {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            if ($httpCode >= 400)
                Girlfriend::comeToMe()->makeDoveCry($this->ebook, "externalLinkCheckFailed", $key);
            elseif ($error)
                Girlfriend::comeToMe()->makeDoveCry($this->ebook, "externalLinkCheckTimeout", $key, $error);
        }
        curl_multi_close($mh);
        echo "\r" . Fancy::CLR_EOL;
    }

    /**
     * You know, if you don't give me the real story, I'll have to make one up of my own.
     *
     * The Segue uses the established data clarity for a few final steps to put into place.
     *
     * @return void
     * @throws DOMException
     * @throws Exception
     */
    public function seguePartTwo(): void
    {
        /**
         * Before doing anything else, let's trigger sanitizeStylesheets here
         * so that we will have a complete set of identifiers to work with.
         * That's because stylesheets may reference image files,
         * and those image files will be registered by this sanitization,
         * and therefore, compiling all normalized identifiers
         * should not be done before that happens.
         */
        $discard = Girlfriend::comeToMe()->sanitizeStylesheets($this->ebook);
        /**
         * Execute all <lea:script> tags.
         */
        foreach ($this->ebook->texts as $text)
            XMLetsGoCrazy::executeLeaScriptTags($text, $this->alphabetSt, $this->ebook);
        /**
         * Replace all <lea:block> tags with xhtml content.
         */
        foreach ($this->ebook->texts as $text)
            XMLetsGoCrazy::replaceLeaBlockTags($text);
        /**
         * Execute all <lea:script> tags.
         */
        foreach ($this->ebook->texts as $text)
            XMLetsGoCrazy::executeLeaScriptTags($text, $this->alphabetSt, $this->ebook);
        /**
         * We now have dynamically generated content from scripts and blocks resolved.
         * Let's then extract images from all text content files and add them to the ebook image list.
         */
        if (Girlfriend::comeToMe()->recall(name: "include-images") === "yes")
            foreach ($this->ebook->texts as $text)
                $this->ebook->addImages(XMLetsGoCrazy::extractImages($text));
        /**
         * Also, extract subjects from all text content files and add them to the ebook image list.
         */
        foreach ($this->ebook->texts as $text)
            $this->ebook->addSubjects(XMLetsGoCrazy::extractSubjects($text->xpath));
        /**
         * Checks if there are any <lea:subject> declarations.
         * - no = warning about missing subject declarations
         */
        if (count($this->ebook->subjects) === 0)
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "ebookSubjectRecommended",
                Girlfriend::$pathEbooks . $this->ebook->fileName);
        /**
         * Checks if there are any <lea:image> tags defining file names not found in the file system.
         * - Yes = fatal
         */
        /**
         * $missing = [];
         * foreach ($this->ebook->images as $image)
         * if (!is_file(
         * filename: Girlfriend::$pathImages . $image->folder . $image->fileName)
         * )
         * $missing[] = Girlfriend::$pathImages . $image->folder . $image->fileName;
         * if (count($missing) > 0)
         * Girlfriend::comeToMe()->makeDoveCry($this->ebook, "imageReadError",
         * (string)count($missing), implode(separator: PHP_EOL, array: $missing),
         * Girlfriend::$pathEbooks . $this->ebook->fileName);
         */
        /**
         * Replace all <lea:chapter> and <lea:section> tags with xhtml.
         */
        foreach ($this->ebook->texts as $text) {
            XMLetsGoCrazy::replaceLeaChapterTags($text);
            XMLetsGoCrazy::replaceLeaSectionTags($text);
        }
        /**
         * Replace all <lea:image> tags with xhtml.
         */
        foreach ($this->ebook->texts as $text)
            XMLetsGoCrazy::replaceLeaImageTags($text, $this->ebook->images);
        /**
         * If a cover image was defined, create a text file for it and add it to the EPUB.
         */
        if ($this->ebook->cover !== "") {
            $text = new Text(fileName: "cover.xhtml", xhtml: $this->theOpera->generateCoverFile(), title: "Cover");
            $text->addAuthor(new Author(Girlfriend::comeToMe()->leaNamePlain));
            $this->ebook->addText(text: $text);
        }
        /**
         * The nav.xhtml is mandatory EPUB navigation, so we create and add it here.
         */
        $text = new Text(fileName: "nav.xhtml", xhtml: $this->theOpera->generateNavFile(), title: "EPUB Navigation");
        $text->addAuthor(new Author(Girlfriend::comeToMe()->leaNamePlain));
        $this->ebook->addText(text: $text);
        /**
         * Let's also quickly add all authors of text content to the Ebook authors list.
         * It'll be easier later, and any segue is supposed to provide smooth transitions.
         * We'll do it with an array, so we auto-eliminate duplicates.
         */
        $authorData = [];
        foreach ($this->ebook->authors as $author)
            $authorData[$author->name] = $author->fileAs;
        foreach ($this->ebook->texts as $text)
            foreach ($text->authors as $author)
                $authorData[$author->name] = $author->fileAs;
        $this->ebook->eraseAuthors();
        foreach ($authorData as $name => $fileAs)
            if ($name !== Girlfriend::comeToMe()->leaNamePlain)
                $this->ebook->addAuthor(new Author(name: $name, fileAs: $fileAs));
        /**
         * If the user requested to check external links, do it now.
         */
        if (Girlfriend::comeToMe()->recall(name: "check-urls") === "yes")
            $this->validateURLs();
        else
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "urlsNotChecked", $this->ebook->fileName);
        /**
         * At this point, we want to resolve any links in the text content.
         */
        $targetData = [];
        foreach ($this->ebook->texts as $text) {
            $target = [
                "name" => "",
                "identifier" => "",
                "targetFileName" => Girlfriend::comeToMe()->strToEpubTextFileName(
                    title: $text->title . " by " . $text->authors[0]->name)
            ];
            $targetData[Girlfriend::$leaPrefixes["target"] . Girlfriend::comeToMe()->strToEpubIdentifier($text->title)]
                = $targetData[Girlfriend::$leaPrefixes["target"] . Girlfriend::comeToMe()->strToEpubIdentifier(
                string: $text->title . " by " . $text->authors[0]->name)]
                = $target;
        }
        foreach ($this->ebook->targets as $target)
            $targetData[$target->identifier] = [
                "name" => $target->name,
                "identifier" => $target->identifier,
                "targetFileName" => $target->targetFileName
            ];
        foreach ($this->ebook->texts as $text) {
            XMLetsGoCrazy::replaceLeaTargetTags($text);
            XMLetsGoCrazy::replaceLeaLinkTags($text, $targetData);
        }
        /**
         * Finally, throw an Info message to the user if EPUBCheck was not requested.
         */
        if (Girlfriend::comeToMe()->recall(name: "check-epub") === "no")
            Girlfriend::comeToMe()->makeDoveCry($this->ebook, "epubNotChecked", $this->ebook->fileName);
    }

    /**
     * With one more verse to the story
     * I need another piece of your ear
     * I want to hip you all to the reason
     * I'm known as the Player of the Year
     *
     * @return bool
     */
    public function pControl(): bool
    {
        try {
            $this->theOpera->theOverture();         // make sure we're laughing in the purple rain
            $this->segue();
            if (!$this->inThisBedEyeScream()) exit;
            $errorLog = Girlfriend::comeToMe()->doveCries;
            Girlfriend::comeToMe()->silenceDoves();
            $this->seguePartTwo();
            if (!$this->inThisBedEyeScream()) exit;
            $errorLog = array_merge($errorLog, Girlfriend::comeToMe()->doveCries);
            Girlfriend::comeToMe()->silenceDoves();
            $return = $this->theOpera->conductor($errorLog);
            if (!$this->inThisBedEyeScream()) exit; // error log already written into ePub, nothing to save here
        } catch (Throwable $e) {
            Girlfriend::comeToMe()->extraordinary(throwable: $e);
        }
        echo "READY." . PHP_EOL . PHP_EOL
            . sprintf("0 OK, %s:8%s", Girlfriend::comeToMe()->whereAmI()["line"], PHP_EOL);
        return $return;
    }
}
