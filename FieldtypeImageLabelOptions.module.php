<?php namespace ProcessWire;

/**
 * FieldtypeImageLabelOptions
 *
 * Fieldtype that extends FieldtypeOptions to allow mapping options to images.
 * Uses InputfieldRadiosImageLabel for rendering.
 *
 */

class FieldtypeImageLabelOptions extends FieldtypeOptions implements Module {

	public static function getModuleInfo() {
		return array(
			'title' => 'Image Label Options',
			'version' => 101,
			'summary' => 'Select options that map to images/labels.',
			'requires' => array('FieldtypeOptions', 'InputfieldRadiosImageLabel', 'InputfieldCheckboxesImageLabel'),
			'installs' => array('FieldtypeOptions', 'InputfieldRadiosImageLabel', 'InputfieldCheckboxesImageLabel'),
		);
	}

	public function init() {
		parent::init();
	}

	/**
	 * Return the Inputfield for this Fieldtype
	 *
	 */
	public function getInputfield(Page $page, Field $field) {
		
		// Let parent create the Inputfield. This ensures options are populated,
		// value is set (including initValue logic), and other standard setup is performed.
		$inputfield = parent::getInputfield($page, $field);
		
		// Check if we need to swap the Inputfield to one of our ImageLabel types
		$class = get_class($inputfield);
		$targetClass = '';

		// Map standard classes to our ImageLabel equivalents
		if($class == 'InputfieldRadios' || $class == 'InputfieldSelect') {
			$targetClass = 'InputfieldRadiosImageLabel';
		} elseif ($class == 'InputfieldCheckboxes' || $class == 'InputfieldSelectMultiple') {
			$targetClass = 'InputfieldCheckboxesImageLabel';
		} elseif ($class == 'InputfieldRadiosImageLabel' || $class == 'InputfieldCheckboxesImageLabel') {
			// Already correct
			$targetClass = $class;
		}

		// If we identified a target class different from what we have, swap it
		if($targetClass && $class !== $targetClass) {
			$newInputfield = $this->modules->get($targetClass);
			if($newInputfield) {
				// Copy basic attributes
				$newInputfield->name = $inputfield->name;
				$newInputfield->id = $inputfield->id;
				$newInputfield->label = $inputfield->label;
				$newInputfield->description = $inputfield->description;
				$newInputfield->notes = $inputfield->notes;
				$newInputfield->value = $inputfield->value;
				$newInputfield->columnWidth = $inputfield->columnWidth;
				$newInputfield->required = $inputfield->required;
				$newInputfield->collapsed = $inputfield->collapsed;
				
				// Copy Options
				// Inputfield::getOptions() returns array(value => label)
				foreach($inputfield->getOptions() as $val => $label) {
					// We use addOption to ensure internal structure is correct
					$newInputfield->addOption($val, $label);
				}
				
				$inputfield = $newInputfield;
			}
		}

		// Pass the optionImages configuration to the Inputfield
		if($field->optionImages) {
			$inputfield->optionImages = $field->optionImages;
		}

		// Pass the optionImageMinWidth configuration to the Inputfield
		if($field->optionImageMinWidth) {
			$inputfield->optionImageMinWidth = $field->optionImageMinWidth;
		} else {
			// Default to 100px if not set
			$inputfield->optionImageMinWidth = 100;
		}

		return $inputfield;
	}

	/**
	 * Configuration fields for this Fieldtype
	 *
	 */
	public function getConfigInputfields(Field $field) {
		$inputfields = parent::getConfigInputfields($field);

		// Allow user to select our custom Inputfields explicitly
		$f = $inputfields->get('inputfieldClass');
		if($f) {
			$f->addOption('InputfieldRadiosImageLabel', 'Image Label Radios');
			$f->addOption('InputfieldCheckboxesImageLabel', 'Image Label Checkboxes');
			
			// Optional: Set default description or notes explaining usage
		}

		// Add the Option Images mapping field
		/** @var InputfieldTextarea $f */
		$f = $this->modules->get('InputfieldTextarea');
		$f->attr('name', 'optionImages');
		$f->label = $this->_('Option Images');
		$f->description = $this->_('Enter one per line in the format: option_id=image_url (or option_value=image_url)');
		$f->notes = $this->_('Example: \n1=/site/assets/red.png\nmy_val=/site/assets/blue.png');
		$f->value = $field->optionImages;
		
		$inputfields->add($f);

		// Add the Minimum Image Width field
		/** @var InputfieldInteger $f */
		$f = $this->modules->get('InputfieldInteger');
		$f->attr('name', 'optionImageMinWidth');
		$f->label = $this->_('Minimum Image Width');
		$f->description = $this->_('Minimum width in pixels for rendered images. Default is 100px.');
		$f->value = $field->optionImageMinWidth ? $field->optionImageMinWidth : 100;
		$f->attr('min', 1);
		
		$inputfields->add($f);

		return $inputfields;
	}

	/**
	 * Format value for output
	 * Inject 'image' property into SelectableOption objects
	 *
	 */
	public function formatValue(Page $page, Field $field, $value) {
		$value = parent::formatValue($page, $field, $value);

		// Parse image map
		$imageMap = array();
		if($field->optionImages) {
			$lines = explode("\n", $field->optionImages);
			foreach($lines as $line) {
				if(strpos($line, '=') === false) continue;
				list($k, $v) = explode('=', $line, 2);
				$imageMap[trim($k)] = trim($v);
			}
		}

		if(empty($imageMap)) return $value;

		// Inject into SelectableOption objects
		if($value instanceof SelectableOptionArray) {
			foreach($value as $option) {
				$this->injectImage($option, $imageMap);
			}
		} elseif($value instanceof SelectableOption) {
			$this->injectImage($value, $imageMap);
		}

		return $value;
	}

	protected function injectImage($option, $map) {
		// Check ID first, then Value
		if(isset($map[$option->id])) {
			$option->set('image', $map[$option->id]);
		} elseif(isset($map[$option->value])) {
			$option->set('image', $map[$option->value]);
		}
	}
}
