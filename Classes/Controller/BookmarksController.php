<?php

/*
 * This file is part of the package buepro/bookmark_pages.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\BookmarkPages\Controller;

use Buepro\BookmarkPages\Model\Bookmark;
use Buepro\BookmarkPages\Model\Bookmarks;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Plugin controller
 */
class BookmarksController extends ActionController
{
    /**
     * display bookmarks list
     */
    public function indexAction(): ResponseInterface
    {
        $bookmarks = new Bookmarks();

        // check if we bookmarked the current page
        $bookmark = Bookmark::createFromCurrent();
        $isBookmarked = $bookmarks->bookmarkExists($bookmark);

        $this->view->assign('bookmarks', $bookmarks->getBookmarks());
        $this->view->assign('isBookmarked', $isBookmarked);
        return $this->htmlResponse();
    }

    /**
     * Adds the current page as bookmark and renders/returns updated list as html
     *
     * This is meant to be called by ajax (typoscript_rendering)
     *
     * @param array $localBookmarks
     */
    public function bookmarkAction($localBookmarks = []): ResponseInterface
    {
        // use the parameter directly and ignore chash because url is submitted by JS
        $url = GeneralUtility::_GP('url');
        $url = $url ? $url : null;

        $bookmark = Bookmark::createFromCurrent($url);

        if (GeneralUtility::_GP('title')) {
            $bookmark->setTitle(GeneralUtility::_GP('title'));
        }

        $bookmarks = new Bookmarks();
        $bookmarks->merge($localBookmarks);
        $bookmarks->addBookmark($bookmark);
        $bookmarks->persist();

        $this->updateAndSendList($bookmarks);
        return $this->htmlResponse();
    }

    /**
     * Remove a bookmark from list and renders/returns updated list as html
     *
     * This is meant to be called by ajax (typoscript_rendering)
     *
     * @param string $id
     * @param array $localBookmarks
     */
    public function deleteAction($id = '', $localBookmarks = []): ResponseInterface
    {
        $bookmarks = new Bookmarks();
        $bookmarks->merge($localBookmarks);
        if ($id !== '' && $id !== '0') {
            $bookmarks->removeBookmark($id);
            $bookmarks->persist();
        }

        $this->updateAndSendList($bookmarks);
        return $this->htmlResponse();
    }

    /**
     * Action to get bookmark list
     *
     * @param array $localBookmarks
     */
    public function listEntriesAction($localBookmarks = []): ResponseInterface
    {
        $bookmarks = new Bookmarks();
        $bookmarks->merge($localBookmarks);
        $this->updateAndSendList($bookmarks);
        return $this->htmlResponse();
    }

    /**
     * This is for ajax requests
     */
    public function updateAndSendList(Bookmarks $bookmarks): void
    {
        // build the html for the response
        $this->view->assign('bookmarks', $bookmarks->getBookmarks());
        $listHtml = $this->view->render();

        // check if we bookmarked the current page
        $bookmark = Bookmark::createFromCurrent();
        $isBookmarked = $bookmarks->bookmarkExists($bookmark);

        // build the ajax response data
        $response = [
            'isBookmarked' => $isBookmarked,
            'bookmarks' => $bookmarks->getBookmarksForLocalStorage(),
            'list' => $listHtml,
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
        die();
    }
}
