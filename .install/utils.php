<?php

function read() {
	system("stty sane");
	return trim(fgets(STDIN));
}

function get_char($chars = 1) {
	system("stty -icanon");
	return trim(fread(STDIN, $chars));
	system("stty sane");
	echo "\010";
}

function prompt() {
	echo ">> ";
	return read();
}

function writeln($string) {
	echo "$string \n";
}
$COLS = (int)shell_exec("tput cols");
$margin_w = ($COLS - 39) / 2;
$margin = str_repeat(" ", $margin_w);
$logo  = "$margin  ____  _           ____  _____  ____  \r\n";
$logo .= "$margin / ___|| |         / ___||_   _||  _ \ \r\n";
$logo .= "$margin| |    | |        | |  _   | |  | | | |\r\n";
$logo .= "$margin| |___ | |___     | |_| |  | |  | |_| |\r\n";
$logo .= "$margin \____||_____|_____\____|  |_|  |____/ \r\n";
$logo .= "$margin             |_____|                   \r\n";

// =========================
// = ANSI Colorizing Class =
// =========================
class Colors {
	private $foreground_colors = array();
	private $background_colors = array();

	public function __construct() {
		// Set up shell colors
		$this->foreground_colors['black'] = '0;30';
		$this->foreground_colors['dark_gray'] = '1;30';
		$this->foreground_colors['blue'] = '0;34';
		$this->foreground_colors['light_blue'] = '1;34';
		$this->foreground_colors['green'] = '0;32';
		$this->foreground_colors['light_green'] = '1;32';
		$this->foreground_colors['cyan'] = '0;36';
		$this->foreground_colors['light_cyan'] = '1;36';
		$this->foreground_colors['red'] = '0;31';
		$this->foreground_colors['light_red'] = '1;31';
		$this->foreground_colors['purple'] = '0;35';
		$this->foreground_colors['light_purple'] = '1;35';
		$this->foreground_colors['brown'] = '0;33';
		$this->foreground_colors['yellow'] = '1;33';
		$this->foreground_colors['light_gray'] = '0;37';
		$this->foreground_colors['white'] = '1;37';

		$this->background_colors['black'] = '40';
		$this->background_colors['red'] = '41';
		$this->background_colors['green'] = '42';
		$this->background_colors['yellow'] = '43';
		$this->background_colors['blue'] = '44';
		$this->background_colors['magenta'] = '45';
		$this->background_colors['cyan'] = '46';
		$this->background_colors['light_gray'] = '47';
	}

	// Returns colored string
	public function getColoredString($string, $foreground_color = null, $background_color = null) {
		$colored_string = "";

		// Check if given foreground color found
		if (isset($this->foreground_colors[$foreground_color])) {
			$colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
		}
		// Check if given background color found
		if (isset($this->background_colors[$background_color])) {
			$colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
		}

		// Add string and end coloring
		$colored_string .=  $string . "\033[0m";

		return $colored_string;
	}

	// Returns all foreground color names
	public function getForegroundColors() {
		return array_keys($this->foreground_colors);
	}

	// Returns all background color names
	public function getBackgroundColors() {
		return array_keys($this->background_colors);
	}
}
$colors = new Colors;



function print_tasks($tasks) {
	global $theme;
	$COLS = (int)shell_exec("tput cols");
	$colors = new Colors;
	if ($tasks != null) {
		echo $colors->getColoredString(str_repeat("=", $COLS), $theme["task divider"]["foreground"], $theme["task divider"]["background"]);
		$id = 1;
		foreach ($tasks as $task) {
			$date = preg_replace("/(\d{4})(\d{2})(\d{2})/", "$2/$3/$1", $task->due);
			$tags = preg_replace("/\s?+\,\s?+/", " " . $colors->getColoredString("|", $theme["tags divider"]["foreground"], $theme["tags divider"]["background"]) . " ", $task->tags);
			$title = $task->title;
			$pad = 3 - strlen((string)$id);
			echo str_repeat(" ", $pad) . $colors->getColoredString("$id: ", $theme["id"]["foreground"], $theme["id"]["background"]);
			echo $colors->getColoredString("\033[1m$task->title\033[22m", $theme["title"]["foreground"], $theme["title"]["background"]);
			$space = $COLS - strlen($title) - strlen($date) - 5;
			echo str_repeat(" ", $space);
			echo $colors->getColoredString("\033[3m$date\033[24m", $theme["date"]["foreground"], $theme["date"]["background"]);
			echo $colors->getColoredString(str_repeat("-", $COLS), $theme["header rule"]["foreground"], $theme["header rule"]["background"]);
			writeln($tags);
			echo "\n";
			writeln(trim($task->description));
			echo $colors->getColoredString(str_repeat("=", $COLS), $theme["task divider"]["foreground"], $theme["task divider"]["background"]);
			$id++;
		}
	}
}

$options  = $colors->getColoredString("\033[1mA\033[22m", $theme["toolbar command"]["foreground"], $theme["toolbar command"]["background"]);
$options .= "ll ";
$options .= $colors->getColoredString("|", $theme["toolbar divider"]["foreground"], $theme["toolbar divider"]["background"]);
$options .= " ";
$options .= $colors->getColoredString("\033[1mT\033[22m", $theme["toolbar command"]["foreground"], $theme["toolbar command"]["background"]);
$options .= "itle ";
$options .= $colors->getColoredString("|", $theme["toolbar divider"]["foreground"], $theme["toolbar divider"]["background"]);
$options .= " Tags (";
$options .= $colors->getColoredString("\033[1mO\033[22m", $theme["toolbar command"]["foreground"], $theme["toolbar command"]["background"]);
$options .= "R) ";
$options .= $colors->getColoredString("|", $theme["toolbar divider"]["foreground"], $theme["toolbar divider"]["background"]);
$options .= " Tags (AND) ";
$options .= $colors->getColoredString("\033[1m&\033[22m", $theme["toolbar command"]["foreground"], $theme["toolbar command"]["background"]);
$options .= " ";
$options .= $colors->getColoredString("|", $theme["toolbar divider"]["foreground"], $theme["toolbar divider"]["background"]);
$options .= " ";
$options .= $colors->getColoredString("\033[1mD\033[22m", $theme["toolbar command"]["foreground"], $theme["toolbar command"]["background"]);
$options .= "ate ";
$options .= $colors->getColoredString("|", $theme["toolbar divider"]["foreground"], $theme["toolbar divider"]["background"]);
$options .= " ";
$options .= $colors->getColoredString("\033[1mS\033[22m", $theme["toolbar command"]["foreground"], $theme["toolbar command"]["background"]);
$options .= "elect ";
$options .= $colors->getColoredString("|", $theme["toolbar divider"]["foreground"], $theme["toolbar divider"]["background"]);
$options .= " ";
$options .= $colors->getColoredString("\033[1mN\033[22m", $theme["toolbar command"]["foreground"], $theme["toolbar command"]["background"]);
$options .= "ew ";
$options .= $colors->getColoredString("|", $theme["toolbar divider"]["foreground"], $theme["toolbar divider"]["background"]);
$options .= " ";
$options .= $colors->getColoredString("\033[1mE\033[22m", $theme["toolbar command"]["foreground"], $theme["toolbar command"]["background"]);
$options .= "dit ";
$options .= $colors->getColoredString("|", $theme["toolbar divider"]["foreground"], $theme["toolbar divider"]["background"]);
$options .= " Delete (";
$options .= $colors->getColoredString("\033[1m-\033[22m", $theme["toolbar command"]["foreground"], $theme["toolbar command"]["background"]);
$options .= ") ";
$options .= $colors->getColoredString("|", $theme["toolbar divider"]["foreground"], $theme["toolbar divider"]["background"]);
$options .= " A";
$options .= $colors->getColoredString("\033[1mr\033[22m", $theme["toolbar command"]["foreground"], $theme["toolbar command"]["background"]);
$options .= "chived ";
$options .= $colors->getColoredString("|", $theme["toolbar divider"]["foreground"], $theme["toolbar divider"]["background"]);
$options .= " ";
$options .= $colors->getColoredString("\033[1mP\033[22m", $theme["toolbar command"]["foreground"], $theme["toolbar command"]["background"]);
$options .= "references ";
$options .= $colors->getColoredString("|", $theme["toolbar divider"]["foreground"], $theme["toolbar divider"]["background"]);
$options .= " ";
$options .= $colors->getColoredString("\033[1mB\033[22m", $theme["toolbar command"]["foreground"], $theme["toolbar command"]["background"]);
$options .= "ookmarks ";
$options .= $colors->getColoredString("|", $theme["toolbar divider"]["foreground"], $theme["toolbar divider"]["background"]);
$options .= " ";
$options .= $colors->getColoredString("\033[1mQ\033[22m", $theme["toolbar command"]["foreground"], $theme["toolbar command"]["background"]);
$options .= "uit ";
define(OPTIONS, $options);