<?php

App::uses('AppController', 'Controller');
App::uses('FB', 'Facebook.Lib');

class EventsController extends AppController {

    public $uses = array('Event', 'Venue');

    public function index($isAll = NULL) {

        $this->Event->recursive = -1;
        $sql_event_date = "DATE(`start_time`)"; //TODO: paging the next event date

        if ($isAll == "all") {
            $conditions = array($sql_event_date . " >= CURRENT_DATE()");
        } else {
            $conditions = array($sql_event_date . " >= CURRENT_DATE()", "center_distance <= " => 21);
        }
        $nextEventDate = $this->Event->find("all", array("fields" => array($sql_event_date . " AS event_date", "COUNT(*) as total_events"), "conditions" => $conditions, "order" => array($sql_event_date), "group" => array("event_date")));
        $this->set("nextEventDate", $nextEventDate);

        $statTotal = $this->Event->find("all", array("fields" => array("Count(*) as total_events", "Max(`start_time`) as max_date", "Min(`start_time`) as min_date")));
        $this->set("statTotal", $statTotal);
        $this->set("isAll", $isAll);


        //$date = new DateTime("2014-03-08T19:00:00+0100", new DateTimeZone("Europe/Paris"));
        //debug($date);
        //Get all the details on the facebook user
        //$this->set('facebookUser', $this->Connect->user());
        //retrieve only the id from the facebook user
        //$this->set('facebook_id', $this->Connect->user('id'));
        //retrieve only the email from the facebook user
        //$this->set('facebook_email', $this->Connect->user('email'));
    }

    public function load_events($firstLoad = NULL, $myLatitude = NULL, $myLongitude = NULL) {
        $this->layout = 'ajax';
        try {
            $Facebook = new FB();
            $strFQL = "SELECT eid, name, location, pic_cover, start_time, end_time, update_time, timezone, venue, all_members_count, attending_count FROM event WHERE eid IN (SELECT eid FROM event_member WHERE uid IN (SELECT uid2 FROM friend WHERE uid1=me())) and start_time > '" . date("Y-m-d") . "' and start_time < '" . date("Y-m-d", strtotime("+1 month")) . "'";
            $strFQL = str_replace(" ", "+", $strFQL);
            $this->log($strFQL, "load_events_fql" . $this->Connect->user('id'));
            //$fql = $Facebook->api('/' . $this->Connect->user('id') . '?fields=id,events.fields(id,start_time,timezone,location,cover,name,venue),friends.fields(events.fields(id,start_time,timezone,location,cover,name,venue))', 'GET');
            $fql = $Facebook->api("/fql?q=" . $strFQL);
            $this->log($fql, "load_events_fql" . $this->Connect->user('id'));
        } catch (FacebookApiException $e) {
            $this->log($e->getType(), "load_events_error_fql" . $this->Connect->user('id'));
            $this->log($e->getMessage(), "load_events_error_fql" . $this->Connect->user('id'));
            return;
        }

        $arrEvent = array();
        $arrVenue = array();

        if (!empty($fql["data"])) {
            foreach ($fql["data"] as $event) {
                if (!empty($event["eid"])) {
                    $event["id"] = $event["eid"];
                    if (!empty($event["timezone"])) {
                        $eventDate = new DateTime($event["start_time"], new DateTimeZone($event["timezone"]));
                    } else {
                        $eventDate = new DateTime($event["start_time"]);
                    }
                    $up4TimeZone = new DateTimeZone(Configure::read("Up4.TimeZone"));
                    $eventDate->setTimezone($up4TimeZone); //Change to server's TimeZone

                    $event["start_time"] = $eventDate->format("Y-m-d H:i:s");
                    $event["cover"] = !empty($event["pic_cover"]) ? $event["pic_cover"]["source"] : "";

                    if (!empty($event["venue"]["id"])) {
                        $event["venue_id"] = $event["venue"]["id"];
                        $event["center_distance"] = $this->getDistance($event["venue"]["latitude"], $event["venue"]["longitude"], "48.856614", "2.352222", "K");
                        array_push($arrVenue, $event["venue"]);
                        unset($event["venue"]);
                    }
                    array_push($arrEvent, $event);
                }
            }
        }

        if ($this->Event->saveAll($arrEvent) && $this->Venue->saveAll($arrVenue)) {
            
        } else {
            //TODO: Show error and log the debug for can not save the events
        }

        if ($firstLoad == "firstLoad") {
            $this->redirect("/events/index");
        }

        $result = $fql;
        $this->set("result", $result);
    }

    public function events_by_day($event_date = NULL, $myLatitude = NULL, $myLongitude = NULL, $isAll = NULL) {
        $this->layout = 'ajax';
        if (!empty($event_date)) {
            $sql_event_date = "DATE(`start_time`)"; //TODO: paging the next event date
            if ($isAll == "all") {
                $conditions = array($sql_event_date . " = DATE('$event_date')");
            } else {
                $conditions = array($sql_event_date . " = DATE('$event_date')", "center_distance <= " => 21);
            }
            $this->Event->recursive = 2;
            $todayEvents = $this->Event->find("all", array("conditions" => $conditions, "order" => "start_time"));

            $strEventId = "(";
            foreach ($todayEvents as $event) {
                $strEventId = $strEventId . $event["Event"]["id"] . ",";
            }
            if (substr($strEventId, -1) == ",") {
                $strEventId = substr($strEventId, 0, -1) . ")";
            } else {
                $strEventId = "()";
            }
            try {
                $Facebook = new FB();
                //Find friend join the event
                $strFQL2 = str_replace(" ", "+", "SELECT eid,uid FROM event_member WHERE eid IN " . $strEventId . " AND rsvp_status = 'attending' AND uid IN (SELECT uid2 FROM friend WHERE uid1 = me())");

                $fql = $Facebook->api("/fql?q={\"amis\":\"" . $strFQL2 . "\"}");
                //debug($fql);
            } catch (FacebookApiException $e) {
                $this->log($e->getType(), "load_events_error_fql" . $this->Connect->user('id'));
                $this->log($e->getMessage(), "load_events_error_fql" . $this->Connect->user('id'));
                return;
            }
            $arrNextEvents = array();
            foreach ($todayEvents as $event) {
                $amisCount = 0;
                $arrAmis = array();
                if (!empty($fql["data"][0]["fql_result_set"])) {
                    foreach ($fql["data"][0]["fql_result_set"] as $amis) {
                        if ($amis["eid"] == $event["Event"]["id"]) {
                            array_push($arrAmis, $amis["uid"]);
                            $amisCount++;
                        }
                    }
                    $event["Event"]["attending_friends"] = $amisCount;
                    $event["Amis"] = $arrAmis;
                }
                if (!empty($event["Venue"]["id"]) && $myLongitude != "undefined") {
                    $event["Event"]["distance"] = number_format($this->getDistance($event["Venue"]["latitude"], $event["Venue"]["longitude"], $myLatitude, $myLongitude, "K"), 2, ',', ' ');
                } else {
                    $event["Event"]["distance"] = "--";
                }
                array_push($arrNextEvents, $event);
            }

            $this->set("todayEvents", $arrNextEvents);
        }
    }

    function getDistance($lat1, $lon1, $lat2, $lon2, $unit) {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

}

?>