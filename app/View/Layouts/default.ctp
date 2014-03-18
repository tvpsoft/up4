<!DOCTYPE html>
<?php echo $this->Facebook->html(); //This is required for some of the facebook features to work in IE. ?>
<head>
    <?php echo $this->Html->charset(); ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <title>
        <?php echo $title_for_layout; ?>
    </title>
    <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <?php echo $this->Html->script('bootstrap'); ?>
    <?php echo $this->Facebook->init(); //To load the facebook javascript api to scan your page for fbxml and replace them with various dynamic content ?> 
    <?php
    echo $this->Html->meta('icon');
    echo $this->Html->css('bootstrap');
    echo $this->Html->css('normalize');
    echo $this->Html->css('up4');
    ?>
    <script>
        var myLatitide;
        var myLongitude;
        navigator.geolocation.getCurrentPosition(GetLocation);
        function GetLocation(location) {
            myLatitide = location.coords.latitude;
            myLongitude = location.coords.longitude;
        }
    </script>

</head>
<body>

    <div class="header">
        <div class="container">

            <div class="row">

                <?php echo $this->Html->link($this->Html->image('logo.jpg'), "/", array("escape" => false)) ?>

                
                    <?php
                    if (!empty($User)) {

                        echo '<span class="header-logout">'.$this->Facebook->logout(array('redirect' => array('controller' => 'users', 'action' => 'logout'), 'label' => __("Déconnexion", true))).'</span>';
                        echo '<span class="header-logout">'.$this->Facebook->picture($User["id"]).'</span>';
                    }
                    ?>
                

            </div>
        </div>
    </div>

    <div class="container">
        <?php echo $this->Session->flash(); ?>
        <?php echo $this->fetch('content'); ?>

        <div class="footer">
            <p>&copy; Up4 - 2014</p>
        </div>

    </div> <!-- /container -->

    <?php echo $this->element('sql_dump'); ?>
</body>
</html>
