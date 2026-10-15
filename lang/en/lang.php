<?php

return [
    'plugin' => [
        'name' => 'MarkdownDocs',
        'description' => 'Display markdown files as documentation in the backend.',
    ],
    'nav' => [
        'docs' => 'Docs',
    ],
    'permissions' => [
        'tab' => 'MarkdownDocs',
        'docs' => 'Access the documentation',
        'edit' => 'Edit documentation pages',
        'settings' => 'Manage documentation settings',
    ],
    'settings' => [
        'label' => 'Docs settings',
        'description' => 'Manage docs based settings.',
        'category' => 'Documentation',
        'title' => 'Title for documentation docs',
        'storage_path' => 'Storage path',
        'storage_path_comment' => 'Should end with trailing slash',
        'allow_editing' => 'Allow editing in the backend',
        'allow_editing_comment' => "Shows an Edit button to users with the 'Edit documentation pages' permission. Files must be writable by the web server.",
    ],
    'default_title' => 'Documentation',
    'page' => [
        'not_found' => 'File not found.',
        'no_access' => 'You do not have access to this page.',
        'edit_forbidden' => 'You are not allowed to edit this page.',
        'too_large' => 'The file is too large.',
        'invalid_front_matter' => 'Invalid front matter: :message',
        'saved' => 'Page saved.',
        'on_this_page' => 'On this page',
    ],
    'search' => [
        'placeholder' => 'Search docs…',
        'min_chars' => 'Type at least 2 characters',
        'no_results' => 'No results',
    ],
    'actions' => [
        'edit' => 'Edit',
        'save' => 'Save',
        'saving' => 'Saving…',
        'cancel' => 'Cancel',
    ],
];
