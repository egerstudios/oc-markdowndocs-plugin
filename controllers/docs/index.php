<?php
// Ensure variables are set
$files = $files ?? [];
$selectedFile = $selectedFile ?? '';
$content = $content ?? '';
?>

    <div class="d-flex h-100 flyout-container">
        <div class="layout-sidenav-container">
            <div class="layout-sidenav-spacer">
                <nav id="layout-sidenav" class="layout-sidenav" data-active-class="active" data-control="sidenav">
                    <ul class="mainmenu-items">
                        <?php foreach ($files as $filename => $meta): ?>
                        <li class="mainmenu-item svg-icon-container">
                            <a href="?file=<?= urlencode($filename) ?>" class="<?= $filename === $selectedFile ? 'active' : '' ?>">
                                <?php if (!empty($meta['icon'])): ?>
                                    <span class="nav-icon">
                                        <i class="<?= e($meta['icon']) ?>"></i>
                                    </span>
                                <?php else: ?>
                                    <span class="nav-icon">
                                        <i class="icon-file"></i>
                                    </span> 
                                <?php endif; ?>
                                <span class="nav-label">
                                    <?= e($meta['title'] ?? $filename) ?>
                                </span>
                                <span class="counter empty" data-menu-id="tailor/entry_supplier_info"></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </div>
        </div>
        <div id="layout-body" class="layout-container flex-grow-1 ">
            <div class="d-flex flex-column h-100">
                <div class="padded-container d-flex flex-column h-100">
                    <div class="documentation-content">
                        <?= $content ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
