<?php

require_once('core/defaults.php');
require_once('core/Plugin.php');



class Job {
	
	private $datafile;
	private $data;
	private $Plugin;
	
	public function __construct($datafile) {
		$this->datafile = $datafile;
		$this->loadData();
		$this->loadPlugin();
		$result = $this->processData();
		$this->writeJobDone($result);
		$this->removeDatafile();
	}
	
	private function loadData() {
		$this->data = unserialize(file_get_contents($this->datafile));
	}
	
	private function loadPlugin() {
		require_once($this->data['plugin_path']);
		
		$c = libFile::parseConfigFile('nimda.conf');
		$MySQL = new MySQL($c['mysql_host'], $c['mysql_user'], $c['mysql_pass'], $c['mysql_db']);
		$this->Plugin = new $this->data['classname'](null, $MySQL);
	}
	
	private function processData() {
		$callback = $this->data['callback'];
		if(is_null($this->data['data'])) {
			$result = $this->Plugin->$callback();
		} else {
			$result = $this->Plugin->$callback($this->data['data']);
		}
		
	return $result;
	}
	
	private function writeJobDone($result) {
		$data = array('callback' => $this->data['callback'], 'origin' => $this->data['origin'], 'result' => $result);
		file_put_contents($this->data['job_done_filename'], serialize($data));
	}
	
	private function removeDatafile() {
		unlink($this->datafile);
	}
	
}

new Job($argv[1]);

?>
