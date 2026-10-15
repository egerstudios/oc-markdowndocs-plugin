<?php foreach ($nodes as $node): if (!empty($node['hidden'])) continue;
    $hasKids = !empty($node['children']);
    $isActive = $node['id'] === $selectedFile;
    $isOpen = in_array($node['id'], $openIds, true) || $isActive;
?>
<li class="mainmenu-item docs-nav-item<?= $isActive ? ' active' : '' ?><?= $hasKids ? ' has-children' : '' ?><?= $hasKids && $isOpen ? ' is-open' : '' ?>">
    <?php if ($node['link']): ?>
        <a href="?file=<?= urlencode($node['id']) ?>">
    <?php else: ?>
        <a href="#" class="docs-nav-group" onclick="this.parentNode.classList.toggle('is-open');return false;">
    <?php endif ?>
        <span class="nav-icon"><i class="<?= e($node['icon'] ?: 'icon-file') ?>"></i></span>
        <span class="nav-label"><?= e($node['title']) ?></span>
        <?php if ($hasKids): ?><span class="docs-nav-caret"></span><?php endif ?>
    </a>
    <?php if ($hasKids): ?>
        <ul class="docs-subnav">
            <?= $this->makePartial('nav_items', ['nodes' => $node['children'], 'selectedFile' => $selectedFile, 'openIds' => $openIds]) ?>
        </ul>
    <?php endif ?>
</li>
<?php endforeach ?>
