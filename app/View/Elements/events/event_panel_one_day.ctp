<div class="panel panel-default accordion-caret">
    <div class="panel-heading">
        <a <?= $firstOpen ? "id='firstEventDate'" : '' ?> class="panel-title" data-toggle="collapse" onclick="javascript:event_load('<?= $event_date ?>')" data-parent="panel-event-list" href="#panel-element-<?= $event_date ?>" ><?= (date("Y-m-d", strtotime($event_date)) == date("Y-m-d", strtotime("now"))) ? __("Aujourd'hui") : strftime("%A %d %B", strtotime($event_date)) ?> 
            <span class="badgeBox pull-right"><span class="badge"><?= $total_events ?></span></span>
        </a>
    </div>
    <div id="panel-element-<?= $event_date ?>" class="panel-collapse collapse " >
        <div class="panel-body">
            <div id="panel-body-<?= $event_date ?>" class="row clearfix">
                


            </div>
        </div>
    </div>
</div>
