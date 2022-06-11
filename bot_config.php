<?php 
/* 
**********************************
**** Variabili API Telegram ******
**********************************
*/
/* Token API Telegram. Da richiere a @BotFather */ 
$token = 'xxxxxxxxxxxxxxxx:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

/* Chat a cui spedire i messaggi */
$chat = '-xxxxxxxxxxxxxxxxxx'; 

/* Feed RSS da cui prendere i valori */
$rss = 'https://yourfeed';

/* File in cui salvare i log */
$log_file = 'channel_bot.log';

/* File in cui salvare il PID */
$pid_file = 'bot.pid';

/*interval to send*/
$ritardo = 300;

/* Static variables. Warning: Only change the variables in bot_config.php */
$max_age_article = time() - 1200;
//$max_age_article = time() - 12000000000;

/* file url sended */
$db_sended_message = 'sended.db';

?>
