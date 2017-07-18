#!/usr/bin/env php
CREATE TABLE `log3` (
`id` int(10) unsigned NOT NULL auto_increment,
`repo` varchar(255) NOT NULL,
`commit` varchar(40) NOT NULL,
`date` datetime NOT NULL,
`message` text NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `commit` (`commit`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
<?php
date_default_timezone_set('Asia/Kolkata');
exec('pwd',$pwd);
$repo = rtrim(array_shift($pwd),'/');
$repo = substr($repo,strrpos($repo,'/') + 1);
$db = new PDO('mysql:dbname=git;host=localhost','git','binfex123');
exec('git log --all --pretty=format:"%H%n%ct%n%s%n%b%n<><><>" --since="1 week ago"',$capture,$log);
if ($capture){
    // preprocess the log
    $commits = array();
    $current = array();
    foreach ($capture as $row){
        if (trim($row) === '<><><>') {
            $commits[] = $current;
            $current = array();
        } else {
            $current[] = $row;
        }
    }
    $v = array();
    $b = array();
    foreach ($commits as $commit){
        $sha = $commit[0];
        $m = $commit[2] . (trim($commit[3]) === '' ? '' : "\n\n" . implode("\n",array_slice($commit,3)));
        $d = date('Y-m-d H:i:s',$commit[1]);
        $v[] = '(?,?,?,?)';
        $b[] = $repo;
        $b[] = $sha;
        $b[] = $m;
        $b[] = $d;
    }
    $stmt = $db->prepare('insert ignore into log3 (repo,commit,message,`date`) values' . implode(',',$v));
    try {
        if ($stmt) {
            if (!$stmt->execute($b)) throw new PDOException;;
        } else Throw new PDOException;
    } catch (PDOException $e) {
        mail('EMAIL','Commit did not reach db',$e->getMessage());
    }
}
?>
