<?php
/*  i am not a very great php coder. sorry for the messy code :) */
/* list of headers just incase another exploit shows up and adds it */

$hwidTypes = array("Syn-Fingerprint", "Exploit-Guid", "Proto-User-Identifier", "Sentinel-Fingerprint");
/* the reason i made a key for each exploit is to demonstrate the differences between the HWID for each exploit that supports a header */
/* i suggest that you clear this database or i will be able to use your script lol */

$database = array(
	array(
		"key" => "key_synapse",
		"hwid" => "fb78cf2dc8240577ae1819d36d87179fb073347d7f59092375e9e841087103d6d0799ae237c95112dd1e44308d7976fe536015ade95c407bc21ac8d8fbe5f8e4"
	)
);

function fetch_hwid($headers) {
	global $hwidTypes;

	$hwid = NULL;
	$count = 0;
	
	foreach($hwidTypes as $key => $header) {
		if (isset($headers[$header])) {
			$count = $count + 1;
			$hwid = $headers[$header];
		};
	}

	if ($count > 1) {
		/* prevent people from setting multiple of the headers to bypass checks :) */
		/* does this work?? */

		$hwid = NULL;
	}

	return $hwid;	
}

if (isset($_GET["key"])) {
	$hwid = fetch_hwid(getallheaders());
	if (!is_null($hwid)) {
		$didUserPass = FALSE;
		$errorMsg = "authentication failed";
		$key = $_GET["key"];

		foreach($database as $_ => $user) {
			$stored_key = $user["key"];
			$stored_hwid = $user["hwid"];
			$key_matches = ($stored_key == $key);
			$hwid_matches = ($stored_hwid == $hwid);

			if ($key_matches and $hwid_matches) {
				$didUserPass = TRUE;

				/* hey, they are whitelisted! give them the correct response */

				echo(hash("sha256", $stored_key));
			} elseif ($key_matches and !$hwid_matches) {
				/* hey, why does their hardware id not match but key does? NO SHARING ALLOWED!*/

				$errorMsg = "hwid mismatch";
			}
		}

		if (!$didUserPass) {
			echo($errorMsg);
		};
	} else {
		die("no hwid supplied");
	}
} else {
	die("invalid key");
}

?>
