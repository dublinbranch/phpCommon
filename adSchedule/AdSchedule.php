<?php

class AdSchedule
{
    public function makeAdScheduleTable(string $id = "mainSchedule", ?DBWrapper $db = null, ?string $remoteId = null): string
    {
        $res = array();
        if (!is_null($db) && !is_null($remoteId)) {
            $sql = <<<MYSQL
SELECT
    ads.*,
    ads.day_of_week + 0 as day_of_week
FROM
    turboProp.adSchedule as ads
WHERE
    ads.remoteId = {$remoteId}
MYSQL;

            $res = $db->getAllObj($sql);
        }

        $table = <<<HTML
<table id="{$id}" class="adScheduleTable">
    <tbody>
HTML;

        //start with monday
        for ($day_of_week = 2; $day_of_week < 8; $day_of_week++) {
            $table .= $this->scanLine($res, $day_of_week);
        }
        //sunday at the end
        $table .= $this->scanLine($res, 1);
        $table .= <<<HTML
    </tbody> 
</table>
<input type='hidden' name='{$id}_schedule' class='{$id}_schedule'>
<script>
let {$id}_interval;
{$id}_interval = setInterval( () => {
    if(typeof Scheduler === 'function'){
        clearInterval({$id}_interval);
        adSchedulersList.{$id}_scheduler = new AdSchedule('{$id}');
    }
},1000);
</script>
HTML;
        return $table;
    }

    private function scanLine($res, $day_of_week): string
    {
        $line = "\n<tr>";
        for ($hour = 0; $hour < 24; $hour++) {
            $line .= "\n<td";
            $in = $this->inRange($res, $day_of_week, $hour);
            if ($in) {
                $line .= " class='active' ";
            } else {
                $line .= " class='paused' ";
            }

            $line .= ">";
            if ($hour == 0) {
                $line .= $this->unroller($day_of_week);
            } else if ($hour % 3 == 0) {
                $line .= $hour;
            }
            $line .= "</td>";
        }
        $line .= "\n</tr>";
        return $line;
    }

    private function unroller($date): string
    {
        switch ($date) {
            case 1:
                return "Sunday";
            case 2:
                return "Monday";
            case 3:
                return "Tuesday";
            case 4:
                return "Wednesday";
            case 5:
                return "Thursday";
            case 6:
                return "Friday";
            case 7:
                return "Saturday";
            default:
                throw new Exception("{$date} is not in the range 1-7");
        }
    }

    private function inRange($adSchedules, string $day_of_week, int $hour): bool
    {
        foreach ($adSchedules as $adSchedule) {
            if ($adSchedule->day_of_week == $day_of_week) {
                if ($hour >= $adSchedule->start_hour && $hour <= $adSchedule->end_hour) {
                    return true;
                }
            }
        }
        return false;
    }

    private function roller($json)
    {
        /*global $dbConfs7 , $cidQuery , $comparisonid , $comparisonTable , $devMode;
        $db = new DBWrapper($dbConfs7);
        $campaignId = $db->getLine( $cidQuery )->cId;
        $gdnCampaignId = $db->getLineSS("select remoteParentId from turboProp.campaigns WHERE id = $campaignId")->remoteParentId;

        $sqlBuffer = false;*/
        $arr = json_decode($json);
        foreach ($arr as $day => $hours) {
            if ($day == 0) {
                continue; //first one is not set
            }
            //add a dummy value at the end, so the algorithm is much easier
            $hours[] = false;
            $active = false;
            $start_hour = 0;
            //scan all the hour to find disconnection point
            foreach ($hours as $key => $hour) {
                if ($hour == true) { //If the hour is active
                    if ($active) {
                        //do nothing
                    } else {
                        $active = true; //start a new range
                        $start_hour = $key;
                    }
                } else { //if the hour is inactive
                    if ($active) {//if we are at the end of an active range
                        $end_hour = $key - 1;
                        $campaignId = 123;
                        $sql = <<<EOD
INSERT INTO turboProp.gdnAdSchedule SET
gdnCampaignId = $gdnCampaignId
,day_of_week = $day
,start_hour = $start_hour
,start_minute = 1 
,end_hour = $end_hour
,end_minute = 1
;
EOD;
                        $sqlBuffer[] = $sql;
                        $sqlBuffer[] = str_replace("turboProp","turboPropTemp",$sql);
                    }
                    $active = false; //terminate range
                }
            }

        }


        /*$delete = "DELETE FROM turboProp.gdnAdSchedule WHERE gdnCampaignId = $gdnCampaignId;";
        $db->query($delete);
        $delete = "DELETE FROM turboPropTemp.gdnAdSchedule WHERE gdnCampaignId = $gdnCampaignId;";
        $db->query($delete);
        foreach ($sqlBuffer as $query) {
            $db->query($query);
        }

        if( ! $devMode ){
            $url = "http://127.0.0.1:9025/?comparisonId={$comparisonid}&action=campaignAdSchedule&comparisonTable={$comparisonTable}";
            $result = file_get_contents( $url );
        }else{
            $result = new Stdclass();
            $result->status = 'ok';
            $result = json_encode( $result );
        }
        die( $result );*/
    }

}
