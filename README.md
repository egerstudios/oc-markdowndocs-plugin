## A simple documentation plugin for the October CMS backend

Using markdown files in a folder of your choice i in the storage folder you can easily create simple documentation in the backend.
A simple yaml-section at the beginning of your markdown file contains the file metadata, and sets options for the sidebar display.


#### License
The plugin is open-sourced software licensed under the MIT license same as OctoberCMS platform.

#### Front matter

```yaml
---
title: Install          # sidebar label
icon: icon-download     # optional
position: 2             # sort order
parent: guide/index.md  # optional: nest under another page
hidden: true            # optional: not in sidebar, still reachable by link
permissions:            # optional: user must hold all of these
  - acme.blog.manage_posts
---
```

#### Submenus
Subfolders are nested automatically. `guide/index.md` becomes the parent of the other files in `guide/`;
a folder without an `index.md` becomes a non-clickable group. `parent:` overrides the folder structure.

#### Permissions
- `egerstudios.markdowndocs.docs` – open the Docs section.
- `egerstudios.markdowndocs.settings` – edit the plugin settings.
- `permissions:` in a page's front matter restricts that page (and its subpages) to users holding any October permission code. Direct links are checked too.

#### Search, TOC and links
- The sidebar search covers titles and page text, and only returns pages the user may view.
- Each page shows an "On this page" list built from its `##`–`####` headings.
- Relative links work: `[next](install.md)`, `[up](../index.md#intro)`, `![shot](img/screen.png)`. Images are served from the docs folder only.
- Markdown is rendered with October's own Markdown parser in safe mode (raw HTML is stripped).

#### Code highlighting
Optional: run `composer update` to install `scrivo/highlight.php`, then use fenced blocks with a language (` ```php `). Without the package, code is shown unhighlighted.

#### Editing
1. Enable **Allow editing in the backend** in the plugin settings (the docs folder must be writable).
2. Grant users the **Edit documentation pages** permission.

An Edit button then appears on every page the user can view. Saving validates the front matter. Rendered pages and the search index are cached and refresh automatically when files change.
