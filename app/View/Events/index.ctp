<div class="row clearfix">
    <div class="col-md-8 column">
        <h4><?php echo __("Rejoindre un RDV à proximité"); ?></h4>
        <h5>Total de <?= $statTotal[0][0]["total_events"] ?> événements de <?= strftime("%A %d %B %G", strtotime($statTotal[0][0]["min_date"])) ?> à <?= strftime("%A %d %B %G", strtotime($statTotal[0][0]["max_date"])) ?></h5>
        <?= $this->Form->hidden("isAll", array("value" => $isAll)) ?>
    </div>
    <div class="col-md-4 column text-right">
        <input type="checkbox" value="<?= $isAll ?>" id="checkFilter" <?= ($isAll != "all") ? "checked" : "" ?> ><?= __(" 20 km du centre de Paris"); ?>
        <button class="btn btn-primary" id='btnRefresh'><?php echo __("Refresh") ?></button>
    </div>
</div>

<!-- Loading Panel -->
<div class="loadingG">
    <span id="loadingG_1">
    </span>
    <span id="loadingG_2">
    </span>
    <span id="loadingG_3">
    </span>
</div>


<div id="list_events"></div>


<div class="row clearfix">
    <div class="col-md-12 column">

        <div class="panel-group" id="panel-event-list">
            <?php
            $firstOpen = true;
            foreach ($nextEventDate as $eventDate) {
                echo $this->element("events/event_panel_one_day", array("event_date" => $eventDate[0]["event_date"], "total_events" => $eventDate[0]["total_events"], "firstOpen" => $firstOpen));
                $firstOpen = false;
            }
            ?>
        </div>


    </div>
</div>



<script>

    $("#checkFilter").click(function() {
        var filter = "all";
        if ($("#checkFilter").val() === "all") {
            filter = "";
        }
        location.replace("/up4/events/index/" + filter);
    });
    $("#btnRefresh").click(
            function()
            {
                $(".loadingG").show();
                //Update location
                navigator.geolocation.getCurrentPosition(GetLocation);
                function GetLocation(location) {
                    myLatitide = location.coords.latitude;
                    myLongitude = location.coords.longitude;
                }
                $.ajax({
                    url: "<?php echo Router::Url(array('controller' => 'events', 'action' => 'load_events'), TRUE); ?>" + "/false/" + myLatitide + "/" + myLongitude,
                    context: document.body
                }).done(function(e) {
                    $('#list_events').html(e);
                    $(".loadingG").hide();
                    location.reload();
                });
                return false;
            }
    );
    function event_load(event_day)
    {
        $(".loadingG").show();
        $.ajax({
            url: "<?php echo Router::Url(array('controller' => 'events', 'action' => 'events_by_day/'), TRUE); ?>" + "/" + event_day + "/" + myLatitide + "/" + myLongitude + "/" + $("#checkFilter").val(),
            context: document.body
        }).done(function(e) {
            $('#panel-body-' + event_day).html(e);
            $(".loadingG").hide();
        });

        return false;
    }

    $(window).bind("load", function() {
        $("#firstEventDate").click();
    });

</script>