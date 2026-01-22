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

		// Note: optionImages is configured at the Field level (FieldtypeImageLabelOptions),
		// not at the Inputfield level, to avoid conflicts when inputfieldClass is explicitly set.

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

			// If an image is defined for this option key, use it
			if (isset($imageMap[$key])) {
				$imgUrl = $this->wire('sanitizer')->url($imageMap[$key]);
				$minWidth = isset($this->optionImageMinWidth) ? (int)$this->optionImageMinWidth : 100;
				$label = "<img src='$imgUrl' alt='" . $this->wire('sanitizer')->entities($value) . "' style='min-width: {$minWidth}px;' />";
			} else {
				// Fallback to text label (entity-encoded for safety)
				$label = $this->wire('sanitizer')->entities($value);
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
