<?php

defined('TYPO3') || die('Access denied.');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'BookmarkPages',
    'Bookmarks',
    [
        \Buepro\BookmarkPages\Controller\BookmarksController::class => 'index, bookmark, delete, listEntries',
    ],
    [
        \Buepro\BookmarkPages\Controller\BookmarksController::class => 'bookmark, delete, listEntries',
    ]
);
