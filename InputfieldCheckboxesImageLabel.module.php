<?php
namespace ProcessWire;

/**
 * InputfieldCheckboxesImageLabel
 *
 * Renders checkboxes using images or styled labels, following the technique:
 * https://stackoverflow.com/a/17541916/3612981
 *
 */

class InputfieldCheckboxesImageLabel extends InputfieldCheckboxes
{

	public static function getModuleInfo()
	{
		return array(
			'title' => 'Inputfield Checkboxes Image Label',
			'version' => 100,
			'summary' => 'Checkboxes that use images/labels instead of standard checkboxes, suitable for FieldtypeOptions.',
			'requires' => 'ProcessWire>=3.0.0',
		);
	}

	public function getConfigInputfields()
	{
		$inputfields = parent::getConfigInputfields();

		/** @var InputfieldTextarea $f */
		$f = $this->wire('modules')->get('InputfieldTextarea');
		$f->attr('name', 'optionImages');
		$f->label = $this->_('Option Images');
		$f->description = $this->_('Enter one per line in the format: value=image_url');
		$f->notes = $this->_('Example: \nred=/site/assets/red.png\nblue=/site/assets/blue.png');
		$inputfields->add($f);

		return $inputfields;
	}

	public function render()
	{
		$out = "<div class='InputfieldCheckboxesImageLabel'>";

		// Parse the optionImages configuration
		$imageMap = array();
		if ($this->optionImages) {
			$lines = explode("\n", $this->optionImages);
			foreach ($lines as $line) {
				if (strpos($line, '=') === false)
					continue;
				list($k, $v) = explode('=', $line, 2);
				$imageMap[trim($k)] = trim($v);
			}
		}

		$selectedValues = $this->value;
		if(!is_array($selectedValues)) $selectedValues = array();

		foreach ($this->getOptions() as $key => $value) {
			$checked = in_array($key, $selectedValues) ? " checked='checked'" : "";
			$id = $this->id . "_" . $this->wire('sanitizer')->name($key);
			// InputfieldCheckboxes usually handles arrays, so we likely need name[]
			// But check if InputfieldCheckboxes logic requires strict name matching
			$name = $this->name . "[]"; 

			$label = $value;
			// If an image is defined for this option key, use it
			if (isset($imageMap[$key])) {
				$imgUrl = $this->wire('sanitizer')->url($imageMap[$key]);
				$path = parse_url($imgUrl, PHP_URL_PATH);
				$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

				$svgContent = '';
				if ($ext === 'svg') {
					$rootPath = $this->wire('config')->paths->root;
					// Remove leading slash from path to append to root
					$filePath = $rootPath . ltrim($path, '/');
					if (file_exists($filePath)) {
						$svgContent = file_get_contents($filePath);
					}
				}

				if ($svgContent) {
					$label = $svgContent;
				} else {
					$label = "<img src='$imgUrl' alt='" . $this->wire('sanitizer')->entities($value) . "' />";
				}
			} else {
				// Fallback to text label, respecting entityEncode
				$label = $this->entityEncode ? $this->wire('sanitizer')->entities($value) : $value;
			}

			$out .= "<label for='$id' class='image-label-option'>";
			$out .= "<input type='checkbox' name='$name' id='$id' value='$key'$checked />";
			$out .= "<span class='content'>$label</span>";
			$out .= "</label>";
		}

		$out .= "</div>";
		return $out;
	}
}
