<?php

require_once __DIR__ . '/vendor/autoload.php';

$duc = new PDO("mysql:host=127.0.0.1;dbname=mig_ducati","username","password");
$gu  = new PDO("mysql:host=127.0.0.1;dbname=mig_gu",    "username","password");

$faker = Faker\Factory::create();

$type = strtolower($argv[1]);

$NUM_COMMON_TEAMS  = 20;
$NUM_OTHER_TEAMS   = 80;
$NUM_COMMON_USERS  = 280;
$NUM_OTHER_USERS   = 20;
$NUM_MATCHES       = 37000;
$MATCHES_PER_BATCH = 600;
$VISITS_PER_PLAYER = 50;
$NUM_NEWS          = 100;
$NUM_BANS          = 30;

if ($type == 'teams') {
    for ($i = 0; $i < $NUM_COMMON_TEAMS; $i++) {
        $name = $faker->unique()->company;
        $leader = $faker->unique()->numberBetween(2,$NUM_COMMON_USERS);

        $duc->prepare("INSERT INTO teams (name,leader_playerid) VALUES (?,?)")->execute([$name, $leader]);
        $gu->prepare( "INSERT INTO teams (name,leader_userid) VALUES (?,?)")->execute([$name, $leader]);

        echo "Added common team $i\n";
    }

    for ($i = 0; $i < $NUM_OTHER_TEAMS; $i++) {
        $name = $faker->unique()->company;
        $leader = $faker->unique()->numberBetween(2,$NUM_COMMON_USERS);

        $duc->prepare("INSERT INTO teams (name,leader_playerid) VALUES (?,?)")->execute([$name, $leader]);

        $name = $faker->unique()->company;
        $leader = $faker->unique()->numberBetween(2,$NUM_COMMON_USERS);
        $gu->prepare( "INSERT INTO teams (name,leader_userid) VALUES (?,?)")->execute([$name, $leader]);

        echo "Added other team $i\n";
    }
} elseif ($type == 'players') {
    for ($i = 0; $i < $NUM_COMMON_USERS; $i++) {
        $name = $faker->unique()->userName;
        $bzid = $faker->unique()->numberBetween($min = 180, $max = 55000);
        $team = $faker->optional($weight = 0.7)->numberBetween(2, $NUM_COMMON_TEAMS + $NUM_OTHER_TEAMS) ?: 0;

        $duc->prepare("INSERT INTO players(name,teamid,external_playerid) VALUES (?,?,?)")->execute([$name, $team, $bzid]);
        $gu->prepare("INSERT INTO players(name,teamid,external_id) VALUES (?,?,?)")->execute([$name, $team, $bzid]);

        echo "Added common player $i\n";
    }

    for ($i = 0; $i < $NUM_OTHER_USERS; $i++) {
        $name = $faker->unique()->userName;
        $bzid = $faker->unique()->numberBetween($min = 180, $max = 55000);
        $team = $faker->optional($weight = 0.8)->numberBetween(2, $NUM_COMMON_TEAMS + $NUM_OTHER_TEAMS) ?: 0;

        $duc->prepare("INSERT INTO players(name,teamid,external_playerid) VALUES (?,?,?)")->execute([$name, $team, $bzid]);

        $name = $faker->unique()->userName;
        $bzid = $faker->unique()->numberBetween($min = 180, $max = 55000);
        $team = $faker->optional($weight = 0.7)->numberBetween(2, $NUM_COMMON_TEAMS + $NUM_OTHER_TEAMS) ?: 0;
        $gu->prepare("INSERT INTO players(name,teamid,external_id) VALUES (?,?,?)")->execute([$name, $team, $bzid]);

        echo "Added other player $i\n";
    }
} elseif ($type == 'player_profiles') {
    for ($i = 0; $i < $NUM_COMMON_USERS; $i++) {
        $location = $faker->numberBetween(1,2);
        $user = implode("\n\n",$faker->paragraphs($nb = 2, $asText = false));
        $admin = $faker->sentences($nb = 2, $asText = true);

        $joined = $faker->dateTime;
        $lastlogin = $faker->dateTimeBetween('-2 years', 'now');

        $duc->prepare("INSERT INTO players_profile VALUES (null,?,?,1,?,?,?,?,?,?,null)")
            ->execute([$i+1, $location, $user, $user, $admin, $admin, ft($joined), ft($lastlogin)]);

        $gu->prepare("INSERT INTO players_profile VALUES (null,?,?,1,?,?,?,?,?,?,null)")
            ->execute([$i+1, $location, $user, $user, $admin, $admin, ft($joined), ft($lastlogin)]);

        echo "Added common player profile $i\n";
    }

    for ($i = 0; $i < $NUM_OTHER_USERS; $i++) {
        $location = $faker->numberBetween(1,2);
        $user = implode("\n\n",$faker->paragraphs($nb = 2, $asText = false));
        $admin = $faker->sentences($nb = 2, $asText = true);

        $joined = $faker->dateTime;
        $lastlogin = $faker->dateTimeBetween('-2 years', 'now');

        $duc->prepare("INSERT INTO players_profile VALUES (null,?,?,1,?,?,?,?,?,?,null)")
        ->execute([$i+$NUM_COMMON_USERS+1, $location, $user, $user, $admin, $admin, ft($joined), ft($lastlogin)]);

        $gu->prepare("INSERT INTO players_profile VALUES (null,?,?,1,?,?,?,?,?,?,null)")
            ->execute([$i+$NUM_COMMON_USERS+1, $location, $user, $user, $admin, $admin, ft($joined), ft($lastlogin)]);

        echo "Added other player profile $i\n";
    }
} elseif ($type == 'team_profiles') {
    for ($i = 0; $i < $NUM_COMMON_TEAMS+$NUM_OTHER_TEAMS; $i++) {
        $description = $faker->realText();
        $created = $faker->dateTime;
        $icon = 'http://helit.org/assets/imgs/bzflag_icon.png';

        $duc->prepare("INSERT INTO teams_profile VALUES (null, ?,0,0,0,?,?,?,?)")
            ->execute([$i+1, $description, $description, $icon, ft($created)]);

        $description = $faker->realText();
        $created = $faker->dateTime;

        $gu->prepare("INSERT INTO teams_profile VALUES (?,0,0,0,?,?,?,?)")
            ->execute([$i+1, $description, $description, $icon, ft($created)]);

        echo "Added team profile $i\n";
    }
} elseif ($type == 'team_overview') {
    for ($i = 0; $i < $NUM_COMMON_TEAMS+$NUM_OTHER_TEAMS; $i++) {
        $score = $faker->numberBetween(1000,1400);
        $activityNew = $faker->randomFloat(2, $min = 0, $max = 2);
        $activityOld = $faker->randomFloat(2, $min = 0, $max = 2);
        $open = $faker->boolean(20);

        $duc->prepare("INSERT INTO teams_overview VALUES (null, ?,?,0,?,2,?, 0)")
            ->execute([$i+1, $score, $activityNew, $open]);

        $score = $faker->numberBetween(1000,1400);
        $activityNew = $faker->randomFloat(2, $min = 0, $max = 2);
        $activityOld = $faker->randomFloat(2, $min = 0, $max = 2);
        $open = $faker->boolean(20);

        $gu->prepare("INSERT INTO teams_overview VALUES (?,?,0,?,?,2,?, 0)")
            ->execute([$i+1, $score, $activityNew, $activityOld, $open]);

        echo "Added team overview $i\n";
    }
} elseif ($type == 'matches') {
    for ($i = 0; $i < $NUM_MATCHES; $i += $MATCHES_PER_BATCH) {
        $gum = $ducm = [];
        for ($j = 0; $j < 2*$MATCHES_PER_BATCH; $j++) {
            $team1 = $faker->unique($reset = true)->numberBetween(2, $NUM_COMMON_TEAMS+$NUM_OTHER_TEAMS);
            $team2 = $faker->unique()->numberBetween(2, $NUM_COMMON_TEAMS+$NUM_OTHER_TEAMS);

            $date = $faker->dateTime;

            $points1 = $faker->biasedNumberBetween(0,10,'bias');
            $points2 = $faker->biasedNumberBetween(0,10,'bias');

            if ($j % 2 == 0) {
                $gum[] = [$team1, $team2, $date, $points1, $points2];
            } else {
                $ducm[] = [$team1, $team2, $date, $points1, $points2];
            }
        }

        $query = "INSERT INTO matches VALUES " . str_repeat("(null,1,?,?,?,?,?,1400,1400),", $MATCHES_PER_BATCH);
        $query = rtrim($query, ',');

        $params = [];
        foreach ($gum as $m) {
            $params = array_merge($params,[ft($m[2]), $m[0], $m[1], $m[3], $m[4]]);
        }
        $gu->prepare($query)->execute($params);

        $params = [];
        foreach ($ducm as $m) {
            $params = array_merge($params,[ft($m[2]), $m[0], $m[1], $m[3], $m[4]]);
        }
        $duc->prepare($query)->execute($params);

        echo "Added match $i\n";
    }
} elseif ($type == 'visits') {
    $count = $VISITS_PER_PLAYER + $faker->numberBetween(-10,10);

    for ($i = 0; $i < $NUM_COMMON_USERS + $NUM_OTHER_USERS; $i++) {
        $gum = $ducm = [];
        for ($j = 0; $j < 2*$count; $j++) {
            $ip = $faker->ipv4;
            $host = $faker->domainName;
            $ff = $faker->url;
            $date = $faker->dateTimeBetween('-4 years', 'now');

            if ($j % 2 == 0) {
                $gum[] = [$ip, $host, $ff, $date];
            } else {
                $ducm[] = [$ip, $host, $ff, $date];
            }
        }

        $query = "INSERT INTO visits VALUES " . str_repeat("(null,?,?,?,?,?),", $count);
        $query = rtrim($query, ',');

        $params = [];
        foreach ($gum as $m) {
            $params = array_merge($params,[$i+1, $m[0], $m[1], $m[2], ft($m[3])]);
        }
        $gu->prepare($query)->execute($params);

        $params = [];
        foreach ($ducm as $m) {
            $params = array_merge($params,[$i+1, $m[0], $m[1], $m[2], ft($m[3])]);
        }
        $duc->prepare($query)->execute($params);

        echo "Added visits for $i\n";
    }
} elseif ($type == 'news') {
    for ($i = 0; $i < $NUM_NEWS; $i++) {
        $title = $faker->sentence();
        $author = $faker->numberBetween(2, $NUM_COMMON_USERS + $NUM_OTHER_USERS);
        $content = $faker->realText(800);
        $alias = $faker->unique()->slug;
        $time = $faker->dateTime;

        $gu->prepare("INSERT INTO newssystem VALUES (null,?,?,?,?,?,?)")
            ->execute([ $title, ft($time), $author, $content, $content, $alias ]);

        $title = $faker->sentence();
        $author = $faker->numberBetween(2, $NUM_COMMON_USERS + $NUM_OTHER_USERS);
        $content = $faker->realText(800);
//        $alias = $faker->unique()->domainWord;
        $time = $faker->dateTime;

        $duc->prepare("INSERT INTO news VALUES (null,?,?,?,?)")
            ->execute([ ft($time), $author, $content, $content ]);

        echo "Added news article $i\n";
    }
} elseif ($type == 'bans') {
    for ($i = 0; $i < $NUM_BANS; $i++) {
        $time = $faker->dateTime;
        $author = $faker->numberBetween(2, $NUM_COMMON_USERS + $NUM_OTHER_USERS);
        $content = $faker->userName . " has been banned because they were " .
            "cheating at " . $faker->time('g:i a') . " on " .$faker->dayOfWeek . ".\n\n" .
            "It is suspected that they were using the " . $faker->userAgent .
            " cheat, which utilises ." . $faker->fileExtension . " files to generate " .
            "a stream of invalid data sent to " . $faker->country . ", under the fake " .
            "name of \"" . $faker->name . "\", posing as methods to \"" .
            $faker->bs ."\".";

        $duc->prepare("INSERT INTO bans VALUES (null,?,?,?,?)")
            ->execute([ ft($time), $author, $content, $content ]);

        echo "Added ban $i\n";
    }
} else {
    echo "Unknown type.";
}

echo "\n";

/**
 * Format a date for MySQL
 *
 * @param DateTime $t
 *
 * @return string
 */
function ft(DateTime $t) {
    return $t->format("Y-m-d H:i:s");
}

function bias($i) {
    return pow($i,1/4);
}
