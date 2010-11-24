<?php

class GTD {
	public $continue = TRUE;
	
	private $options = OPTIONS;
	private $db = "";
	private $tasks = array();
	private $startup = "";
	
	function __construct() {
		// set db
		$this->db = simplexml_load_file(DATABASE);
		
		// set tasks
		$this->tasks = $this->db->tasks->task;
		$startup = $this->db->prefs->startup;
		$this->bookmarks($startup);
	}
	
	public function main() {
		writeln($this->options);
		$char = strtolower(get_char());
		echo "\010 \010";
		if ($char == "q") {
			$this->continue = FALSE;
		}
		elseif ($char == "a") {
			$this->show_all();
		}
		elseif ($char == "o") {
			$this->tags_or();
		}
		elseif ($char == "&") {
			$this->tags_and();
		}
		elseif ($char == "d") {
			$this->by_date();
		}
		elseif ($char == "t") {
			$this->by_title();
		}
		elseif ($char == "e") {
			$this->edit();
		}
		elseif ($char == "s") {
			$this->select();
		}
		elseif ($char == "n") {
			$this->new_task();
		}
		elseif ($char == "-") {
			$this->delete_task();
		}
		elseif ($char == "p") {
			$this->preferences();
		}
		elseif ($char =="b") {
			$this->bookmarks();
		}
	}
	
	public function close() {
		$id = 0;
		foreach ($this->db->tasks->task as $the_task) {
			$the_task["id"] = $id;
			$id++;
		}
		$xml = fopen(DATABASE, "w+");
		fwrite($xml, $this->db->asXML());
		fclose($xml);
		system("clear");
	}
	
	private function show_all() {
		$this->tasks = $this->db->tasks->task;
		system("clear");
		print_tasks($this->tasks);
	}
	
	private function tags_or($q = null) {
		echo "Tags: ";
		$input = ($q == null) ? read() : $q;
		$tags = preg_split("/\s?+,\s?+/", $input);
		$tasks = array();
		foreach ($this->tasks as $task) {
			$task_tags = preg_split("/\s?+,\s?+/", $task->tags);
			foreach ($tags as $tag) {
				if (in_array($tag, $task_tags)) {
					$tasks[] = $task;
					break;
				}
			}
		}
		$this->tasks = $tasks;
		system("clear");
		print_tasks($this->tasks);
	}
	
	private function tags_and($q = null) {
		echo "Tags: ";
		$input = ($q == null) ? read() : $q;
		$tags = preg_split("/\s?+,\s?+/", $input);
		$tasks = array();
		foreach ($this->tasks as $task) {
			$task_tags = preg_split("/\s?+,\s?+/", $task->tags);
			$okay = TRUE;
			foreach ($tags as $tag) {
				if (!in_array($tag, $task_tags)) {
					$okay = FALSE;
					break;
				}
			}
			if ($okay) {
				$tasks[] = $task;
			}
		}
		$this->tasks = $tasks;
		system("clear");
		print_tasks($this->tasks);
	}
	
	private function by_date($q = null) {
		echo "Date command: ";
		$tasks = array();
		$read = ($q == null) ? read() : $q;
		$input = preg_split("/\|/", preg_replace("/^(.)(.+)$/", "$1|$2", $read));
		if ($input[0] == "+") {
			$command = preg_split("/\|/", preg_replace("/^(.)(.+)(.)$/", "$1|$2|$3", $read));
			if ($command[2] == "d") {
				$denominator = 86400;
			}
			elseif ($command[2] == "w") {
				$denominator = 604800;
			}
			// TODO add month and year commands
			foreach ($this->tasks as $task) {
				$date = preg_split("/\|/", preg_replace("/(\d{4})(\d{2})(\d{2})/", "$1|$2|$3", $task->due));
				$s_date = floor(date("U", mktime(0, 0, 0, $date[1], $date[2], $date[0]))/$denominator);
				$s = floor(date("U")/$denominator);
				if ($s_date <= $s + $command[1]) {
					$tasks[] = $task;
				}
			}
		}
		elseif ($input[0] == "=") {
			foreach ($this->tasks as $task) {
				$date = preg_split("/\|/", preg_replace("/(\d{4})(\d{2})(\d{2})/", "$1|$2|$3", $task->due));
				$i_date = preg_split("/\|/", preg_replace("/(\d{2}).?(\d{2}).?(\d{4})/", "$3|$1|$2", $input[1]));
				$s_date = date('mdY', mktime(0, 0, 0, $date[1], $date[2], $date[0]));
				$s = date('mdY', mktime(0, 0, 0, $i_date[1], $i_date[2], $i_date[0]));
				echo "$s_date $s\n";
				if ($s == $s_date) {
					$tasks[] = $task;
				}
			}
		}
		$this->tasks = $tasks;
		system("clear");
		print_tasks($this->tasks);
	}
	
	private function by_title($q = null) {
		echo "Title: ";
		$input = ($q == null) ? read() : $q;
		$tasks = array();
		foreach ($this->tasks as $task) {
			if ($task->title == $input) {
				$tasks[] = $task;
			}
		}
		$this->tasks = $tasks;
		system("clear");
		print_tasks($this->tasks);
	}
	
	private function select($q = null) {
		echo "Select number: ";
		$input = ($q == null) ? read() : $q;
		$title = $this->tasks[(int)$input - 1];
		$tasks = array();
		foreach ($this->tasks as $task) {
			if ($task->title == $title) {
				$tasks[] = $task;
			}
		}
		$this->tasks = $tasks;
		system("clear");
		print_tasks($this->tasks);
	}
	
	private function edit() {
		echo "Select task to edit: ";
		$input = read();
		system("clear");
		$target = $this->tasks[$input - 1];
		print_tasks(array(0 => $target));
		$edit = array();
		echo "Title: ";
		$edit["title"] = read();
		echo "Tags: ";
		$edit["tags"] = read();
		echo "Due: ";
		$edit["due"] = read();
		echo "Description: ";
		$edit["description"] = read();
		foreach ($edit as $prop => $val) {
			if ($val == null) {
				if ($prop == "title") {
					$edit["title"] = $target->title;
				}
				if ($prop == "tags") {
					$edit["tags"] = $target->tags;
				}
				if ($prop == "due") {
					$edit["due"] = $target->due;
				}
				if ($prop == "description") {
					$edit["description"] = $target->description;
				}
			}
		}
		$target->title = $edit["title"];
		$target->tags = $edit["tags"];
		$target->due = $edit["due"];
		$target->description = $edit["description"];
		$xml = fopen(DATABASE, "w+");
		fwrite($xml, $this->db->asXML());
		fclose($xml);
		system("clear");
		$this->tasks = array(0 => $target);
		print_tasks($this->tasks);
	}
	
	private function new_task() {
		system("clear");
		$task = $this->db->tasks->addChild("task");
		$new = array();
		echo "Title: ";
		$new["title"] = read();
		echo "Tags: ";
		$new["tags"] = read();
		echo "Due: ";
		$new["due"] = preg_replace("/(\d{2}).?(\d{2}).?(\d{4})/", "$3$1$2", read());
		echo "Description: ";
		$new["description"] = read();
		$task->addChild("title", $new["title"]);
		$task->addChild("due", $new["due"]);
		$task->addChild("tags", $new["tags"]);
		$task->addChild("description", $new["description"]);
		$task->addAttribute("id", "");
		$id = 0;
		foreach ($this->db->tasks->task as $the_task) {
			$the_task["id"] = $id;
			$id++;
		}
		$xml = fopen(DATABASE, "w+");
		fwrite($xml, $this->db->asXML());
		fclose($xml);
		$this->tasks = array(0 => $task);
		system("clear");
		print_tasks($this->tasks);
	}
	
	private function delete_task() {
		echo "Select task to delete: ";
		$input = read();
		$target = $this->tasks[$input - 1];
		$doc = new DOMDocument;
		$doc->loadxml($this->db->asXML());
		$xpath = new DOMXpath($doc);
		foreach ($xpath->query("//task[@id='" . $target["id"] . "']") as $node) {
			$node->parentNode->removeChild($node);
		}
		$this->db = new SimpleXMLElement($doc->savexml());
		$xml = fopen(DATABASE, "w+");
		fwrite($xml, $this->db->asXML());
		fclose($xml);
		system("clear");
		$this->db = simplexml_load_file(DATABASE);
		$this->tasks = $this->db->tasks->task;
		print_tasks($this->tasks);
	}
	
	private function preferences() {
		system("clear");
		writeln("Select a preference to edit");
		writeln("\033[1m1)\033[0m Startup");
		$choice = get_char();
		if ($choice == "1") {
			system("clear");
			$current = $this->db->prefs->startup;
			writeln("\033[1mCurrent startup command: $current\033[0m");
			echo "New startup command: ";
			$input = read();
			$this->db->prefs->startup = $input;
			$xml = fopen(DATABASE, "w+");
			fwrite($xml, $this->db->asXML());
			fclose($xml);
		}
	}
	
	private function bookmarks($q = null) {
		if ($q == null) {
			system("clear");
			writeln("Select a bookmark");
			$bookmarks = $this->db->bookmarks->bookmark;
			$id = 1;
			if (is_string($bookmarks)) {
				$bookmarks = array($bookmarks);
			}
			foreach ($bookmarks as $bookmark) {
				echo "\033[1m$id) \033[0m";
				echo $bookmark["name"] . "\n";
				$id++;
			}
			writeln("\033[1mN) \033[0mNew bookmark");
			writeln("\033[1m-) \033[0mDelete Bookmark");
			echo "Item ID: ";
			$input = read();
			if (preg_match("/[0-9]+/", $input)) {
				$bookmark = $bookmarks[(int)$input - 1];
				$commands = preg_split("/\;/", $bookmark);
				foreach ($commands as $command) {
					$function = preg_replace("/^(.).+$/", "$1", $command);
					$parameter = preg_replace("/^.(.+)$/", "$1", $command);
					$this->tasks = $this->db->tasks->task;
					if ($function == "a") {
						$this->show_all();
					}
					elseif ($function == "o") {
						$this->tags_or($parameter);
					}
					elseif ($function == "&") {
						$this->tags_and($parameter);
					}
					elseif ($function == "d") {
						$this->by_date($parameter);
					}
					elseif ($function == "t") {
						$this->by_title($parameter);
					}
					elseif ($function == "s") {
						$this->select($parameter);
					}
				}
			}
			elseif ($input == "n") {
				echo "\033[1mBookmark name: \033[0m";
				$name = read();
				system("clear");
				writeln("\033[1;32m$name\033[0m");
				echo "\033[1mBookmark command: \033[0m";
				$command = read();
				$this->db->bookmarks->addChild("bookmark", $command)->addAttribute("name", $name);
				$xml = fopen(DATABASE, "w+");
				fwrite($xml, $this->db->asXML());
				fclose($xml);
				system("clear");
			}
			elseif ($input == "-") {
				echo "Select bookmark to delete: ";
				$deletee = read();
				if (!is_string($this->db->bookmarks->bookmark)) {
					unset($this->db->bookmarks->bookmark[$deletee - 1]);
				}
				else {
					unset($this->db->bookmarks->bookmark);
				}
			}
		}
		else {
			$bookmark = $q;
			$commands = preg_split("/\;/", $bookmark);
			foreach ($commands as $command) {
				$function = preg_replace("/^(.).+$/", "$1", $command);
				$parameter = preg_replace("/^.(.+)$/", "$1", $command);
				$this->tasks = $this->db->tasks->task;
				if ($function == "a") {
					$this->show_all();
				}
				elseif ($function == "o") {
					$this->tags_or($parameter);
				}
				elseif ($function == "&") {
					$this->tags_and($parameter);
				}
				elseif ($function == "d") {
					$this->by_date($parameter);
				}
				elseif ($function == "t") {
					$this->by_title($parameter);
				}
				elseif ($function == "s") {
					$this->select($parameter);
				}
			}
		}
	}
}

$GTD = new GTD;