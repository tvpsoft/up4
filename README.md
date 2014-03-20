UP4
=======

Network sharing the nearby events.


Query load events in 1 month

SELECT eid, start_time FROM event_member WHERE uid IN (SELECT uid2 FROM friend WHERE uid1=me()) and start_time > '2014-03-20' and start_time < '2014-04-20' 


SELECT eid, name, pic_cover, start_time, timezone, venue, all_members_count, attending_count FROM event WHERE eid IN (SELECT eid FROM event_member WHERE uid IN (SELECT uid2 FROM friend WHERE uid1=me())) and start_time > '2014-03-20' and start_time < '2014-04-20' 


SELECT eid FROM event_member WHERE uid IN (SELECT page_id FROM place WHERE distance(latitude, longitude, "37.76", "-122.427") < 1000)


 https://graph.facebook.com/search?q=*&type=place&center=37.76,-122.427&distance=1000


SELECT eid, name, pic_cover, start_time, timezone, venue, all_members_count, attending_count FROM event WHERE eid IN (SELECT eid FROM event_member WHERE uid IN (SELECT uid2 FROM friend WHERE uid1=me())) and start_time > '2014-03-20' and start_time < '2014-04-20' and venue.id IN (SELECT page_id FROM place WHERE distance(latitude, longitude, "48.856614", "2.352222") < 20000 )