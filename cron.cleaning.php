<?php

    require __DIR__ . DIRECTORY_SEPARATOR . 'mainfile.php';
    
    deleteFilesNotListedByArray(API_FONTS_WEBDAV, array_merge(array_keys(getExtractionShellExec()), cleanWhitespaces(file(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'font-converted.diz'))));
    deleteFilesNotListedByArray(API_FONTS_SOURCES, array_merge(array_keys(getExtractionShellExec()), cleanWhitespaces(file(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'font-converted.diz'))));
    removeEmptyPathFolderList(API_FONTS_WEBDAV);
    removeEmptyPathFolderList(API_FONTS_SOURCES);
?>