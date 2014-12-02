#!/usr/bin/perl -w

$num_args = $#ARGV + 1;

if ($num_args == 0) {
	$extload_path = "extload.php";
} else {
	$extload_path = $ARGV[0];
}

$subscriptions = `php "$extload_path" cron process_subscriptions 1`;

@subscriptions = split(" ", $subscriptions);

foreach $subscription (@subscriptions)
{
	print `php "$extload_path" cron process_subscription "$subscription"`;
}