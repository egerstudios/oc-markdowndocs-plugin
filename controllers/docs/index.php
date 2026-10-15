<div class="d-flex h-100 flyout-container">
    <div class="layout-sidenav-container">
        <div class="layout-sidenav-spacer">
            <nav id="layout-sidenav" class="layout-sidenav" data-active-class="active" data-control="sidenav">
                <div class="docs-search">
                    <input type="search" name="q" class="form-control form-control-sm" autocomplete="off"
                           placeholder="<?= e(__('Search docs…')) ?>"
                           data-request="onSearch"
                           data-track-input="300">
                    <div id="docs-search-results"></div>
                </div>
                <ul class="mainmenu-items">
                    <?= $this->makePartial('nav_items', ['nodes' => $tree, 'selectedFile' => $selectedFile, 'openIds' => $openIds]) ?>
                </ul>
            </nav>
        </div>
    </div>
    <div id="layout-body" class="layout-container flex-grow-1">
        <div class="d-flex flex-column h-100">
            <div class="padded-container d-flex flex-column h-100">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow-sm bg-white p-5">
                                <?php if ($editing): ?>
                                    <form data-request="onSave" data-request-flash class="docs-edit">
                                        <input type="hidden" name="file" value="<?= e($selectedFile) ?>">
                                        <textarea name="source" class="form-control" rows="30" spellcheck="false"><?= e($source) ?></textarea>
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-primary" data-load-indicator="<?= e(__('Saving…')) ?>"><?= e(__('Save')) ?></button>
                                            <a href="?file=<?= urlencode($selectedFile) ?>" class="btn btn-default"><?= e(__('Cancel')) ?></a>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <?php if ($canEdit): ?>
                                        <a href="?file=<?= urlencode($selectedFile) ?>&amp;edit=1" class="btn btn-sm btn-default docs-edit-link">
                                            <i class="icon-pencil"></i> <?= e(__('Edit')) ?>
                                        </a>
                                    <?php endif ?>
                                    <div class="docs-page">
                                        <div id="docs-content" class="docs-content"><?= $content ?></div>
                                        <?php if (count($toc) > 1): ?>
                                            <aside class="docs-toc">
                                                <strong><?= e(__('On this page')) ?></strong>
                                                <ul>
                                                    <?php foreach ($toc as $item): ?>
                                                        <li class="level-<?= (int) $item['level'] ?>"><a href="#<?= e($item['id']) ?>"><?= e($item['text']) ?></a></li>
                                                    <?php endforeach ?>
                                                </ul>
                                            </aside>
                                        <?php endif ?>
                                    </div>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
