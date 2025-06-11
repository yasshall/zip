<header class="admin-header">
    <div class="header-title">
        <h1><?= $pageTitle ?? 'Administration' ?></h1>
    </div>
    
    <div class="header-actions">
        <a href="../../index.html" target="_blank" class="btn btn-sm btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                <polyline points="15,3 21,3 21,9"/>
                <line x1="10" y1="14" x2="21" y2="3"/>
            </svg>
            Voir le Site
        </a>
        
        <a href="logout.php" class="user-menu">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16,17 21,12 16,7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Déconnexion
        </a>
    </div>
</header>