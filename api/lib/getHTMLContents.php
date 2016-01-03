<?php
	include("{$_SERVER['DOCUMENT_ROOT']}/api/vendor/dom_parser.php");

	function getHTML ($url, &$arr) {
		$m_url = $url;

		$m_html = file_get_html ($m_url);

		$property = "";
		$no_bed = get_no_bed ($m_html);
		$no_bath = get_no_bath ($m_html);
		$no_car = get_no_car ($m_html);
		$address = get_address ($m_html);
		$agency = get_agency ($m_html);
		$agency_localDir = get_agency_localDir($m_html);
		$first_agent_name = get_first_agent_name ($m_html);
		$first_agent_contact = get_first_agent_contact ($m_html);
		$listing_type = "";
		$price = get_price ($m_html);
		$inspect_time = get_inspect_time ($m_html);
		$inspect_date = get_inspect_date ($m_html);
		$inspect_hour = get_inspect_hour ($m_html);
		$auction_time = get_auction_time ($m_html);
		$auction_date = get_auction_date ($m_html);
		$auction_hour = get_auction_hour ($m_html);
		$auction_inspect_hour = get_auction_inspect_hour ($m_html);

		$arr = array (
			'url' => $m_url,
			'no_bed' => $no_bed,
			'no_bath' => $no_bath,
			'no_car' => $no_car,
			'address' => $address,
			'agency' => $agency,
			'agency_localDir' => $agency_localDir,
			'first_agent_name' => $first_agent_name,
			'first_agent_contact' => $first_agent_contact,
			'listing_type' => $listing_type,
			'price' => $price,
			'inspect_time' => $inspect_time,
			'inspect_date' => $inspect_date,
			'inspect_hour' => $inspect_hour,
			'auction_time' => $auction_time,
			'auction_date' => $auction_date,
			'auction_hour' => $auction_hour,
			'auction_inspect_hour' => $auction_inspect_hour
		);

		//print_r ($arr);
	}

	function get_no_bed (&$html) {
		$no_bed = $html->find ('.rui-icon-bed', 0)->next_sibling()->plaintext;

		return $no_bed;
	}

	function get_no_bath (&$html) {
		$no_bath = $html->find ('.rui-icon-bath', 0)->next_sibling()->plaintext;
		
		return $no_bath;
	}

	function get_no_car (&$html) {
		$no_car = $html->find ('.rui-icon-car', 0);
		if (is_null ($no_car))
			return "N/A";

		$no_car = $no_car->next_sibling()->plaintext;
		
		return $no_car;
	}

	function get_address (&$html) {
		$addressElement = $html->find ('span[itemprop="streetAddress"]', 0);
		$suburb = $addressElement->next_sibling()->plaintext;

		$address = substr ($addressElement->plaintext, 0, -1) . ', ' . strtoupper ($suburb);
		
		return $address;
	}

	function get_agency (&$html) {
		$agency = $html->find ('.agencyName', 0)->plaintext;
		
		return $agency;
	}

	function get_agency_localDir (&$html) {
		$agency_localDir = strtok(get_agency ($html), " ");
		
		return $agency_localDir;
	}

	function get_first_agent (&$html) {
		$first_agent = $html->find ('.agentContactInfo', 0)->first_child();
		
		return $first_agent;
	}

	function get_first_agent_name (&$html) {
		$name = get_first_agent ($html)->plaintext;
		
		return $name;
	}

	function get_first_agent_contact (&$html) {
		$contact = get_first_agent ($html)->next_sibling()->first_child()->plaintext;
		$contact = preg_replace('/\s+/', '', $contact);
		
		return $contact;
	}

	function get_price (&$html) {
		$price = $html->find ('.priceText', 0);
		if (is_null ($price))
			return "N/A";

		$price = $price->plaintext;
		//$price = "Contact Agent";
		$price = preg_replace('/\s+/', '', $price);

		if ($price[0] != '$')
			$price = '$' . $price;

		if (!is_numeric ($price[1]))
			return "N/A";

		$isRange = false;
		$price_strlen = strlen ($price);
		for ($i=0; $i<$price_strlen; $i++) {
			if ($price[$i] == '-')
				$isRange = true;
		}

		if ($isRange) {
			$c = 0;
			while ($price[$c] != '-') {
				$c++;
			}
			$c++;
			if ($price[$c] != '$')
				$price = substr_replace ($price, "$", $c, 0);
		}

		return $price;
	}

	function get_inspect_time (&$html) {
		$time = $html->find ('.inspectionTimesWrapper', 0);
		if (is_null ($time))
			return "N/A";

		$time = $time->plaintext;
		$time = str_replace (" Save to Calendar", "", $time);

		return $time;
	}

	function get_inspect_date (&$html) {
		$date = $html->find ('.inspectionTimesWrapper strong[itemprop="name"]', 0);
		if (is_null ($date))
			return "N/A";

		$date = $date->plaintext;
		
		return $date;
	}

	function get_inspect_hour (&$html) {
		$hour = $html->find ('.inspectionTimesWrapper span[class="time"]', 0);	
		if (is_null ($hour))
			return "N/A";

		$hour = $hour->plaintext;
		$hour = strtolower ($hour);
		$hour = preg_replace('/\s+/', '', $hour);
		$hour = str_replace (":00", "", $hour);

		return $hour;
	}

	function get_auction_time (&$html) {
		$time = $html->find ('.auctionDetails span', 0);
		if (is_null ($time))
			return "N/A";
		
		$time = $time->plaintext;
		$time = str_replace ("Auction: ", "", $time);
		$time = substr ($time, 0, -1);
		//$time = "Sat 21-Feb-15 2:00PM";
		
		return $time;
	}

	// Sat 20-Feb-16 11:00AM
	function get_auction_date (&$html) {
		$time = get_auction_time ($html);
		if ($time == "N/A")
			return $time;

		$date = substr ($time, 0, 10);
		$date = str_replace ("-", " ", $date);

		return $date;
	}

	function get_auction_hour (&$html) {
		$time = get_auction_time ($html);
		if ($time == "N/A")
			return $time;

		$hour = substr ($time, 14);
		$hour = strtolower ($hour);
		$hour = str_replace (":00", "", $hour);

		return $hour;
	}

	function get_auction_inspect_hour (&$html) {
		$auction_hour = get_auction_hour ($html);
		if ($auction_hour == "N/A")
			return $auction_hour;

		$auction_inspect_hour = "";
		$am_pm = substr ($auction_hour, -2, 2);
		$actual_hour = substr ($auction_hour, 0, -2);
		
		if (strpos ($auction_hour, ":30")) {
			$auction_inspect_hour = substr ($auction_hour, 0, -5);
			$auction_inspect_hour .= $am_pm;
		}

		else {
			$tmp = (int)$actual_hour-1;
			if ($tmp == 0)
				$tmp = 12;
			if ($tmp == 11)
				$am_pm = "am";

			$auction_inspect_hour = "$tmp" . ":30" . $am_pm;
		}

		return $auction_inspect_hour;
	}
?>