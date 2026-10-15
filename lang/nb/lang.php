<?php

return [
    'plugin' => [
        'name' => 'MarkdownDocs',
        'description' => 'Vis markdown-filer som dokumentasjon i administrasjonspanelet.',
    ],
    'nav' => [
        'docs' => 'Dokumentasjon',
    ],
    'permissions' => [
        'tab' => 'MarkdownDocs',
        'docs' => 'Få tilgang til dokumentasjonen',
        'edit' => 'Redigere dokumentasjonssider',
        'settings' => 'Administrere dokumentasjonsinnstillinger',
    ],
    'settings' => [
        'label' => 'Dokumentasjonsinnstillinger',
        'description' => 'Administrer innstillinger for dokumentasjonen.',
        'category' => 'Dokumentasjon',
        'title' => 'Tittel på dokumentasjonen',
        'storage_path' => 'Lagringssti',
        'storage_path_comment' => 'Skal slutte med skråstrek',
        'allow_editing' => 'Tillat redigering i administrasjonspanelet',
        'allow_editing_comment' => "Viser en Rediger-knapp for brukere med tillatelsen «Redigere dokumentasjonssider». Filene må være skrivbare for webserveren.",
    ],
    'default_title' => 'Dokumentasjon',
    'page' => [
        'not_found' => 'Fant ikke filen.',
        'no_access' => 'Du har ikke tilgang til denne siden.',
        'edit_forbidden' => 'Du har ikke tillatelse til å redigere denne siden.',
        'too_large' => 'Filen er for stor.',
        'invalid_front_matter' => 'Ugyldig front matter: :message',
        'saved' => 'Siden er lagret.',
        'on_this_page' => 'På denne siden',
    ],
    'search' => [
        'placeholder' => 'Søk i dokumentasjonen…',
        'min_chars' => 'Skriv minst 2 tegn',
        'no_results' => 'Ingen treff',
    ],
    'actions' => [
        'edit' => 'Rediger',
        'save' => 'Lagre',
        'saving' => 'Lagrer…',
        'cancel' => 'Avbryt',
    ],
];
