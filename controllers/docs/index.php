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
                        <?php foreach ($files as $filename => $parsed): ?>
                        <li class="mainmenu-item svg-icon-container <?= $filename === $selectedFile ? 'active' : '' ?>">
                            <a href="?file=<?= urlencode($filename) ?>" >
                                <?php if (!empty($parsed['meta']['icon'])): ?>
                                    <span class="nav-icon">
                                        <i class="<?= e($parsed['meta']['icon']) ?>"></i>
                                    </span>
                                <?php else: ?>
                                    <span class="nav-icon">
                                        <i class="icon-file"></i>
                                    </span> 
                                <?php endif; ?>
                                <span class="nav-label">
                                    <?= e($parsed['meta']['title'] ?? $filename) ?>
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
                    <div class="docs-content">
                    
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card shadow-sm bg-white p-5">
                                    <?= $content ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
