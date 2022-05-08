<!DOCTYPE html>
<html lang="en">
<?php
    if (!isset($title)) {
        $browserTitle = 'Title';
    }
    if (!isset($theme)) {
        $theme = 'default';
    }
?>
<head>
    <meta charset="UTF-8">
    <?php
        printf('<link rel="stylesheet" type="text/css" href="%s/styles.css"></link>',$themePath ?? 'Error missing theme path');
        if (isset($extraSTyles)) {
            printf('<link rel="stylesheet" type="text/css" href="%s/extra.css"></link>',$themePath ?? 'Error missing theme path');
        }
    ?>
    <title>
        <?php print $title ?>
    </title>
    <script src="https://kit.fontawesome.com/e3f06c8db4.js" crossorigin="anonymous"></script>
    <?php if (isset($mvvm)) { ?>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/headjs/1.0.3/head.load.js" integrity="sha512-XDpsu7o5F1+SqCmdXgSfbx7yPA99X0IQs8RsbiQSrJ4kxOZSlbJtgCJjmVbLiAPKOhnffctq61O/VMlD88GcxA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.1/knockout-latest.js" integrity="sha512-2AL/VEauKkZqQU9BHgnv48OhXcJPx9vdzxN1JrKDVc4FPU/MEE/BZ6d9l0mP7VmvLsjtYwqiYQpDskK9dG8KBA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="/nutshell/pnut/core/PeanutLoader.js"></script>
    <?php } ?>
</head>
<body>
    <?php if ($mvvm) { ?>
        <!-- Peanut service messages container -->
        <!-- div class="container-fluid" id="peanut-messages" style="position:sticky" -->
        <div id="peanut-messages"  class="container-fluid"  style="position:fixed;top:0;z-index: 10000">
            <div class="row">
                <div class="col-md-12"   >
                    <div id="service-messages-container"><service-messages></service-messages></div>
                </div>
            </div>
        </div>
        <!-- end Peanut service messages -->
    <?php } ?>

    <?php
    if (isset($showSiteHeader) && isset($themeIncludePath)) {
        include $themeIncludePath."/site-header.php";
    }
    if (isset($pageTitle) && isset($themeIncludePath)) {
        include $themeIncludePath."/page-header.php";
    }
    ?>

    <!-- main content -->
    <div  id="nutshell-main-section">
        <div class="container" id="page-content">
        <?php if (isset($view)) {
            include $view;
        } ?>
        </div>
    </div> <!-- end main section -->

    <!-- todo: yagni - page footer -->

    <?php
    if (isset($showSiteFooter) && isset($themeIncludePath)) {
        include $themeIncludePath."/site-footer.php";
    }
    ?>

    <?php if (isset($pageVars)) {
        print "\n<form id='page-data'>\n";
        foreach ($pageVars as $key => $value) {
            printf("<input type='hidden' id='%s' name='%s' value='%s'>\n",$key,$key,$value);
        }
        print "</form>\n";
    }

    ?>
<!-- late loading scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<?php
    \Peanut\sys\ViewModelManager::RenderStartScript();
?>
</body>
</html>