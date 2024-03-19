<?php

namespace Buepro\BookmarkPages\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class Bookmark
{
    protected string $id;

    protected string $title;

    protected string $url;

    protected int $pid;

    protected string $parameter;

    /**
     * Bookmark constructor.
     * Initialize the bookmark with data
     *
     * @param string|array $url Full url or bookmark data array (same as array from toArray())
     * @param null $title
     * @param null $pid page id
     * @param null $parameter
     */
    public function __construct($url, $title = null, $pid = null, $parameter = null)
    {
        if (is_array($url)) {
            $this->id = $url['id'];
            $this->title = $url['title'];
            $this->url = $url['url'];
            $this->pid = $url['pid'];
            $this->parameter = $url['parameter'];
        } else {
            $this->id = md5($pid . ':' . $parameter);
            $this->title = $title;
            $this->url = $url;
            $this->pid = $pid;
            $this->parameter = $parameter;
        }
    }

    /**
     * Create bookmark from the current TSFE page
     *
     * @param string url to bookmark, if null TYPO3_REQUEST_URL will be used - which is wrong when we're in ajax context, then we use HTTP_REFERER
     */
    public static function createFromCurrent($url = null): self
    {
        if ($url === null) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                //request is ajax
                $url = GeneralUtility::getIndpEnv('HTTP_REFERER');
            } else {
                $url = GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
            }
        }

        $pid = self::getFrontend()->id;
        $title = self::getCurrentPageTitle();

        /*

        The idea was to store get parameters to make bookmark handling more flexible.
        Unfortunately that didn't worked out.

        When we use ajax to trigger bookmarking the current page, we can pass the current url as parameter.
        But the url doesn't have the parameters in it when you use speaking urls (realurl, simulatestatic, ...).
        The problem is that there's no common api to decode urls and get the parameters.

        One solution would be to make the parameters available to the ajax javascript during page rendering.

        We skip all this and use a bit from the url for hashing and add the page id.

         */

        $urlParts = parse_url($url ?? '');
        $parameter = $urlParts['path'];
        $parameter .= isset($urlParts['query']) ? '?' . $urlParts['query'] : '';
        $parameter .= isset($urlParts['fragment']) ? '#' . $urlParts['fragment'] : '';

        return new self($url, $title, $pid, $parameter);

        /*
         * So what is the idea of storing the pid and the get vars?
         *
         * This might makes sense if urls changed for the same page (realurl).
         * With this information the new working url can be restored.
         *
         * Not sure which way is better ...
         */
        //        $parameter = (array)GeneralUtility::_GET();
        //        unset($parameter['id']);
        //        // @todo remove cHash?
        //        ksort($parameter);
        //        $parameter = $parameter ? GeneralUtility::implodeArrayForUrl(false, $parameter) : '';
        //
        //        return new self($url, $title, $pid, $parameter);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url): void
    {
        $this->url = $url;
    }

    /**
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @param int $pid
     */
    public function setPid($pid): void
    {
        $this->pid = $pid;
    }

    /**
     * @return string
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * @param string $parameter
     */
    public function setParameter($parameter): void
    {
        $this->parameter = $parameter;
    }

    /**
     * Returns the bookmark data as array
     *
     * @return array{id: string, title: string, url: string, pid: int, parameter: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->url,
            'pid' => $this->pid,
            'parameter' => $this->parameter,
        ];
    }

    /**
     * Get the current page title
     *
     * @return string
     */
    protected static function getCurrentPageTitle()
    {
        return self::getFrontend()->altPageTitle ? self::getFrontend()->altPageTitle : self::getFrontend()->page['title'];
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected static function getFrontend()
    {
        return $GLOBALS['TSFE'];
    }
}
