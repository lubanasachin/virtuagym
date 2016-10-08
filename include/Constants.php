<?php

/*** database connection parameters */
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'qwerasdf');
define('DB_HOST', 'localhost');
define('DB_NAME', 'workout');

/*** API response message on success/failure */
$RESPMSG = array(
	'101'	=>	'Get exercise list failed',
	'102'	=>	'Get plan list failed',
	'103'	=>	'Get selected plan details failed',
	'104'	=>	'Get selected plan exercise details failed',
	'105'	=>	'Check Plan already added failed',
	'106'	=>	'Plan name is already added',
	'107'	=>	'Add new plan failed',
	'108'	=>	'Add new plan success',
	'109'	=>	'Update plan failed',
	'110'	=>	'Update plan success'
);

$MOTIVATION = array(
	'Exercise equals endorphins. Endorphins make you happy.',
	'Your legs are not giving out. Your head is giving up. Keep going',
	'Your desire to change must be greater than your desire to stay the same',
	'You have to expect things of yourself before you can do them',
	"You don't have to be great to start, but you have to start to be great",
	"What would you attempt to do if you knew you could not fail?",
	"What did you do today to bring you one step closer to your goal?",
	"We cannot become what we want to be by remaining what we are",
	"Today I will do what others won't, so tomorrow I can do what others can't",
	"There is no diet that will do what eating healthy does",
	"The only way to define your limits is by going beyond them",
	"The only disability in life is a bad attitude",
	"The greatest pleasure in life is doing what people say you cannot do",
	"The body achieves what the mind believes",
	"Take care of your body. It's the only place you have to live",
	"Strive for progress, not perfection",
	"Stop waiting for things to happen. Go out and make them happen",
	"Small changes can make a big difference",
	"Remind yourself frequently how far you've come",
	"Limitations exist only if you let them",
	"Keep smiling and one day life will get tired of upsetting you"
);
