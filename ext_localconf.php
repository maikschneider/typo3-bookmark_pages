<?php

/*
 * This file is part of the package buepro/bookmark_pages.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

defined('TYPO3') or die('Access denied.');

(function () {
    $version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version();
    $version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger($version);
    if ($version < 10000000) {
        // For TYPO3 < V10
        // @extensionScannerIgnoreLine
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Buepro.BookmarkPages',
            'Bookmarks',
            [
                'Bookmarks' => 'index, bookmark, delete, listEntries',
            ],
            // non-cacheable actions
            [
                'Bookmarks' => 'bookmark, delete, listEntries',
            ]
        );
    } else {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'BookmarkPages',
            'Bookmarks',
            [
                \Buepro\BookmarkPages\Controller\BookmarksController::class => 'index, bookmark, delete, listEntries'
            ],
            [
                \Buepro\BookmarkPages\Controller\BookmarksController::class => 'bookmark, delete, listEntries'
            ]
        );
    }
})();
