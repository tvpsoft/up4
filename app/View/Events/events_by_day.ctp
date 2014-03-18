<?php
foreach ($todayEvents as $event) {
    ?>

    <div class="col-xs-6 col-md-3">
        <div class="thumbnail">
            <div class="event_thumb">
                <img alt="<?= $event["Event"]["name"] ?>" class="scale" src="<?= !empty($event["Event"]["cover"]) ? $event["Event"]["cover"] : $this->Html->url('/img/thumb_default.png') ?>" />
            </div>
            <div class="caption">
                <div class="event_title"><?= $this->Html->link($event["Event"]["name"],"http://www.facebook.com/event.php?eid=".$event["Event"]["id"],array("target"=>"blank")) ?></div>
                <div class="event_time"><?= date("H\Hi", strtotime($event["Event"]["start_time"])) ?></div>
                <div class="event_param">
                    <?= !empty($event["Attending"])?$event["Attending"]["attending_count"]:"-" ?>/<?= !empty($event["Attending"])?$event["Attending"]["all_members_count"]:"-"  ?> 
                    <?php if ($event["Event"]["attending_friends"] > 0) {
                        ?>
                        <a id="modal-<?= $event["Event"]["id"] ?>" href="#modal-container-<?= $event["Event"]["id"] ?>" role="button" data-toggle="modal">(<?= $event["Event"]["attending_friends"] ?> amis)</a>
                    <?php } else { ?>
                        (<?= $event["Event"]["attending_friends"] ?> amis)
                    <?php } ?>
                    <br/>
                    @<?= $event["Event"]["location"] ?></div>
                <p><span class="event_distance label label-success"> <?= $event["Event"]["distance"] ?> km  </span></p>
            </div>
        </div>
    </div>
<?php } ?>

<?php
foreach ($todayEvents as $event) {
    if ($event["Event"]["attending_friends"] > 0) {
        ?>

        <div class="modal fade" id="modal-container-<?= $event["Event"]["id"] ?>" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="myModalLabel">
                            <?= __("Mes amis à "); ?><?= $event["Event"]["name"] ?>
                        </h4>
                    </div>
                    <div class="modal-body">

                        <?php
                        foreach ($event["Amis"] as $amis) {
                            echo $this->Html->link($this->Html->image("http://graph.facebook.com/" . $amis . "/picture"), "http://facebook.com/profile.php?id=" . $amis, array("escape" => false, 'target' => '_blank'));
                        }
                        ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>
