<?php



 
function travianz_form_try_require_autoloader()
{
	for ($i = 0; $i < 5; $i++) {
		$prefix = str_repeat('../', $i);
		$autoloader = $prefix . 'autoloader.php';
		if (file_exists($autoloader)) {
			require_once $autoloader;
			return true;
		}
	}
	return false;
}

class Form {
	private $errorarray = array();
	public $valuearray = array();
	private $errorcount;
	private $modern;
	public function __construct() {
		if (class_exists(\App\Legacy\FormService::class) || travianz_form_try_require_autoloader()) {
			$this->modern = new \App\Legacy\FormService(new \App\Legacy\LegacySessionStore());
			$this->errorarray = $this->modern->getErrors();
			$this->valuearray = $this->modern->values;
			$this->errorcount = $this->modern->returnErrors();
		} else {
			if(isset($_SESSION['errorarray']) && isset($_SESSION['valuearray'])) {
				$this->errorarray = $_SESSION['errorarray'];
				$this->valuearray = $_SESSION['valuearray'];
				$this->errorcount = count($this->errorarray);
	
				unset($_SESSION['errorarray']);
				unset($_SESSION['valuearray']);
			}
			else $this->errorcount = 0;
		}
	}

	public function addError($field,$error) {
		if ($this->modern) {
			$this->modern->addError((string)$field,(string)$error);
			$this->errorarray = $this->modern->getErrors();
			$this->errorcount = $this->modern->returnErrors();
		} else {
			$this->errorarray[$field] = $error;
			$this->errorcount = count($this->errorarray);
		}
	}

	public function getError($field) {
		if ($this->modern) {
			return $this->modern->getError((string)$field);
		} else {
			if(array_key_exists($field,$this->errorarray)) {
				return $this->errorarray[$field];
			}
			else return "";
		}
	}

	public function getValue($field) {
		if ($this->modern) {
			return $this->modern->getValue((string)$field);
		} else {
			if(array_key_exists($field,$this->valuearray)) {
				return $this->valuearray[$field];
			}
			else return "";
		}
	}
	
	public function setValue($field, $value) {
        if ($this->modern) {
			$this->modern->setValue((string)$field,(string)$value);
			$this->valuearray = $this->modern->values;
		} else {
			$this->valuearray[$field] = $value;
		}
	}

	public function getDiff($field,$cookie) {
		if ($this->modern) {
			return $this->modern->getDiff((string)$field,(string)$cookie);
		} else {
			if(array_key_exists($field,$this->valuearray) && $this->valuearray[$field] != $cookie) {
				return $this->valuearray[$field];
			}
			else return $cookie;
		}
	}

	public function getRadio($field,$value) {
		if ($this->modern) {
			return $this->modern->getRadio((string)$field,(string)$value);
		} else {
			if(array_key_exists($field,$this->valuearray) && $this->valuearray[$field] == $value) {
				return "checked";
			}
			else return "";
		}
	}

	public function returnErrors() {
		if ($this->modern) {
			return $this->modern->returnErrors();
		} else {
			return $this->errorcount;
		}
	}

	public function getErrors() {
		if ($this->modern) {
			return $this->modern->getErrors();
		} else {
			return $this->errorarray;
		}
	}
};
?>
