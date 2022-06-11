<?php 
/* 
**********************************
* Bot Telegram**
**********************************
*/
require_once('bot_config.php');

/* I set the last start date to FALSE. At first startup, consider the max_age_articles parameter */
$last_send = false;
$last_send_title = "";
$dir = dirname(__FILE__);

/* logs that the bot was started */
$time = date("m-d-y H:i", time());
$log_text = "[$time] Bot started. URL Feed: $rss".PHP_EOL;
file_put_contents($dir."/".$log_file, $log_text, FILE_APPEND | LOCK_EX);
echo $log_text;
/* save PID for possible check in the future */
$pid = getmypid();
file_put_contents($dir."/".$pid_file, $pid);

/* Read already sended URL */
$already_sended = array();
if (file_exists($db_sended_message)) {
	$already_sended = file($db_sended_message);
	array_walk($already_sended, 'trim_value');
}

/* Function to remove PHP_EOL from array element*/
function trim_value(&$value) 
{ 
    $value = trim($value); 
}


/* Function for sending messages to the telegram chat */
function telegram_send_chat_message($token, $chat, $message, $dir, $log_file, $db_sended_message, $link) {
	/* current timestamp retrieval for any error log */
	$time = time();
	/* URL variable initialization */
	$url = "https://api.telegram.org/bot$token/sendMessage?chat_id=$chat";
	/* Imposto variabile URL con il message da inviare */
	$send_text=urlencode($message);
	$url = $url ."&text=$send_text";
	//start session curl 
	$ch = curl_init();
	$optArray = array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true
	);
	curl_setopt_array($ch, $optArray);
	$result = curl_exec($ch);

	/* In case of an error, I save it in the logs */
	if ($result == FALSE) {
		$time = date("m-d-y H:i", time());
		$log_text = "[$time] Sending message failed: $message".PHP_EOL;
		file_put_contents($dir."/".$log_file, $log_text, FILE_APPEND | LOCK_EX);
	} else {
		$log_text = $link.PHP_EOL;
		file_put_contents($dir."/".$db_sended_message, $log_text, FILE_APPEND | LOCK_EX);
	}
	curl_close($ch);
}
/* If $ last_send has not been parameterized, it means that the bot has just started. So I set it equal to $ max_age_article, which is the current time - 20 minutes. It will then retroactively post all news older than 20 minutes*/
if ($last_send == false) $last_send = $max_age_article;
$current_time = time();
$article = @simplexml_load_file($rss);
/*If it failed to download the feed, I post an error message in the log*/
if ($article === false) { 
	$time = date("m-d-y H:i", $current_time);
	$log_text = "[$time] The bot was unable to contact the RSS Feed. Connection failed $rss.".PHP_EOL;
	file_put_contents($dir."/".$log_file, $log_text, FILE_APPEND | LOCK_EX);
/* I go ahead only if $ article is not in false, this means that simplexml was able to load the feed and I can proceed to process the news */	
}else{
	/* I reverse the order of the news, from descending to ascending */
	$xmlArray = array();
	foreach ($article->channel->item as $item) $xmlArray[] = $item;
	$xmlArray = array_reverse($xmlArray);

	/* sending news */
	foreach ($xmlArray as $item) {
		/* I extract the timestamp of the article */
		$timestamp_article = strtotime($item->pubDate);
		/* I calculate the difference between the current timestamp and that of the article */
		$ora_attuale = time();
		$diff_timestamp = time() - $timestamp_article;
		/* Check if the news is more recent than the last published */
		/* Although it should * not * but it does for unknown reasons, I have added a control that should avoid having the same story published twice */
		/* I do not publish articles with less than 5 minutes (300 seconds) of seniority */
		/* I do not publish articles already published */
		if ($timestamp_article > $last_send AND $last_send_title != $item->title AND (in_array($item->link,$already_sended) != true)) {
//		if ($timestamp_article > $last_send AND $diff_timestamp > $ritardo AND $last_send_title != $item->title AND (in_array($item->link,$already_sended) != true)) {
//		if ($timestamp_article > $last_send AND $diff_timestamp > $ritardo) {
			$message = ucfirst($item->category) . " - " . $item->title . PHP_EOL;
			$message .= $item->link . PHP_EOL;
			telegram_send_chat_message($token, $chat , $message, $dir, $log_file, $db_sended_message, $item->link);
			$last_send = $timestamp_article;
			$last_send_title = $item->title;
		}
	}
}
?>

