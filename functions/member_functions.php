<?
$membersDir = "$BASE/_members";

/** Sort members by their last name */
function cmp($a, $b) {
	if ($a == $b) {
		return 0;
	}

	$p1 = strrpos($a, "_");
	$p2 = strrpos($b, "_");
	
	$lastName1 = substr($a, $p1);
	$lastName2 = substr($b, $p2);
	
    if ($lastName1 == $lastName2) {
        return ($a < $b) ? -1 : 1;
    }
    return ($lastName1 < $lastName2) ? -1 : 1;
}

/** Load all the members of this team into the $members array
 */
function loadMembers() {
    global $membersDir;
	
    if ($dh = opendir($membersDir)) {
        while (($file = readdir($dh)) !== false) {
            if (is_file("$membersDir/$file")) {
                include_once("$membersDir/$file");
            }
        }
        closedir($dh);
    }
    uksort($members, "cmp");
    return $members;
}

/** Load just one member into the global members array
 */
function loadMember(&$members, $memberKey) {
	global $membersDir;
	
	if (is_file("$membersDir/$memberKey.inc")) {
		include_once("$membersDir/$memberKey.inc");
	}
	return $members[$memberKey];
}

function getMemberKey($name) {
	return preg_replace("/ /", "_", $name);
}

/** Add a new team member to the list of members of the team
 */
function editMember(&$members, $name, $email, $phone, $admin = false) {
	global $membersDir;
	
	$key = getMemberKey($name);
	$members[$key] = array("name" => $name, "email" => $email, "phone" => $phone, "admin" => $admin);
	
	$filename = "$membersDir/$key.inc";
	$fh = fopen($filename, "w");
	fwrite($fh, "<" . "?\n" . "\$members['$key'] = array(");
	foreach ($members[$key] as $k => $v) {
		fwrite($fh, "'$k' => '$v',");
	}
	fwrite($fh, ");\n" . "?" . ">");
	fclose($fh);
	
	return $members[$key];
}

function deleteMember(&$members, $name) {
	global $membersDir;
	
	$key = getMemberKey($name);
	if (is_file("$membersDir/$key.inc")) {
		unlink("$membersDir/$key.inc");
	}
	unset($members[$key]);
}

function listMemberAsJson($member) {
	$out = "{\n";
	if (isset($member)) {
		$out .= "    key : '" . getMemberKey($member["name"]) . "',\n";
		$out .= "    name : '" . $member["name"] . "',\n";
		$out .= "    email : '" . $member["email"] . "',\n";
		$out .= "    phone : '" . $member["phone"] . "'\n";
		if (isset($member["admin"]) && $member["admin"]) {
			$out .= ",    admin : 1\n";
		}
	}
	$out .= "}";
	
	return $out;
}
?>