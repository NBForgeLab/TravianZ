<?php
// Prefer Twig when available, fallback to legacy markup otherwise
if (class_exists('\Twig\Environment')) {
    if (!class_exists('\App\View\TwigFactory')) {
        require_once dirname(__DIR__) . '/autoloader.php';
    }
    $projectRoot = dirname(__DIR__);
    $serverName = defined('SERVER_NAME') ? SERVER_NAME : 'TravianZ';
    \App\View\TwigFactory::get($projectRoot)->display('footer.twig', [
        'serverName' => $serverName,
        'year' => date('Y'),
    ]);
    return;
}
?>

<div id="footer">
    <div id="mfoot">
        <div class="footer-menu">
            <center><br />
            <div class="copyright">&copy; 2010 - <?php echo date('Y') . ' ' . (defined('SERVER_NAME') ? SERVER_NAME : 'TravianZ');?> All rights reserved</div>
            <div class="copyright">Server running on: <a href="version.php"><b><font color="Red">v.8.3.5</font></b></a>
            </div>
        </div>
    </div></center>
    <div id="cfoot">
    </div>
</div>
