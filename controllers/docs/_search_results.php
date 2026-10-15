<?php $query = $query ?? ''; $results = $results ?? []; ?>
<?php if ($query !== ''): ?>
    <ul class="docs-search-list">
        <?php if (!$results): ?>
            <li class="docs-search-empty"><?= e(mb_strlen($query) < 2 ? __('egerstudios.markdowndocs::lang.search.min_chars') : __('egerstudios.markdowndocs::lang.search.no_results')) ?></li>
        <?php endif ?>
        <?php foreach ($results as $hit): ?>
            <li>
                <a href="?file=<?= urlencode($hit['id']) ?>">
                    <strong><i class="<?= e($hit['icon'] ?: 'icon-file') ?>"></i> <?= e($hit['title']) ?></strong>
                    <small><?= $hit['snippet'] /* escaped in DocTree::snippet */ ?></small>
                </a>
            </li>
        <?php endforeach ?>
    </ul>
<?php endif ?>
