<?php 
    require 'skt_s/functions.php'; 
    $skatek = new SkatekServer(__DIR__);
    $skatek->setExceptions(['xampp', 'webalizer']);
?>
<!DOCTYPE html>
<html>
	<head>
            <title>Skatek Corporation</title>
            <style>
                @import "skt_s/css/bootstrap.min.css";
                .btn {  margin-right: 3px; margin-bottom: 5px; }
                .jumbotron { text-align: center; padding-bottom: 15px;}
                .jumbotron h1 { text-transform: uppercase }
            </style>
	</head>
	<body class="container">
            <br>
        <div class="jumbotron">
            <h1>Skatek Corporation <small>Server</small></h1>
            <span class="label label-info"><?= $_SERVER['SERVER_ADDR'] ?></span>
            <span class="label label-warning"><?= $_SERVER['SERVER_SOFTWARE'] ?></span>
            <span class="label label-danger"><?= $skatek->getDate()?></span><hr>
            <a class="btn btn-primary" href="/">
                <span class="glyphicon glyphicon-home"></span>
                Accueil
            </a>
            <a class="btn btn-primary" href="<?= $skatek->getDblink() ?>" target="_blank">
                <span class="glyphicon glyphicon-dashboard"></span>
                Database Access
            </a>
            <a class="btn btn-primary" href="?href=phpinfo">
                <span class="glyphicon glyphicon-exclamation-sign"></span>
                Php-info
            </a>
        </div>
        <?php if(count($skatek->obtenirDirectories()) > 0):  ?>
        <div class="page-header">
            <h1>Repertoires <span class="badge alert-danger"><?= count($skatek->obtenirDirectories()) ?></span> </h1>
        </div>
            <div class="alert alert-info">
                <?php 
                foreach ($skatek->obtenirDirectories() as $dir): if ($dir['index']): ?>
                <span>
                    <a class="btn btn-default" target="_blank" href="<?= $dir['link'] ?>">
                        <i class="glyphicon glyphicon-folder-close"></i> <?= $dir['name'] ?>                        
                    </a>
                </span>
                <?php else: ?>
                <span>
                    <a class="btn btn-default" href="?dir=<?= $dir['link'] ?>">
                        <i class="glyphicon glyphicon-folder-open"></i> <?= $dir['name'] ?>                        
                    </a>
                </span>
                <?php
                    endif;
                endforeach;
                
                ?>
            </div>
            <?php endif; ?>
        <?php if(count($skatek->obtenirFiles()) > 0):  ?>
        <div class="page-header">
            <h1>Fichiers <span class="badge"><?= count($skatek->obtenirFiles()) ?></span></h1>
        </div>
            <div class="alert alert-info">
                <?php foreach($skatek->obtenirFiles() as $file): ?>
                <span>
                    <a class="btn btn-warning" target="_blank" href="<?= $file['link'] ?>"><?= $file['name'] ?></a>
                </span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php 
                switch ($skatek->getQuery('href')) {
                    case 'phpinfo':
                        phpinfo();
                        break;
                    case 'database':
                        $skatek->phpMyAdmin();
                        break;
                    
                    default:
                        # code...
                        break;
                }

            ?>

            <?php if(count($skatek->obtenirFiles()) < 1 && count($skatek->obtenirDirectories()) < 1 && !$skatek->getZero()): ?>
            <div class="alert alert-danger">
                Aucun fichier ou r&eacute;pertoire.
            </div>
            <?php endif; ?>
	</body>
        <div class="footer">
            <div class="container">
                <p class="text-muted">&copy; 2017 Skatek Corporation. Tous droits r&eacute;serv&eacute;s.</p>
            </div>
          </div>
</html>