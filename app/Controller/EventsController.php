<?php

App::uses('AppController', 'Controller');
App::uses('FB', 'Facebook.Lib');

class EventsController extends AppController {

    public $uses = array('Event', 'Venue');

    public function index() {

        $this->Event->recursive = -1;
        $sql_event_date = "DATE(`start_time`)"; //TODO: paging the next event date
        $nextEventDate = $this->Event->find("all", array("fields" => array($sql_event_date . " AS event_date", "COUNT(*) as total_events"), "conditions" => array($sql_event_date . " >= CURRENT_DATE()"), "order" => array($sql_event_date), "group" => array("event_date")));
        $this->set("nextEventDate", $nextEventDate);
        
        $statTotal = $this->Event->find("all", array("fields" => array("Count(*) as total_events","Max(`start_time`) as max_date","Min(`start_time`) as min_date")));
        $this->set("statTotal", $statTotal);;

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

        $Facebook = new FB();
        $fql = $Facebook->api('/' . $this->Connect->user('id') . '?fields=id,events.fields(id,start_time,timezone,location,cover,name,venue),friends.fields(events.fields(id,start_time,timezone,location,cover,name,venue))');

        $arrEvent = array();
        $arrVenue = array();
        foreach ($fql as $setEvents) {
            if (!empty($setEvents["data"])) {
                foreach ($setEvents["data"] as $data) {
                    if (!empty($data["events"])) {
                        foreach ($data["events"] as $events) {
                            foreach ($events as $event) {
                                if (!empty($event["id"])) {
                                    if (!empty($event["timezone"])) {
                                        $eventDate = new DateTime($event["start_time"], new DateTimeZone($event["timezone"]));
                                    } else {
                                        $eventDate = new DateTime($event["start_time"]);
                                    }
                                    $up4TimeZone = new DateTimeZone(Configure::read("Up4.TimeZone"));
                                    $eventDate->setTimezone($up4TimeZone); //Change to server's TimeZone
                                    if ($eventDate->format("Y-m-d H:i:s") >= date("Y-m-d")) { //Only save the next events from today
                                        $event["start_time"] = $eventDate->format("Y-m-d H:i:s");
                                        $event["cover"] = !empty($event["cover"]) ? $event["cover"]["source"] : "";
                                        if (!empty($event["venue"]["id"])) {
                                            $event["venue_id"] = $event["venue"]["id"];
                                            array_push($arrVenue, $event["venue"]);
                                            unset($event["venue"]);
                                        }
                                        array_push($arrEvent, $event);
                                    }
                                }
                            }
                        }
                    }
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

    public function events_by_day($event_date = NULL, $myLatitude = NULL, $myLongitude = NULL) {
        $this->layout = 'ajax';
        if (!empty($event_date)) {
            $sql_event_date = "DATE(`start_time`)"; //TODO: paging the next event date
            $this->Event->recursive = 2;
            $todayEvents = $this->Event->find("all", array("conditions" => array($sql_event_date . " = DATE('$event_date')"), "order" => "start_time"));

            $strEventId = "(";
            foreach ($todayEvents as $event) {
                $strEventId = $strEventId . $event["Event"]["id"] . ",";
            }
            if (substr($strEventId, -1) == ",") {
                $strEventId = substr($strEventId, 0, -1) . ")";
            } else {
                $strEventId = "()";
            }

            //Find attending
            $Facebook = new FB();
            $strFQL = str_replace(" ", "+", "select eid,all_members_count, attending_count FROM event where eid IN " . $strEventId);
            $fqlAttendng = $Facebook->api("/fql?q=" . $strFQL);

            //Find friend join the event
            $strFQL = str_replace(" ", "+", "SELECT eid,uid FROM event_member WHERE eid IN " . $strEventId . " AND rsvp_status = 'attending' AND uid IN (SELECT uid2 FROM friend WHERE uid1 = me())");
            $fql = $Facebook->api("/fql?q=" . $strFQL);

            $arrNextEvents = array();
            foreach ($todayEvents as $event) {
                $amisCount = 0;
                $arrAmis = array();
                foreach ($fql["data"] as $amis) {
                    if ($amis["eid"] == $event["Event"]["id"]) {
                        array_push($arrAmis, $amis["uid"]);
                        $amisCount++;
                    }
                }
                $event["Event"]["attending_friends"] = $amisCount;
                $event["Amis"] = $arrAmis;

                foreach ($fqlAttendng["data"] as $eventAttending) {
                    if ($eventAttending["eid"] == $event["Event"]["id"]) {
                        $event["Attending"] = $eventAttending;
                    }
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